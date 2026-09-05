<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ]));

        $expire = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Reset Kata Sandi – MarketLabs')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun MarketLabs Anda.')
            ->line('Klik tombol di bawah ini untuk membuat kata sandi baru.')
            ->action('Atur Ulang Kata Sandi', $url)
            ->line('Tautan ini akan kedaluwarsa dalam '.$expire.' menit.')
            ->line('Jika Anda tidak meminta pengaturan ulang, abaikan email ini. Kata sandi Anda tetap aman.')
            ->salutation('Hormat kami,<br><strong>Tim MarketLabs</strong><br><small style="color: #94a3b8;">UPT Laboratorium Terpadu</small>');
    }
}
