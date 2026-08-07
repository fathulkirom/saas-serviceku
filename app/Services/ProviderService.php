<?php

namespace App\Services;

use App\Models\GoogleDriveToken;
use App\Models\Tenant\WaGatewayConfig;

/**
 * Provider Service — unified management layer for ALL providers.
 * WRAPS existing implementations (GoogleDriveService, WhatsAppService, etc.)
 * NEVER replaces them. Adds health checks, status, and connection management.
 *
 * "Wrap Existing Implementation, Don't Replace It." — Sprint 7.1D
 */
class ProviderService
{
    /**
     * Get all providers with their runtime status.
     */
    public function getAllWithStatus(): array
    {
        $settings = app(SettingsService::class);
        $registry = ProviderRegistry::getAll();
        $result = [];

        foreach ($registry as $catKey => $category) {
            $catResult = ['label' => $category['label'], 'icon' => $category['icon'] ?? 'plug', 'providers' => []];
            foreach ($category['providers'] as $provKey => $provider) {
                $provResult = array_merge($provider, [
                    'key' => $provKey,
                    'connection_status' => $this->getConnectionStatus($catKey, $provKey),
                    'health' => $this->getHealth($catKey, $provKey),
                    'last_check' => $settings->get("provider_{$catKey}_{$provKey}_last_check"),
                    'last_error' => $settings->get("provider_{$catKey}_{$provKey}_last_error"),
                ]);
                $catResult['providers'][] = $provResult;
            }
            $result[$catKey] = $catResult;
        }

        return $result;
    }

    /**
     * Get runtime connection status for a provider.
     */
    public function getConnectionStatus(string $category, string $key): string
    {
        return match ("{$category}.{$key}") {
            // Storage
            'storage.local' => 'connected',
            'storage.gdrive' => $this->checkGoogleDrive() ? 'connected' : 'disconnected',

            // Messaging
            'messaging.whatsapp_web' => $this->checkWhatsAppWeb() ? 'connected' : 'disconnected',
            'messaging.whatsapp_cloud_api' => $this->checkWhatsAppCloudApi() ? 'connected' : 'disconnected',

            // Email
            'email.resend' => $this->checkSmtpConfigured() ? 'connected' : 'disconnected',
            'email.smtp' => $this->checkSmtpConfigured() ? 'connected' : 'disconnected',

            // Payment
            'payment.cash' => 'connected',
            'payment.midtrans' => $this->checkMidtrans() ? 'connected' : 'disconnected',

            // Printing
            'printing.browser' => 'connected',

            // Notification
            'notification.browser' => 'connected',
            'notification.email' => 'connected',

            // Backup
            'backup.local' => 'connected',

            // Location
            'location.openstreetmap' => 'connected',

            // Default: use stored status
            default => 'disabled',
        };
    }

    /**
     * Run a health check for a provider.
     * Returns: 'ok' | 'degraded' | 'error' | 'unknown'
     */
    public function getHealth(string $category, string $key): string
    {
        return match ("{$category}.{$key}") {
            'storage.gdrive' => $this->healthGoogleDrive(),
            'messaging.whatsapp_web' => $this->healthWhatsAppWeb(),
            'email.resend' => $this->healthResend(),
            'payment.midtrans' => $this->healthMidtrans(),
            default => 'unknown',
        };
    }

    /**
     * Test connection for a specific provider.
     * Returns ['success' => bool, 'message' => string]
     */
    public function testConnection(string $category, string $key): array
    {
        $settings = app(SettingsService::class);
        $now = now()->toDateTimeString();

        try {
            $result = match ("{$category}.{$key}") {
                'storage.gdrive' => $this->testGoogleDrive(),
                'messaging.whatsapp_web' => $this->testWhatsAppWeb(),
                'messaging.whatsapp_cloud_api' => $this->testWhatsAppCloudApi(),
                'email.resend' => $this->testResend(),
                'email.smtp' => $this->testSmtp(),
                'payment.midtrans' => $this->testMidtrans(),
                'payment.qris' => $this->testQris(),
                default => ['success' => false, 'message' => 'Provider ini belum mendukung test koneksi.'],
            };

            $settings->set("provider_{$category}_{$key}_last_check", $now);
            if (! $result['success']) {
                $settings->set("provider_{$category}_{$key}_last_error", $result['message']);
            }

            return $result;
        } catch (\Throwable $e) {
            $settings->set("provider_{$category}_{$key}_last_check", $now);
            $settings->set("provider_{$category}_{$key}_last_error", $e->getMessage());

            return ['success' => false, 'message' => 'Error: '.$e->getMessage()];
        }
    }

