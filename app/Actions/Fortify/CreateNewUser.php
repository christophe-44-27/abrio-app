<?php

namespace App\Actions\Fortify;

use App\Concerns\GeoValidationRules;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Http\Controllers\InvitationController;
use App\Models\HouseholdInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use GeoValidationRules, PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            ...$this->geoRules($input),
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'country' => $input['country'],
            'province' => $input['province'] ?? null,
            'password' => $input['password'],
        ]);

        $this->joinInvitedHousehold($user);

        return $user;
    }

    /**
     * Someone who signs up with the address an owner invited joins that
     * household. Having received the invitation proves the address is theirs,
     * so it counts as verified.
     */
    private function joinInvitedHousehold(User $user): void
    {
        $token = session(InvitationController::SESSION_KEY);
        $invitation = $token ? HouseholdInvitation::findValid($token) : null;

        if (! $invitation || strcasecmp($invitation->email, $user->email) !== 0) {
            return;
        }

        $user->forceFill([
            'joined_household_id' => $invitation->household_id,
            'email_verified_at' => now(),
        ])->save();

        $invitation->delete();
        session()->forget(InvitationController::SESSION_KEY);
    }
}
