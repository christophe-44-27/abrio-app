<?php

namespace App\Actions\Fortify;

use App\Concerns\GeoValidationRules;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
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
            'terms' => ['accepted'],
            'marketing' => ['nullable', 'boolean'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'country' => $input['country'],
            'province' => $input['province'] ?? null,
            'password' => $input['password'],
            'registration_ip' => request()->ip(),
            'terms_accepted_at' => now(),
            'marketing_opt_in_at' => ! empty($input['marketing']) ? now() : null,
        ]);

        // Signing up with the address an owner invited joins their household.
        $invitation = HouseholdInvitation::pending();

        if ($invitation && strcasecmp($invitation->email, $user->email) === 0) {
            $invitation->acceptBy($user);
        }

        return $user;
    }
}
