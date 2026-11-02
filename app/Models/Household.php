<?php

namespace App\Models;

use Database\Factories\HouseholdFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * A household budget, owned by one user and shared with the people they invite.
 *
 * members:           [{id, name, income}]
 * common_expenses:   [{id, name, amount}], split by income share
 * personal_expenses: [{id, name, amounts: {memberId: amount}}]
 * housings:          [{id, name, rent, parking, heating, heating_amount, one_time}]
 *                    heating is 'included' (in the rent) or 'paid' (by the household)
 *
 * @property int $id
 * @property int $user_id
 * @property array<int, array{id: string, name: string, income: int|float}> $members
 * @property array<int, array{id: string, name: string, amount: int|float}> $common_expenses
 * @property array<int, array{id: string, name: string, amounts: array<string, int|float>}> $personal_expenses
 * @property array<int, array{id: string, name: string, rent: int|float, parking: int|float, heating: 'included'|'paid', heating_amount: int|float, one_time: int|float}>|null $housings
 * @property int $revision
 * @property string|null $share_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Fillable(['user_id', 'members', 'common_expenses', 'personal_expenses', 'housings', 'share_token'])]
class Household extends Model
{
    /** @use HasFactory<HouseholdFactory> */
    use HasFactory;

    /** Owner included. */
    public const MAX_USERS = 5;

    protected function casts(): array
    {
        return [
            'members' => 'array',
            'common_expenses' => 'array',
            'personal_expenses' => 'array',
            'housings' => 'array',
        ];
    }

    /**
     * The owner: the only one who pays and manages the team.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * People who joined, not counting the owner.
     *
     * @return HasMany<User, $this>
     */
    public function team(): HasMany
    {
        return $this->hasMany(User::class, 'joined_household_id');
    }

    /**
     * @return HasMany<HouseholdInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(HouseholdInvitation::class);
    }

    /**
     * Save an edit, unless someone saved since the editor loaded the page
     * (their $revision is then stale). Returns false when the edit is refused.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function saveEdit(array $attributes, ?int $revision = null): bool
    {
        return DB::transaction(function () use ($attributes, $revision) {
            $current = (int) static::whereKey($this->getKey())->lockForUpdate()->value('revision');

            if ($revision !== null && $revision !== $current) {
                return false;
            }

            $this->forceFill([...$attributes, 'revision' => $current + 1])->save();

            return true;
        });
    }

    public function isUnlocked(): bool
    {
        return $this->user->hasPaid();
    }

    /**
     * Seats left for new invitations (members and pending invitations count).
     */
    public function freeSeats(): int
    {
        return max(0, self::MAX_USERS - 1 - $this->team()->count() - $this->invitations()->count());
    }

    public function startSharing(): string
    {
        $this->update(['share_token' => Str::random(40)]);

        return $this->share_token;
    }

    public function stopSharing(): void
    {
        $this->update(['share_token' => null]);
    }
}
