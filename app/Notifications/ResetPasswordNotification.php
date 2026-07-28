<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Build reset URL berdasarkan domain saat ini
        $domain = request()->getHost();
        $port = request()->getPort();
        $portSuffix = ($port !== 80 && $port !== 443) ? ':' . $port : '';
        $protocol = request()->secure() ? 'https://' : 'http://';

        $url = $protocol . $domain . $portSuffix . '/reset-password/' . $this->token . '?email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Reset Password - ServiceKU')
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.')
            ->action('Reset Password', $url)
            ->line('Link reset password ini akan kadaluarsa dalam 60 menit.')
            ->line('Jika Anda tidak meminta reset password, abaikan email ini.')
            ->salutation('Salam, Tim ServiceKU');
    }
}
