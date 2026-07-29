<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode Verifikasi 2FA - ServiceKU')
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Berikut adalah kode verifikasi 2FA Anda:')
            ->line("**{$this->code}**")
            ->line('Kode ini berlaku selama 5 menit.')
            ->line('Jika Anda tidak melakukan login, abaikan email ini.')
            ->salutation('Tim ServiceKU');
    }
}
