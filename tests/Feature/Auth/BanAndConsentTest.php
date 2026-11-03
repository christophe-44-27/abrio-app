<?php

namespace Tests\Feature\Auth;

use App\Models\Ban;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BanAndConsentTest extends TestCase
{
    use RefreshDatabase;

    private function registration(array $overrides = []): array
    {
        return [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'country' => 'CA',
            'province' => 'QC',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
            ...$overrides,
        ];
    }

    public function test_registration_records_ip_and_consents()
    {
        $this->post(route('register.store'), $this->registration(['marketing' => '1']));

        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertNotNull($user->registration_ip);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->marketing_opt_in_at);
    }

    public function test_marketing_is_off_unless_ticked()
    {
        $this->post(route('register.store'), $this->registration());

        $this->assertNull(User::firstOrFail()->marketing_opt_in_at);
    }

    public function test_terms_are_required()
    {
        $this->post(route('register.store'), $this->registration(['terms' => null]))
            ->assertSessionHasErrors('terms');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_banned_email_cannot_register()
    {
        Ban::create(['email' => 'Test@Example.com']);

        $this->post(route('register.store'), $this->registration())
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_banned_ip_cannot_register()
    {
        Ban::create(['ip_address' => '127.0.0.1']);

        $this->post(route('register.store'), $this->registration())
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_banned_user_is_logged_out()
    {
        $user = User::factory()->create();
        Ban::create(['email' => $user->email]);

        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();

        $this->assertGuest();
    }

    public function test_profile_toggles_marketing()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'marketing' => '1',
        ]);

        $this->assertNotNull($user->fresh()->marketing_opt_in_at);

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $this->assertNull($user->fresh()->marketing_opt_in_at);
    }
}
