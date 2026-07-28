<?php

namespace App\Services;

use App\Models\SystemSetting;

class MailConfigService
{
    /**
     * Apply mail configuration from database settings.
     * Call this in AppServiceProvider boot() method.
     */
    public static function apply(): void
    {
        try {
            $driver = SystemSetting::getValue('mail_driver', 'log');

            if ($driver === 'smtp') {
                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp' => [
                        'transport' => 'smtp',
                        'host' => SystemSetting::getValue('mail_host', 'smtp.gmail.com'),
                        'port' => (int) SystemSetting::getValue('mail_port', 587),
                        'encryption' => SystemSetting::getValue('mail_encryption', 'tls'),
                        'username' => SystemSetting::getValue('mail_username'),
                        'password' => SystemSetting::getValue('mail_password'),
                        'timeout' => 30,
                    ],
                    'mail.from' => [
                        'address' => SystemSetting::getValue('mail_from_address', 'notifications@' . env('MAIL_DOMAIN', 'serviceku.my.id')),
                        'name' => SystemSetting::getValue('mail_from_name', 'ServiceKU'),
                    ],
                ]);
            }
        } catch (\Exception $e) {
            // Fallback ke log driver jika error
            config(['mail.default' => 'log']);
        }
    }

    /**
     * Test email configuration by sending a test email.
     */
    public static function test(string $toEmail): bool
    {
        try {
            self::apply();

            \Illuminate\Support\Facades\Mail::raw(
                'Email konfigurasi ServiceKU berhasil!',
                function ($message) use ($toEmail) {
                    $message->to($toEmail)
                        ->subject('Test Email ServiceKU');
                }
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
