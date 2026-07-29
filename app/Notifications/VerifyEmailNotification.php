<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email - ServiceKU')
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Silakan klik tombol di bawah untuk memverifikasi alamat email Anda.')
            ->action('Verifikasi Email', $verificationUrl)
            ->line('Jika Anda tidak membuat akun, abaikan email ini.')
            ->salutation('Tim ServiceKU');
    }

    protected function verificationUrl($notifiable): string
    {
        $tenant = tenancy()->tenant;
        $domain = $tenant->domains->first()?->domain
            ?? $tenant->slug . '.' . (config('tenancy.central_domains')[0] ?? 'serviceku.my.id');

        $scheme = config('app.env') === 'local' ? 'http' : 'https';

        return URL::temporarySignedRoute(
            'tenant.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
