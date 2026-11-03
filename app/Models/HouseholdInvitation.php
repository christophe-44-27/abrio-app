<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * An owner's invitation to join their household. Only the hash of the token
 * is stored; the plain token only exists in the invitation email.
 *
 * @property int $id
 * @property int $household_id
 * @property int $invited_by
 * @property string $email
 * @property string $token
 * @property Carbon $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Household $household
 * @property-read User $inviter
 */
#[Fillable(['household_id', 'invited_by', 'email', 'token', 'expires_at'])]
class HouseholdInvitation extends Model
{
    public const TTL_DAYS = 7;

    /** Session key that carries a followed invitation link through to registration. */
    private const SESSION_KEY = 'invitation_token';

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * A usable invitation for this plain token, if there is one.
     */
    public static function findValid(string $token): ?self
    {
        return self::with('inviter')
            ->where('token', self::hash($token))
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Keep the followed link until the visitor has registered.
     */
    public static function remember(string $token): void
    {
        session([self::SESSION_KEY => $token]);
    }

    /**
     * The invitation whose link the visitor followed, if still usable.
     */
    public static function pending(): ?self
    {
        $token = session(self::SESSION_KEY);

        return $token ? self::findValid($token) : null;
    }

    /**
     * Put the user in the household. Having received the invitation proves
     * the address is theirs, so it counts as verified.
     */
    public function acceptBy(User $user): void
    {
        $user->forceFill([
            'joined_household_id' => $this->household_id,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        $this->delete();
        session()->forget(self::SESSION_KEY);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * @return BelongsTo<Household, $this>
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
