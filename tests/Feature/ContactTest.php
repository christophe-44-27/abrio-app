<?php

namespace Tests\Feature;

use App\Notifications\ContactMessageNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContactTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_a_contact_message_is_sent_to_the_support_address(): void
    {
        $this->post('/contact', [
            'name' => 'Marie',
            'email' => 'marie@example.com',
            'message' => 'Une question sur le calcul.',
        ])->assertSessionHasNoErrors();

        Notification::assertSentOnDemand(
            ContactMessageNotification::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === config('app.support_email')
                && $notification->senderEmail === 'marie@example.com',
        );
    }

    public function test_a_contact_message_is_validated(): void
    {
        $this->post('/contact', ['name' => '', 'email' => 'nope', 'message' => 'court'])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        Notification::assertNothingSent();
    }

    public function test_a_filled_honeypot_sends_nothing(): void
    {
        $this->post('/contact', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'Achetez mes produits maintenant.',
            'website' => 'http://spam.test',
        ])->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }
}
