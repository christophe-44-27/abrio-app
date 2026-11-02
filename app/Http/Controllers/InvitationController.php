<?php

namespace App\Http\Controllers;

use App\Models\HouseholdInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The link in an invitation email. New people are sent to the registration
 * (CreateNewUser joins them); existing accounts sign in, then confirm here.
 */
class InvitationController extends Controller
{
    public const SESSION_KEY = 'invitation_token';

    public function show(Request $request, string $token): Response|RedirectResponse
    {
        $invitation = HouseholdInvitation::findValid($token);

        if (! $invitation) {
            return Inertia::render('invitations/Show', ['state' => 'invalid']);
        }

        $user = $request->user();

        if (! $user) {
            if (User::where('email', $invitation->email)->exists()) {
                return redirect()->guest(route('login'));
            }

            $request->session()->put(self::SESSION_KEY, $token);

            return redirect()->route('register');
        }

        return Inertia::render('invitations/Show', [
            'state' => $this->stateFor($user, $invitation),
            'inviter' => $invitation->inviter->name,
            'email' => $invitation->email,
            'token' => $token,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = HouseholdInvitation::findValid($token);

        abort_if($invitation === null, 404);
        abort_unless($this->stateFor($request->user(), $invitation) === 'confirm', 403);

        $request->user()->forceFill(['joined_household_id' => $invitation->household_id])->save();
        $invitation->delete();

        return $this->withToast(redirect()->route('dashboard'), __('team.joined'));
    }

    private function stateFor(User $user, HouseholdInvitation $invitation): string
    {
        if (strcasecmp($user->email, $invitation->email) !== 0) {
            return 'wrong_account';
        }

        if ($user->household || $user->joined_household_id) {
            return 'unavailable';
        }

        return 'confirm';
    }
}
