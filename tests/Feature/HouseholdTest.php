<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_reach_the_household_pages(): void
    {
        $this->get(route('onboarding.menage'))->assertRedirect(route('login'));
        $this->get(route('household.edit'))->assertRedirect(route('login'));
    }

    public function test_saving_names_creates_the_household_with_zero_incomes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('onboarding.menage.store'), [
                'people' => [['name' => 'Toi'], ['name' => 'Conjoint(e)']],
            ])
            ->assertRedirect(route('household.edit'));

        $members = $user->fresh()->household->members;

        $this->assertCount(2, $members);
        $this->assertSame(['Toi', 'Conjoint(e)'], array_column($members, 'name'));
        $this->assertSame([0, 0], array_column($members, 'income'));
    }

    public function test_renaming_people_keeps_their_income_and_removing_one_prunes_personal_amounts(): void
    {
        $household = Household::factory()->create();
        [$a, $b] = $household->members;

        $this->actingAs($household->user)
            ->post(route('onboarding.menage.store'), [
                'people' => [['id' => $a['id'], 'name' => 'Christophe']],
            ])
            ->assertRedirect(route('household.edit'));

        $household->refresh();

        $this->assertSame('Christophe', $household->members[0]['name']);
        $this->assertEquals(5480, $household->members[0]['income']);
        $this->assertArrayNotHasKey($b['id'], $household->personal_expenses[0]['amounts']);
    }

    public function test_household_can_be_updated(): void
    {
        $household = Household::factory()->create();
        [$a, $b] = $household->members;

        $this->actingAs($household->user)
            ->put(route('household.update'), [
                'continue' => true,
                'members' => [
                    ['id' => $a['id'], 'name' => 'Toi', 'income' => 6000],
                    ['id' => $b['id'], 'name' => 'Conjoint(e)', 'income' => 4000],
                ],
                'common_expenses' => [['id' => 'c1', 'name' => 'Garderie', 'amount' => 180]],
                'personal_expenses' => [
                    ['id' => 'p1', 'name' => 'Prêt auto', 'amounts' => [$a['id'] => 272, $b['id'] => 500]],
                ],
            ])
            ->assertRedirect(route('housings.edit'));

        $household->refresh();

        $this->assertEquals(6000, $household->members[0]['income']);
        $this->assertSame('Garderie', $household->common_expenses[0]['name']);
        $this->assertEquals(500, $household->personal_expenses[0]['amounts'][$b['id']]);
    }

    public function test_negative_amounts_are_rejected(): void
    {
        $household = Household::factory()->create();
        [$a] = $household->members;

        $this->actingAs($household->user)
            ->put(route('household.update'), [
                'members' => [['id' => $a['id'], 'name' => 'Toi', 'income' => -5]],
                'common_expenses' => [],
                'personal_expenses' => [],
            ])
            ->assertSessionHasErrors('members.0.income');
    }

    public function test_edit_without_a_household_goes_back_to_the_onboarding(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('household.edit'))
            ->assertRedirect(route('onboarding.menage'));
    }
}
