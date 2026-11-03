<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'country' => 'CA',
            'province' => 'QC',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'country' => 'CA',
            'province' => 'QC',
        ]);
    }

    public function test_province_must_belong_to_the_selected_country()
    {
        $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'country' => 'CA',
            'province' => 'TX',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ])->assertSessionHasErrors('province');

        $this->assertGuest();
    }

    public function test_province_is_optional_outside_canada_and_the_us()
    {
        $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'country' => 'FR',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticated();
    }
}