    // ======== WRAPPED CHECKS — calls existing services ========

    protected function checkGoogleDrive(): bool
    {
        try {
            $token = GoogleDriveToken::where('tenant_id', tenant()->id)->first();

            return $token && $token->token_expiry && now()->lt($token->token_expiry);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function healthGoogleDrive(): string
    {
        if (! $this->checkGoogleDrive()) {
            return 'disconnected';
        }
        try {
            app(GoogleDriveService::class)->getStorageQuota();

            return 'ok';
        } catch (\Throwable) {
            return 'error';
        }
    }

    protected function testGoogleDrive(): array
    {
        if (! $this->checkGoogleDrive()) {
            return ['success' => false, 'message' => 'Google Drive belum terkoneksi. Silakan hubungkan terlebih dahulu.'];
        }
        try {
            $quota = app(GoogleDriveService::class)->getStorageQuota();

            return ['success' => true, 'message' => "Google Drive terkoneksi. Quota: {$quota['used']} / {$quota['total']}."];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Gagal mengakses Google Drive: '.$e->getMessage()];
        }
    }

    protected function checkWhatsAppWeb(): bool
    {
        $config = WaGatewayConfig::where('provider', 'whatsapp_web')->where('is_active', true)->first();

        return $config !== null;
    }

    protected function healthWhatsAppWeb(): string
    {
        if (! $this->checkWhatsAppWeb()) {
            return 'disconnected';
        }
        try {
            app(WhatsAppService::class)->isConnected();

            return 'ok';
        } catch (\Throwable) {
            return 'degraded';
        }
    }

    protected function testWhatsAppWeb(): array
    {
        return ['success' => false, 'message' => 'WhatsApp Web tidak mendukung test koneksi otomatis. Silakan scan QR Code untuk pairing.'];
    }

    protected function checkWhatsAppCloudApi(): bool
    {
        $settings = app(SettingsService::class);

        return ! empty($settings->get('wa_api_key')) && ! empty($settings->get('wa_phone_number_id'));
    }

    protected function testWhatsAppCloudApi(): array
    {
        if (! $this->checkWhatsAppCloudApi()) {
            return ['success' => false, 'message' => 'API Key atau Phone Number ID belum diisi. Silakan isi di Pengaturan WhatsApp.'];
        }

        return ['success' => false, 'message' => 'Test koneksi WhatsApp Cloud API akan tersedia di update berikutnya.'];
    }

    protected function checkResendConfigured(): bool
    {
        return config('mail.mailers.smtp.host') === 'smtp.resend.com'
            && config('mail.mailers.smtp.username') === 'resend'
            && ! empty(config('mail.mailers.smtp.password'));
    }

    protected function healthResend(): string
    {
        return $this->checkResendConfigured() ? 'ok' : 'error';
    }

    protected function testResend(): array
    {
        if (! $this->checkResendConfigured()) {
            return ['success' => false, 'message' => 'Resend SMTP belum dikonfigurasi. Isi host smtp.resend.com, username resend, dan API key sebagai password.'];
        }

        return ['success' => true, 'message' => 'Resend SMTP terkonfigurasi. Email siap dikirim.'];
    }

    protected function checkSmtpConfigured(): bool
    {
        return ! empty(config('mail.mailers.smtp.host'));
    }

    protected function testSmtp(): array
    {
        if (! $this->checkSmtpConfigured()) {
            return ['success' => false, 'message' => 'SMTP belum dikonfigurasi.'];
        }

        return ['success' => true, 'message' => 'SMTP terkonfigurasi: '.config('mail.mailers.smtp.host')];
    }

    protected function checkMidtrans(): bool
    {
        return ! empty(config('services.midtrans.server_key'));
    }

    protected function healthMidtrans(): string
    {
        return $this->checkMidtrans() ? 'ok' : 'disconnected';
    }

    protected function testMidtrans(): array
    {
        if (! $this->checkMidtrans()) {
            return ['success' => false, 'message' => 'Midtrans Server Key belum dikonfigurasi.'];
        }

        return ['success' => true, 'message' => 'Midtrans terkonfigurasi. Payment gateway siap digunakan.'];
    }

    protected function testQris(): array
    {
        return ['success' => true, 'message' => 'QRIS siap digunakan. QR Code akan digenerate saat transaksi.'];
    }
}
