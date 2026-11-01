<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Events\WebhookReceived;
use Tests\TestCase;

class UnlockTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $session
     */
    private function webhook(array $session, string $type = 'checkout.session.completed'): void
    {
        event(new WebhookReceived(['type' => $type, 'data' => ['object' => $session]]));
    }

    public function test_a_paid_checkout_session_unlocks_the_account(): void
    {
        $user = User::factory()->create();

        $this->webhook(['payment_status' => 'paid', 'client_reference_id' => (string) $user->id]);

        $this->assertTrue($user->fresh()->isUnlocked());
    }

    public function test_an_unpaid_session_does_not_unlock(): void
    {
        $user = User::factory()->create();

        $this->webhook(['payment_status' => 'unpaid', 'client_reference_id' => (string) $user->id]);

        $this->assertFalse($user->fresh()->isUnlocked());
    }

    public function test_other_event_types_are_ignored(): void
    {
        $user = User::factory()->create();

        $this->webhook(
            ['payment_status' => 'paid', 'client_reference_id' => (string) $user->id],
            'charge.refunded',
        );

        $this->assertFalse($user->fresh()->isUnlocked());
    }

    public function test_only_the_configured_payment_link_unlocks(): void
    {
        config(['billing.unlock.payment_link_id' => 'plink_unlock']);
        $user = User::factory()->create();

        $this->webhook([
            'payment_status' => 'paid',
            'client_reference_id' => (string) $user->id,
            'payment_link' => 'plink_other',
        ]);
        $this->assertFalse($user->fresh()->isUnlocked());

        $this->webhook([
            'payment_status' => 'paid',
            'client_reference_id' => (string) $user->id,
            'payment_link' => 'plink_unlock',
        ]);
        $this->assertTrue($user->fresh()->isUnlocked());
    }

    public function test_unknown_user_ids_are_ignored(): void
    {
        $this->webhook(['payment_status' => 'paid', 'client_reference_id' => '999999']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_unlock_redirects_to_the_payment_link_with_the_user_reference(): void
    {
        config(['billing.unlock.link' => 'https://buy.stripe.com/test_abc']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('unlock'));

        $response->assertRedirectContains('https://buy.stripe.com/test_abc?');
        $response->assertRedirectContains('client_reference_id='.$user->id);
    }

    public function test_unlock_fails_clearly_when_no_link_is_configured(): void
    {
        config(['billing.unlock.link' => null]);

        $this->actingAs(User::factory()->create())->get(route('unlock'))->assertStatus(503);
    }

    public function test_success_page_waits_until_the_webhook_has_run(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('unlock.success'))->assertOk();

        $user->markUnlocked();

        $this->actingAs($user->fresh())
            ->get(route('unlock.success'))
            ->assertRedirect(route('verdicts', ['unlocked' => 1]));
    }
}
