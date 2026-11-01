<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_users_with_a_household_but_no_housings_resume_at_the_housings_step()
    {
        $user = User::factory()->has(Household::factory())->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('housings.edit'));
    }

    public function test_users_with_housings_resume_at_the_verdicts()
    {
        $user = User::factory()
            ->has(Household::factory()->state(['housings' => [[
                'id' => 'a', 'name' => 'Logement', 'rent' => 1000, 'parking' => 0,
                'heating' => 'included', 'heating_amount' => 0, 'one_time' => 0,
            ]]]))
            ->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('verdicts'));
    }

    public function test_users_without_a_household_are_sent_to_the_onboarding()
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))->assertRedirect(route('onboarding.bienvenue'));
    }
}
