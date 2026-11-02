<?php

namespace App\Notifications;

use App\Models\HouseholdInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HouseholdInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $url,
        public string $inviter,
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
        $locale = $this->locale ?? 'fr';

        return (new MailMessage)
            ->subject(__('mail.invitation.subject', ['inviter' => $this->inviter], $locale))
            ->greeting(__('mail.invitation.hello', [], $locale))
            ->line(__('mail.invitation.intro', ['inviter' => $this->inviter], $locale))
            ->line(__('mail.invitation.details', [], $locale))
            ->action(__('mail.invitation.action', [], $locale), $this->url)
            ->line(__('mail.invitation.expires', ['days' => HouseholdInvitation::TTL_DAYS, 'inviter' => $this->inviter], $locale));
    }
}
