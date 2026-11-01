<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_language_can_be_switched_and_is_applied_to_auth_pages(): void
    {
        $this->from(route('login'))
            ->post(route('locale.update', 'en'))
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertInertia(fn ($page) => $page->where('locale', 'en'));
    }

    public function test_unknown_languages_are_rejected(): void
    {
        $this->post('/locale/de')->assertNotFound();
    }
}
