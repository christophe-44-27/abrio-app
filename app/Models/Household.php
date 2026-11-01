<?php

namespace App\Models;

use Database\Factories\HouseholdFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A user's household budget.
 *
 * members:           [{id, name, income}]
 * common_expenses:   [{id, name, amount}], split by income share
 * personal_expenses: [{id, name, amounts: {memberId: amount}}]
 * housings:          [{id, name, rent, parking, heating, heating_amount, one_time}]
 *                    heating is 'included' (in the rent) or 'paid' (by the household)
 */
#[Fillable(['user_id', 'members', 'common_expenses', 'personal_expenses', 'housings'])]
class Household extends Model
{
    /** @use HasFactory<HouseholdFactory> */
    use HasFactory;

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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
