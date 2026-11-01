<?php

namespace App\Notifications;

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

    public function toMail(object $notifiable): MailMessage
    {
        $minutes = EmailVerificationCode::TTL_MINUTES;

        if ($this->locale === 'en') {
            return (new MailMessage)
                ->subject("Your Abrio code: {$this->code}")
                ->greeting("Hi {$notifiable->name},")
                ->line('Use this code to verify your email address:')
                ->line("**{$this->code}**")
                ->line("It expires in {$minutes} minutes. If you didn't create an Abrio account, you can ignore this email.");
        }

        return (new MailMessage)
            ->subject("Ton code Abrio : {$this->code}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line('Utilise ce code pour valider ton adresse courriel :')
            ->line("**{$this->code}**")
            ->line("Il expire dans {$minutes} minutes. Si tu n'as pas créé de compte Abrio, ignore ce courriel.");
    }
}
