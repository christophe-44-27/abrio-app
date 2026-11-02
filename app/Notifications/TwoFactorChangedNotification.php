<?php

namespace App\Notifications;

use App\Models\User;
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

    public function toMail(User $notifiable): MailMessage
    {
        $locale = $this->locale ?? 'fr';
        $when = now()->settings(['locale' => $locale])->isoFormat('LLLL');
        $where = $this->ip ? " ({$this->ip})" : '';
        $state = __('mail.two_factor.'.($this->enabled ? 'enabled' : 'disabled'), [], $locale);

        return (new MailMessage)
            ->subject(__('mail.two_factor.subject', ['state' => $state], $locale))
            ->greeting(__('mail.greeting', ['name' => $notifiable->name], $locale))
            ->line(__('mail.two_factor.intro', ['state' => $state, 'when' => $when, 'where' => $where], $locale))
            ->line(__('mail.two_factor.'.($this->enabled ? 'if_you_enabled' : 'if_you_disabled'), [], $locale))
            ->line(__('mail.two_factor.if_not_you', ['support' => config('app.support_email')], $locale))
            ->action(__('mail.two_factor.action', [], $locale), route('password.request'));
    }
}
