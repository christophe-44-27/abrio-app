<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * One-time purchase that lifts the free housing limit, through a Stripe Payment Link.
 * The Stripe webhook (see UnlockAccountOnPayment) is the only thing that unlocks
 * an account; the page shown after paying just waits for it.
 */
class UnlockController extends Controller
{
    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();
        $link = config('billing.unlock.link');

        if ($user->isUnlocked()) {
            return redirect()->route('verdicts');
        }

        abort_unless($link, 503, 'The unlock payment link is not configured.');

        $query = http_build_query([
            'client_reference_id' => $user->id,
            'prefilled_email' => $user->email,
            'locale' => app()->getLocale(),
        ]);

        return redirect()->away($link.(str_contains($link, '?') ? '&' : '?').$query);
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
}
