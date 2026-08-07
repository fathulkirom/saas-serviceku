<?php

namespace App\Services;

use App\Models\SystemSetting;

class MailConfigService
{
    /**
     * Apply mail configuration from database settings.
     * Call this in AppServiceProvider boot() method.
     * When a tenant context is active and the tenant has enabled their own
     * mail config, tenant SMTP settings override the central defaults.
     */
    public static function apply(): void
    {
        try {
            $driver = SystemSetting::getValue('mail_driver', 'log');

            // Check tenant-level mail override (tenant owns their SMTP config).
            $tenantMail = self::tenantSettings();
            if ($tenantMail && ($tenantMail['mail_enabled'] ?? 'false') === 'true') {
                $driver = 'smtp';
            }

            if ($driver === 'smtp') {
                $host = SystemSetting::getValue('mail_host', 'smtp.gmail.com');
                $port = (int) SystemSetting::getValue('mail_port', 587);
                $encryption = SystemSetting::getValue('mail_encryption', 'tls');
                $username = SystemSetting::getValue('mail_username');
                $password = SystemSetting::getValue('mail_password');
                $fromAddress = SystemSetting::getValue('mail_from_address', 'notifications@' . env('MAIL_DOMAIN', 'serviceku.my.id'));
                $fromName = SystemSetting::getValue('mail_from_name', 'ServiceKU');

                // Tenant-specific override: use tenant's own SMTP credentials + branding.
                if ($tenantMail && ($tenantMail['mail_enabled'] ?? 'false') === 'true') {
                    $host     = $tenantMail['mail_host'] ?? $host;
                    $port     = (int) ($tenantMail['mail_port'] ?? $port);
                    $encryption = $tenantMail['mail_encryption'] ?? $encryption;
                    $username = $tenantMail['mail_username'] ?? $username;
                    $password = $tenantMail['mail_password'] ?? $password;
                    $fromAddress = $tenantMail['mail_from_address'] ?? $fromAddress;
                    $fromName   = $tenantMail['mail_from_name'] ?? $fromName;
                }

                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp' => [
                        'transport' => 'smtp',
                        'host' => $host,
                        'port' => $port,
                        'encryption' => $encryption,
                        'username' => $username,
                        'password' => $password,
                        'timeout' => 30,
                    ],
                    'mail.from' => [
                        'address' => $fromAddress,
                        'name' => $fromName,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            // Fallback ke log driver jika error
            config(['mail.default' => 'log']);
        }
    }

    /**
     * Read tenant-level mail settings from the tenant database (key-value table).
     * Returns null when no tenant context is active.
     */
    private static function tenantSettings(): ?array
    {
        try {
            if (!tenancy()->initialized) return null;
            $rows = \Illuminate\Support\Facades\DB::table('tenant_settings')
                ->whereIn('key', [
                    'mail_enabled', 'mail_host', 'mail_port', 'mail_encryption',
                    'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name',
                ])->pluck('value', 'key');
            return $rows->isEmpty() ? null : $rows->toArray();
        } catch (\Exception $e) {
            return null;
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
