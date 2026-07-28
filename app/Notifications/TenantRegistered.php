<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantRegistered extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $tenantName = $notifiable->name ?? 'Toko Anda';
        $domain = $notifiable->domain ?? '';

        return (new MailMessage)
            ->subject('Selamat Datang di ServiceKU! 🎉')
            ->greeting('Halo ' . $tenantName . '!')
            ->line('Selamat, toko Anda berhasil terdaftar di ServiceKU.')
            ->line('Anda bisa langsung login dan mulai mengelola servis, inventaris, dan keuangan toko Anda.')
            ->action('Masuk ke Dashboard', url(($domain ? 'https://' . $domain : config('app.url')) . '/dashboard'))
            ->line('Butuh bantuan? Hubungi tim support kami di support@serviceku.my.id')
            ->line('Terima kasih telah memilih ServiceKU!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
