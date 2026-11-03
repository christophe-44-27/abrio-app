<?php

namespace App\Models;

use App\Notifications\VerifyEmailCodeNotification;
use App\Support\EmailVerificationCode;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property string $country
 * @property string|null $province
 * @property string|null $registration_ip
 * @property string|null $last_login_ip
 * @property Carbon|null $terms_accepted_at
 * @property Carbon|null $marketing_opt_in_at
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $unlocked_at
 * @property string|null $unlock_checkout_session_id
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $joined_household_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Household|null $household
 * @property-read Household|null $joinedHousehold
 */
#[Fillable(['name', 'email', 'country', 'province', 'password', 'joined_household_id', 'registration_ip', 'terms_accepted_at', 'marketing_opt_in_at'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    public const FREE_HOUSING_LIMIT = 2;

    /**
     * Send a 6-digit code to verify the email address, instead of a link.
     */
    public function sendEmailVerificationNotification(): void
    {
        $code = EmailVerificationCode::issue($this);

        $this->notify(new VerifyEmailCodeNotification($code, app()->getLocale()));
    }

    /**
     * Whether this user bought the unlock themselves (the household owner).
     */
    public function hasPaid(): bool
    {
        return $this->unlocked_at !== null;
    }

    /**
     * Who paid for this user's access: themselves, or the owner of the household they joined.
     */
    public function payer(): ?self
    {
        if ($this->hasPaid()) {
            return $this;
        }

        $owner = $this->joinedHousehold?->user;

        return $owner?->hasPaid() ? $owner : null;
    }

    public function isUnlocked(): bool
    {
        return $this->payer() !== null;
    }

    /**
     * Lift the free housing limit (one-time purchase). Idempotent: a payment
     * is recorded even for someone who already has access through a household.
     */
    public function markUnlocked(?string $checkoutSessionId = null): void
    {
        if (! $this->hasPaid()) {
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
     * The household this user owns.
     *
     * @return HasOne<Household, $this>
     */
    public function household(): HasOne
    {
        return $this->hasOne(Household::class);
    }

    /**
     * The household this user was invited into.
     *
     * @return BelongsTo<Household, $this>
     */
    public function joinedHousehold(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'joined_household_id');
    }

    /**
     * The household the user works on: their own, or the one they joined.
     */
    public function currentHousehold(): ?Household
    {
        return $this->joined_household_id ? $this->joinedHousehold : $this->household;
    }

    public function hasHousehold(): bool
    {
        return $this->currentHousehold() !== null;
    }

    public function leaveHousehold(): void
    {
        $this->forceFill(['joined_household_id' => null])->save();
        $this->unsetRelation('joinedHousehold');
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
            'terms_accepted_at' => 'datetime',
            'marketing_opt_in_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
