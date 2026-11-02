<?php

namespace App\Http\Controllers;

use App\Models\Household;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Read-only verdicts behind the secret link the household owner turned on.
 */
class SharedVerdictController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $household = Household::where('share_token', $token)->first();

        abort_unless($household && $household->isUnlocked() && ! empty($household->housings), 404);

        return Inertia::render('household/Verdicts', [
            'household' => $household->only(['members', 'common_expenses', 'personal_expenses']),
            'housings' => $household->housings,
            'unlocked' => true,
            'shared' => true,
        ]);
    }
}
