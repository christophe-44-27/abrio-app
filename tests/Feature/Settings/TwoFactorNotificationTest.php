<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Notifications\TwoFactorChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Tests\TestCase;

class TwoFactorNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_notified_when_two_factor_is_confirmed(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        event(new TwoFactorAuthenticationConfirmed($user));

        Notification::assertSentTo($user, TwoFactorChangedNotification::class, fn ($n) => $n->enabled);
    }

    public function test_user_is_notified_when_two_factor_is_disabled(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        event(new TwoFactorAuthenticationDisabled($user));

        Notification::assertSentTo($user, TwoFactorChangedNotification::class, fn ($n) => ! $n->enabled);
    }

    public function test_resetting_the_password_turns_two_factor_off_and_notifies(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt('[]'),
            'two_factor_confirmed_at' => now(),
        ]);

        app(ResetsUserPasswords::class)->reset($user, [
            'password' => 'a-brand-new-password-123',
            'password_confirmation' => 'a-brand-new-password-123',
        ]);

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        Notification::assertSentTo($user, TwoFactorChangedNotification::class, fn ($n) => ! $n->enabled);
    }

    public function test_the_email_tells_the_user_what_to_do_in_both_cases(): void
    {
        $user = User::factory()->create();

        foreach ([true, false] as $enabled) {
            $mail = (new TwoFactorChangedNotification($enabled, 'fr', '127.0.0.1'))->toMail($user);
            $text = implode(' ', $mail->introLines);

            $this->assertStringContainsString('Si c’est toi', $text);
            $this->assertStringContainsString('Si ce n’est pas toi', $text);
            $this->assertSame(route('password.request'), $mail->actionUrl);
        }
    }
}
