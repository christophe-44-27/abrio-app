<?php

namespace App\Models;

use App\Notifications\VerifyEmailCodeNotification;
use App\Support\EmailVerificationCode;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Billable;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'country', 'province', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    public const FREE_HOUSING_LIMIT = 3;

    /**
     * Send a 6-digit code to verify the email address, instead of a link.
     */
    public function sendEmailVerificationNotification(): void
    {
        $code = EmailVerificationCode::issue($this);

        $this->notify(new VerifyEmailCodeNotification($code, app()->getLocale()));
    }

    public function isUnlocked(): bool
    {
        return $this->unlocked_at !== null;
    }

    /**
     * Lift the free housing limit (one-time purchase). Idempotent.
     */
    public function markUnlocked(?string $checkoutSessionId = null): void
    {
        if (! $this->isUnlocked()) {
            $this->forceFill([
                'unlocked_at' => now(),
                'unlock_checkout_session_id' => $checkoutSessionId,
            ])->save();
        }
    }

    /**
     * How many housings this user may compare.
     */
    public function housingLimit(): ?int
    {
        return $this->isUnlocked() ? null : self::FREE_HOUSING_LIMIT;
    }

    /**
     * @return HasOne<Household, $this>
     */
    public function household(): HasOne
    {
        return $this->hasOne(Household::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'unlocked_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
