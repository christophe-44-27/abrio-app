<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Support\UnlockCheckout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PremiumController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('settings/Premium', [
            'unlocked' => $user->isUnlocked(),
            'unlockedAt' => $user->unlocked_at?->toIso8601String(),
            'hasInvoice' => $user->isUnlocked() && filled($user->unlock_checkout_session_id),
            'housingLimit' => $user->housingLimit(),
        ]);
    }

    /**
     * Send the user to the Stripe-hosted invoice (or receipt) of their purchase.
     */
    public function invoice(Request $request, UnlockCheckout $checkout): RedirectResponse
    {
        $sessionId = $request->user()->unlock_checkout_session_id;

        abort_unless($sessionId, 404);

        $url = $checkout->invoiceUrl($sessionId);

        abort_unless($url, 404);

        return redirect()->away($url);
    }
}
