<?php

namespace App\Listeners;

use App\Models\User;
use App\Support\UnlockCheckout;
use Laravel\Cashier\Events\WebhookReceived;

class UnlockAccountOnPayment
{
    private const EVENTS = [
        'checkout.session.completed',
        'checkout.session.async_payment_succeeded',
    ];

    public function handle(WebhookReceived $event): void
    {
        if (! in_array($event->payload['type'] ?? null, self::EVENTS, true)) {
            return;
        }

        // Without a signing secret Cashier does not verify the webhook, so
        // anyone could forge this event: never unlock in production like that.
        if (app()->isProduction() && ! config('cashier.webhook.secret')) {
            return;
        }

        $session = $event->payload['data']['object'] ?? [];

        if (($session['payment_status'] ?? null) !== 'paid') {
            return;
        }

        // Only sessions the app created for this purchase count: the marker is
        // set server-side, so other products of the Stripe account never unlock.
        if (($session['metadata']['purchase'] ?? null) !== UnlockCheckout::PURCHASE) {
            return;
        }

        $userId = $session['client_reference_id'] ?? null;

        if ($userId && $user = User::whereKey($userId)->first()) {
            $user->markUnlocked($session['id'] ?? null);
        }
    }
}
