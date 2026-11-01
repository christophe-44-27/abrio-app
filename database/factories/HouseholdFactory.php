<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Household>
 */
class HouseholdFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $a = (string) Str::uuid();
        $b = (string) Str::uuid();

        return [
            'user_id' => User::factory(),
            'members' => [
                ['id' => $a, 'name' => 'Toi', 'income' => 5480],
                ['id' => $b, 'name' => 'Conjoint(e)', 'income' => 3800],
            ],
            'common_expenses' => [
                ['id' => (string) Str::uuid(), 'name' => 'Internet', 'amount' => 80],
            ],
            'personal_expenses' => [
                ['id' => (string) Str::uuid(), 'name' => 'Téléphone', 'amounts' => [$a => 55, $b => 0]],
            ],
        ];
    }
}
