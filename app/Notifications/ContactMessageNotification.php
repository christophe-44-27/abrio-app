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
    ) {}

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
            ->subject("Contact Abrio : {$this->senderName}")
            ->replyTo($this->senderEmail, $this->senderName)
            ->line("**De :** {$this->senderName} ({$this->senderEmail})")
            ->line($this->body);
    }
}
