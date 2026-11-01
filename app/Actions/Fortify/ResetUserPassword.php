<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $hadTwoFactor = $user->two_factor_secret !== null;

        // Resetting through the email link is the way back into an account
        // whose second factor is lost or was set by someone else.
        $user->forceFill([
            'password' => $input['password'],
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        if ($hadTwoFactor) {
            event(new TwoFactorAuthenticationDisabled($user));
        }
    }
}
