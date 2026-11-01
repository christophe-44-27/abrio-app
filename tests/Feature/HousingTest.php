<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HousingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function housing(string $id, array $overrides = []): array
    {
        return [
            'id' => $id,
            'name' => "Logement {$id}",
            'rent' => 1700,
            'parking' => 85,
            'heating' => 'paid',
            'heating_amount' => 140,
            'one_time' => 400,
            ...$overrides,
        ];
    }

    public function test_housings_can_be_saved_and_lead_to_the_verdicts(): void
    {
        $household = Household::factory()->create();

        $this->actingAs($household->user)
            ->put(route('housings.update'), ['housings' => [$this->housing('a')], 'continue' => true])
            ->assertRedirect(route('verdicts'));

        $this->assertSame('Logement a', $household->fresh()->housings[0]['name']);
    }

    public function test_background_saves_stay_on_the_page(): void
    {
        $household = Household::factory()->create();

        $this->actingAs($household->user)
            ->from(route('housings.edit'))
            ->put(route('housings.update'), ['housings' => [$this->housing('a')]])
            ->assertRedirect(route('housings.edit'));

        $this->assertSame('Logement a', $household->fresh()->housings[0]['name']);
    }

    public function test_heating_amount_is_zeroed_when_included_in_the_rent(): void
    {
        $household = Household::factory()->create();

        $this->actingAs($household->user)
            ->put(route('housings.update'), [
                'housings' => [$this->housing('a', ['heating' => 'included', 'heating_amount' => 99])],
            ]);

        $this->assertEquals(0, $household->fresh()->housings[0]['heating_amount']);
    }

    public function test_free_accounts_are_limited_to_three_housings(): void
    {
        $household = Household::factory()->create();
        $four = array_map(fn ($id) => $this->housing($id), ['a', 'b', 'c', 'd']);

        $this->actingAs($household->user)
            ->put(route('housings.update'), ['housings' => $four])
            ->assertSessionHasErrors('housings');

        $this->actingAs($household->user)
            ->put(route('housings.update'), ['housings' => array_slice($four, 0, 3)])
            ->assertSessionHasNoErrors();
    }

    public function test_unlocked_accounts_have_no_limit(): void
    {
        $user = User::factory()->create(['unlocked_at' => now()]);
        Household::factory()->for($user)->create();
        $five = array_map(fn ($id) => $this->housing($id), ['a', 'b', 'c', 'd', 'e']);

        $this->actingAs($user)
            ->put(route('housings.update'), ['housings' => $five])
            ->assertSessionHasNoErrors();

        $this->assertCount(5, $user->fresh()->household->housings);
    }

    public function test_verdicts_require_at_least_one_housing(): void
    {
        $household = Household::factory()->create();

        $this->actingAs($household->user)
            ->get(route('verdicts'))
            ->assertRedirect(route('housings.edit'));

        $household->update(['housings' => [$this->housing('a')]]);

        $this->actingAs($household->user->fresh())->get(route('verdicts'))->assertOk();
    }

    public function test_negative_rent_is_rejected(): void
    {
        $household = Household::factory()->create();

        $this->actingAs($household->user)
            ->put(route('housings.update'), ['housings' => [$this->housing('a', ['rent' => -1])]])
            ->assertSessionHasErrors('housings.0.rent');
    }
}
