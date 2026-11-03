<?php

namespace App\Support;

use App\Models\User;
use Laravel\Cashier\Cashier;
use RuntimeException;

/**
 * Talks to Stripe for the one-time unlock purchase: builds the Checkout Session
 * (with a "back" arrow to the app) and finds the invoice or receipt afterwards.
 */
class UnlockCheckout
{
    public const PURCHASE = 'unlock';

    public function isConfigured(): bool
    {
        return filled(config('cashier.secret')) && filled(config('billing.unlock.price_id'));
    }

    /**
     * Hosted Stripe payment page for this user; Stripe sends them to
     * $cancelUrl if they use its back arrow.
     */
    public function checkoutUrl(User $user, string $successUrl, string $cancelUrl, string $locale): string
    {
        $session = Cashier::stripe()->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => [['price' => config('billing.unlock.price_id'), 'quantity' => 1]],
            'client_reference_id' => (string) $user->id,
            // A real Stripe customer (stored in users.stripe_id): in payment mode,
            // Checkout creates none on its own when given only an email.
            'customer' => $user->hasStripeId() ? $user->stripeId() : $user->createAsStripeCustomer()->id,
            'metadata' => ['purchase' => self::PURCHASE],
            'invoice_creation' => ['enabled' => true],
            'locale' => in_array($locale, ['fr', 'en'], true) ? $locale : 'auto',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        return $session->url ?? throw new RuntimeException('Stripe returned no checkout URL.');
    }

    /**
     * Where the user can see what they paid: the hosted invoice, or the card
     * receipt when no invoice exists.
     */
    public function invoiceUrl(string $checkoutSessionId): ?string
    {
        $stripe = Cashier::stripe();
        $session = $stripe->checkout->sessions->retrieve($checkoutSessionId, [
            'expand' => ['invoice', 'payment_intent.latest_charge'],
        ]);

        $invoice = $session->invoice;

        if (is_object($invoice) && $invoice->hosted_invoice_url) {
            return $invoice->hosted_invoice_url;
        }

        $charge = $session->payment_intent->latest_charge ?? null;

        return is_object($charge) ? ($charge->receipt_url ?? null) : null;
    }
}
