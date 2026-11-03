<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\UnlockCheckout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Events\WebhookReceived;
use Mockery;
use Mockery\MockInterface;
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

    /**
     * @return array<string, mixed>
     */
    private function paidSession(User $user): array
    {
        return [
            'payment_status' => 'paid',
            'client_reference_id' => (string) $user->id,
            'metadata' => ['purchase' => 'unlock'],
        ];
    }

    private function fakeCheckout(bool $configured = true): MockInterface
    {
        $checkout = Mockery::mock(UnlockCheckout::class);
        $checkout->shouldReceive('isConfigured')->andReturn($configured);
        $this->app->instance(UnlockCheckout::class, $checkout);

        return $checkout;
    }

    public function test_a_paid_checkout_session_unlocks_the_account(): void
    {
        $user = User::factory()->create();

        $this->webhook($this->paidSession($user));

        $this->assertTrue($user->fresh()->isUnlocked());
    }

    public function test_an_unpaid_session_does_not_unlock(): void
    {
        $user = User::factory()->create();

        $this->webhook(['payment_status' => 'unpaid'] + $this->paidSession($user));

        $this->assertFalse($user->fresh()->isUnlocked());
    }

    public function test_other_event_types_are_ignored(): void
    {
        $user = User::factory()->create();

        $this->webhook($this->paidSession($user), 'charge.refunded');

        $this->assertFalse($user->fresh()->isUnlocked());
    }

    public function test_only_sessions_created_for_the_unlock_purchase_unlock(): void
    {
        $user = User::factory()->create();

        $this->webhook([
            'payment_status' => 'paid',
            'client_reference_id' => (string) $user->id,
            'metadata' => ['purchase' => 'something_else'],
        ]);
        $this->assertFalse($user->fresh()->isUnlocked());

        $this->webhook([
            'payment_status' => 'paid',
            'client_reference_id' => (string) $user->id,
        ]);
        $this->assertFalse($user->fresh()->isUnlocked());
    }

    public function test_the_checkout_session_is_kept_to_find_the_invoice(): void
    {
        $user = User::factory()->create();

        $this->webhook(['id' => 'cs_test_123'] + $this->paidSession($user));

        $this->assertSame('cs_test_123', $user->fresh()->unlock_checkout_session_id);
    }

    public function test_unknown_user_ids_are_ignored(): void
    {
        $this->webhook(['payment_status' => 'paid', 'client_reference_id' => '999999', 'metadata' => ['purchase' => 'unlock']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_unlock_redirects_to_stripe_with_a_way_back_to_the_app(): void
    {
        $user = User::factory()->create();
        $checkout = $this->fakeCheckout();
        $checkout->shouldReceive('checkoutUrl')
            ->once()
            ->withArgs(fn ($u, $success, $cancel) => $u->is($user)
                && $success === route('unlock.success')
                && $cancel === url('/logements'))
            ->andReturn('https://checkout.stripe.com/c/pay/cs_test_abc');

        $this->actingAs($user)
            ->from('/logements')
            ->get(route('unlock'))
            ->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_abc');
    }

    public function test_the_back_arrow_falls_back_to_the_premium_page(): void
    {
        $checkout = $this->fakeCheckout();
        $checkout->shouldReceive('checkoutUrl')
            ->once()
            ->withArgs(fn ($u, $success, $cancel) => $cancel === route('premium.edit'))
            ->andReturn('https://checkout.stripe.com/c/pay/cs_test_abc');

        $this->actingAs(User::factory()->create())
            ->from('https://evil.example/phish')
            ->get(route('unlock'))
            ->assertRedirect();
    }

    public function test_unlock_fails_clearly_when_stripe_is_not_configured(): void
    {
        $this->fakeCheckout(configured: false);

        $this->actingAs(User::factory()->create())->get(route('unlock'))->assertStatus(503);
    }

    public function test_premium_page_offers_the_purchase_to_free_users(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('premium.edit'))
            ->assertInertia(fn ($page) => $page
                ->component('settings/Premium')
                ->where('unlocked', false)
                ->where('hasInvoice', false)
                ->where('housingLimit', User::FREE_HOUSING_LIMIT));
    }

    public function test_premium_page_shows_the_invoice_link_once_unlocked(): void
    {
        $user = User::factory()->create();
        $user->markUnlocked('cs_test_123');

        $this->actingAs($user->fresh())
            ->get(route('premium.edit'))
            ->assertInertia(fn ($page) => $page
                ->where('unlocked', true)
                ->where('hasInvoice', true)
                ->where('housingLimit', null));
    }

    public function test_invoice_redirects_to_the_stripe_hosted_page(): void
    {
        $user = User::factory()->create();
        $user->markUnlocked('cs_test_123');

        $checkout = $this->fakeCheckout();
        $checkout->shouldReceive('invoiceUrl')
            ->once()
            ->with('cs_test_123')
            ->andReturn('https://invoice.stripe.com/i/acct_1/test_abc');

        $this->actingAs($user->fresh())
            ->get(route('premium.invoice'))
            ->assertRedirect('https://invoice.stripe.com/i/acct_1/test_abc');
    }

    public function test_invoice_is_not_found_without_a_purchase(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('premium.invoice'))
            ->assertNotFound();
    }

    public function test_the_user_lands_back_where_they_started_with_a_toast(): void
    {
        $user = User::factory()->create();
        $this->fakeCheckout()->shouldReceive('checkoutUrl')->andReturn('https://checkout.stripe.com/c/pay/cs_test_abc');

        $this->actingAs($user)->from('/logements')->get(route('unlock'));

        $user->markUnlocked();

        $this->actingAs($user->fresh())
            ->get(route('unlock.success'))
            ->assertRedirect(url('/logements'))
            ->assertInertiaFlash('toast.message', __('settings.unlocked'));
    }

    public function test_success_page_waits_until_the_webhook_has_run(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('unlock.success'))->assertOk();

        $user->markUnlocked();

        $this->actingAs($user->fresh())
            ->get(route('unlock.success'))
            ->assertRedirect(route('verdicts'))
            ->assertInertiaFlash('toast.message', __('settings.unlocked'));
    }
}
