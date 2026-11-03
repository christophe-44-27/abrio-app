<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $payer = $user->payer();

        return Inertia::render('settings/Premium', [
            'unlocked' => $payer !== null,
            'unlockedAt' => $payer?->unlocked_at?->toIso8601String(),
            'isMember' => $user->joined_household_id !== null,
            'hasInvoice' => $user->hasPaid() && filled($user->unlock_checkout_session_id),
            'housingLimit' => $payer ? null : User::FREE_HOUSING_LIMIT,
        ]);
    }

    /**
     * Send the user to the Stripe-hosted invoice (or receipt) of their purchase.
     */
    public function invoice(Request $request, UnlockCheckout $checkout): RedirectResponse
    {
        $sessionId = $request->user()->unlock_checkout_session_id;

        abort_if(blank($sessionId), 404);

        $url = $checkout->invoiceUrl($sessionId);

        abort_if($url === null, 404);

        return redirect()->away($url);
    }
}
