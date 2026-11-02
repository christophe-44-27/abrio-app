<?php

namespace App\Http\Controllers;

use App\Support\UnlockCheckout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * One-time purchase that lifts the free housing limit, through a Stripe Checkout
 * Session. The Stripe webhook (see UnlockAccountOnPayment) is the only thing that
 * unlocks an account; the page shown after paying just waits for it.
 */
class UnlockController extends Controller
{
    public function start(Request $request, UnlockCheckout $checkout): RedirectResponse
    {
        $user = $request->user();

        if ($user->isUnlocked()) {
            return redirect()->route('verdicts');
        }

        abort_unless($checkout->isConfigured(), 503, 'The unlock payment is not configured.');

        return redirect()->away($checkout->checkoutUrl(
            $user,
            route('unlock.success'),
            $this->cancelUrl($request),
            app()->getLocale(),
        ));
    }

    /**
     * Where Stripe sends the user after paying.
     */
    public function success(Request $request): Response|RedirectResponse
    {
        if ($request->user()->isUnlocked()) {
            return redirect()->route('verdicts', ['unlocked' => 1]);
        }

        return Inertia::render('household/UnlockPending');
    }

    /**
     * Where Stripe's back arrow leads: the page the user came from, if it is ours.
     */
    private function cancelUrl(Request $request): string
    {
        $previous = url()->previous();

        $isOurs = parse_url($previous, PHP_URL_HOST) === $request->getHost()
            && ! str_contains($previous, '/unlock');

        return $isOurs ? $previous : route('premium.edit');
    }
}
