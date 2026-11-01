<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public bool $enabled,
        string $locale = 'fr',
        public ?string $ip = null,
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
        $when = now()->locale($this->locale ?? 'fr')->isoFormat('LLLL');
        $where = $this->ip ? " ({$this->ip})" : '';
        $resetUrl = route('password.request');
        $support = config('app.support_email');

        if ($this->locale === 'en') {
            $state = $this->enabled ? 'enabled' : 'disabled';

            return (new MailMessage)
                ->subject("Two-factor authentication was {$state} on your Abrio account")
                ->greeting("Hi {$notifiable->name},")
                ->line("Two-factor authentication was {$state} on your Abrio account on {$when}{$where}.")
                ->line($this->enabled
                    ? 'If it was you, there is nothing to do: your account is now better protected.'
                    : 'If it was you, there is nothing to do. You can turn it back on anytime in your account settings.')
                ->line("If it wasn't you, someone else may have access to your account. Change your password right away, then contact us at {$support} so we can help.")
                ->action('Change my password', $resetUrl);
        }

        $state = $this->enabled ? 'activée' : 'désactivée';

        return (new MailMessage)
            ->subject("La double authentification a été {$state} sur ton compte Abrio")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("La double authentification a été {$state} sur ton compte Abrio le {$when}{$where}.")
            ->line($this->enabled
                ? 'Si c’est toi, tu n’as rien à faire : ton compte est simplement mieux protégé.'
                : 'Si c’est toi, tu n’as rien à faire. Tu peux la réactiver quand tu veux dans les paramètres du compte.')
            ->line("Si ce n’est pas toi, quelqu’un d’autre a peut-être accès à ton compte. Change ton mot de passe tout de suite, puis écris-nous à {$support} pour qu’on t’aide.")
            ->action('Changer mon mot de passe', $resetUrl);
    }
}
