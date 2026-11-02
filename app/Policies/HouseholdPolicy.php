<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\User;

/**
 * Members can edit everything in the household; only its owner manages the
 * team, the payment and the public link.
 */
class HouseholdPolicy
{
    public function manage(User $user, Household $household): bool
    {
        return $user->ownsHousehold($household);
    }
}
