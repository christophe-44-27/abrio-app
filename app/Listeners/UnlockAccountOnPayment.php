<?php

namespace App\Listeners;

use App\Models\User;
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

        // Ignore payments made through any other link of the Stripe account.
        $expectedLink = config('billing.unlock.payment_link_id');

        if ($expectedLink && ($session['payment_link'] ?? null) !== $expectedLink) {
            return;
        }

        $userId = $session['client_reference_id'] ?? null;

        if ($userId && $user = User::find($userId)) {
            $user->markUnlocked();
        }
    }
}
