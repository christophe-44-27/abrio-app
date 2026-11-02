<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\EmailVerificationCode;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        string $locale = 'fr',
    ) {
        $this->locale($locale);
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $locale = $this->locale ?? 'fr';

        return (new MailMessage)
            ->subject(__('mail.verify_code.subject', ['code' => $this->code], $locale))
            ->greeting(__('mail.greeting', ['name' => $notifiable->name], $locale))
            ->line(__('mail.verify_code.intro', [], $locale))
            ->line("**{$this->code}**")
            ->line(__('mail.verify_code.expires', ['minutes' => EmailVerificationCode::TTL_MINUTES], $locale));
    }
}
