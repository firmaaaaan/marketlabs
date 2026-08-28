<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public ?string $message = null,
        public ?string $url = null,
        public bool $notifyViaEmail = false,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->notifyViaEmail
            ? ['database', 'mail']
            : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title . ' – MarketLabs')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line($this->message);

        if ($this->url) {
            $mail->action('Lihat Detail', $this->url);
        }

        return $mail
            ->salutation('Hormat kami,<br><strong>Tim MarketLabs</strong><br><small style="color: #94a3b8;">UPT Laboratorium Terpadu</small>');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}
