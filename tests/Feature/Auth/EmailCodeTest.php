<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\VerifyEmailCodeNotification;
use App\Support\EmailVerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_can_be_verified_with_the_emailed_code(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        $user->sendEmailVerificationNotification();

        $code = null;
        Notification::assertSentTo($user, VerifyEmailCodeNotification::class, function ($notification) use (&$code) {
            $code = $notification->code;

            return true;
        });

        $this->actingAs($user)
            ->post(route('verification.code'), ['code' => $code])
            ->assertRedirect();

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_wrong_code_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();
        EmailVerificationCode::issue($user);

        $this->actingAs($user)
            ->post(route('verification.code'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_code_is_locked_after_too_many_attempts(): void
    {
        $user = User::factory()->unverified()->create();
        $code = EmailVerificationCode::issue($user);

        foreach (range(1, EmailVerificationCode::MAX_ATTEMPTS) as $i) {
            $this->assertFalse(EmailVerificationCode::check($user, '00000'.$i));
        }

        $this->assertFalse(EmailVerificationCode::check($user, $code));
    }
}
