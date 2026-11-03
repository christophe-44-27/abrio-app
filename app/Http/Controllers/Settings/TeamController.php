<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\User;
use App\Notifications\HouseholdInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $household = $user->currentHousehold();

        return Inertia::render('settings/Team', [
            'unlocked' => $household?->isUnlocked() ?? $user->isUnlocked(),
            'team' => $household ? $this->teamPayload($household, $user) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function teamPayload(Household $household, User $viewer): array
    {
        $isOwner = $household->user_id === $viewer->id;
        $owner = $isOwner ? $viewer : $household->user;

        return [
            'isOwner' => $isOwner,
            'maxUsers' => Household::MAX_USERS,
            'freeSeats' => $household->freeSeats(),
            'members' => collect([$owner, ...$household->team()->orderBy('name')->get()])
                ->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'isOwner' => $u->id === $household->user_id,
                    'isYou' => $u->id === $viewer->id,
                ])->values(),
            // Only the owner sees who is invited, and the public link.
            'invitations' => $isOwner
                ? $household->invitations()->orderBy('created_at')->get()->map(fn ($i) => [
                    'id' => $i->id,
                    'email' => $i->email,
                    'expired' => $i->isExpired(),
                ])->values()
                : [],
            'shareUrl' => $isOwner && $household->share_token
                ? route('shared.verdicts', $household->share_token)
                : null,
        ];
    }

    public function invite(Request $request): RedirectResponse
    {
        $household = $this->ownedHousehold($request);

        abort_unless($household->isUnlocked(), 403, __('team.locked'));

        $email = Str::lower($request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ])['email']);

        $existing = User::where('email', $email)->first();

        if ($existing && $existing->currentHousehold()?->is($household)) {
            throw ValidationException::withMessages(['email' => __('team.already_member')]);
        }

        if ($existing?->hasHousehold()) {
            throw ValidationException::withMessages(['email' => __('team.has_household')]);
        }

        $replacing = $household->invitations()->where('email', $email)->exists();

        if (! $replacing && $household->freeSeats() < 1) {
            throw ValidationException::withMessages(['email' => __('team.full')]);
        }

        $token = Str::random(48);

        $household->invitations()->updateOrCreate(['email' => $email], [
            'invited_by' => $request->user()->id,
            'token' => HouseholdInvitation::hash($token),
            'expires_at' => now()->addDays(HouseholdInvitation::TTL_DAYS),
        ]);

        Notification::route('mail', $email)->notify(new HouseholdInvitationNotification(
            route('invitations.show', $token),
            $request->user()->name,
            app()->getLocale(),
        ));

        return $this->withToast(back(), __('team.invited', ['email' => $email]));
    }

    public function cancelInvitation(Request $request, HouseholdInvitation $invitation): RedirectResponse
    {
        $household = $this->ownedHousehold($request);

        abort_unless($invitation->household_id === $household->id, 404);

        $invitation->delete();

        return $this->withToast(back(), __('team.invitation_cancelled'));
    }

    public function removeMember(Request $request, User $member): RedirectResponse
    {
        $household = $this->ownedHousehold($request);

        abort_unless($member->joined_household_id === $household->id, 404);

        $member->leaveHousehold();

        return $this->withToast(back(), __('team.member_removed', ['name' => $member->name]));
    }

    /**
     * A member steps out of the household they joined. The owner cannot leave.
     */
    public function leave(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_if($user->joined_household_id === null, 404);

        $user->leaveHousehold();

        return $this->withToast(redirect()->route('dashboard'), __('team.left'));
    }

    public function startSharing(Request $request): RedirectResponse
    {
        $household = $this->ownedHousehold($request);

        abort_unless($household->isUnlocked(), 403, __('team.locked'));

        $household->startSharing();

        return $this->withToast(back(), __('team.sharing_on'));
    }

    public function stopSharing(Request $request): RedirectResponse
    {
        $this->ownedHousehold($request)->stopSharing();

        return $this->withToast(back(), __('team.sharing_off'));
    }

    /**
     * The household this user owns: only owners manage invitations, members and sharing.
     */
    private function ownedHousehold(Request $request): Household
    {
        $household = $request->user()->household;

        abort_if($household === null, 404);

        return $household;
    }
}
