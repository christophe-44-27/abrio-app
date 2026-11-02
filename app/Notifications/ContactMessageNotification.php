<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $body,
    ) {
        // The message goes to the team, who read French.
        $this->locale('fr');
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
        return (new MailMessage)
            ->subject(__('mail.contact.subject', ['name' => $this->senderName], 'fr'))
            ->replyTo($this->senderEmail, $this->senderName)
            ->line(__('mail.contact.from', ['name' => $this->senderName, 'email' => $this->senderEmail], 'fr'))
            ->line($this->body);
    }
}
