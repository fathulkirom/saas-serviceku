<?php

namespace App\Services;

/**
 * Provider Registry — catalogs ALL available providers (Blueprint v1.0, Sprint 6.2B).
 * Each provider has metadata for the management UI.
 * Actual implementations (GoogleDriveService, WhatsAppService, etc.) remain untouched.
 */
class ProviderRegistry
{
    /**
     * Get all provider categories with their providers.
     */
    public static function getAll(): array
    {
        return [
            'storage' => [
                'label' => 'Penyimpanan', 'icon' => 'hard-drive',
                'providers' => [
                    'local'     => ['label' => 'Local Storage', 'status' => 'connected', 'is_default' => true, 'description' => 'Penyimpanan di server'],
                    'gdrive'    => ['label' => 'Google Drive', 'status' => 'disconnected', 'description' => 'Simpan foto servis ke akun Google Drive tenant', 'config_keys' => ['gdrive_connected', 'gdrive_folder_id', 'gdrive_quota_used']],
                    's3'        => ['label' => 'Amazon S3', 'status' => 'disabled', 'plan' => 'pro', 'description' => 'Object storage untuk enterprise'],
                    'r2'        => ['label' => 'Cloudflare R2', 'status' => 'disabled', 'plan' => 'pro', 'description' => 'Alternatif S3 tanpa biaya egress'],
                    'nas'       => ['label' => 'NAS', 'status' => 'disabled', 'plan' => 'enterprise', 'description' => 'Network Attached Storage milik tenant'],
                ],
            ],
            'messaging' => [
                'label' => 'Pesan & WhatsApp', 'icon' => 'message-circle',
                'providers' => [
                    'whatsapp_web'      => ['label' => 'WhatsApp Web', 'status' => 'disconnected', 'is_default' => true, 'description' => 'Gratis — pairing QR Code. Default untuk UMKM.'],
                    'whatsapp_cloud_api'=> ['label' => 'WhatsApp Cloud API', 'status' => 'disabled', 'plan' => 'pro', 'description' => 'API resmi Meta. Multi-agent, template message.'],
                    'evolution_api'     => ['label' => 'Evolution API', 'status' => 'disabled', 'plan' => 'enterprise', 'description' => 'Self-hosted WhatsApp gateway.'],
                ],
            ],
            'email' => [
                'label' => 'Email', 'icon' => 'mail',
                'providers' => [
                    'brevo' => ['label' => 'Brevo (Sendinblue)', 'status' => 'connected', 'is_default' => true, 'description' => 'Default — OTP, reset password, notifikasi.'],
                    'smtp'  => ['label' => 'SMTP Kustom', 'status' => 'disabled', 'description' => 'Server email sendiri (Gmail, Zoho, dsb.)'],
                    'ses'   => ['label' => 'Amazon SES', 'status' => 'disabled', 'plan' => 'enterprise', 'description' => 'Email skala besar.'],
                ],
            ],
            'payment' => [
                'label' => 'Pembayaran', 'icon' => 'credit-card',
                'providers' => [
                    'cash'      => ['label' => 'Tunai', 'status' => 'connected', 'is_default' => true, 'description' => 'Pembayaran langsung di toko.'],
                    'qris'      => ['label' => 'QRIS', 'status' => 'disabled', 'description' => 'QR Code pembayaran standar Indonesia.'],
                    'midtrans'  => ['label' => 'Midtrans', 'status' => 'disconnected', 'description' => 'Payment gateway — QRIS, VA, CC, e-wallet.'],
                    'xendit'    => ['label' => 'Xendit', 'status' => 'disabled', 'plan' => 'pro', 'description' => 'Payment gateway alternatif.'],
                    'tripay'    => ['label' => 'Tripay', 'status' => 'disabled', 'description' => 'Payment gateway ekonomis.'],
                ],
            ],
            'printing' => [
                'label' => 'Pencetakan', 'icon' => 'printer',
                'providers' => [
                    'browser'           => ['label' => 'Browser Print', 'status' => 'connected', 'is_default' => true, 'description' => 'Cetak via browser.'],
                    'thermal_usb'       => ['label' => 'Thermal USB', 'status' => 'disabled', 'description' => 'Printer thermal via USB (ESC/POS).'],
                    'thermal_bluetooth' => ['label' => 'Thermal Bluetooth', 'status' => 'disabled', 'description' => 'Printer thermal nirkabel.'],
                    'network'           => ['label' => 'Network Printer', 'status' => 'disabled', 'description' => 'Printer jaringan (LAN/WiFi).'],
                ],
            ],
            'notification' => [
                'label' => 'Notifikasi', 'icon' => 'bell',
                'providers' => [
                    'browser' => ['label' => 'Browser Notification', 'status' => 'connected', 'is_default' => true, 'description' => 'Notifikasi real-time via browser.'],
                    'email'   => ['label' => 'Email Notification', 'status' => 'connected', 'is_default' => true, 'description' => 'Notifikasi via email.'],
                    'whatsapp'=> ['label' => 'WhatsApp Notification', 'status' => 'disconnected', 'description' => 'Notifikasi via WhatsApp.'],
                ],
            ],
            'backup' => [
                'label' => 'Backup', 'icon' => 'archive',
                'providers' => [
                    'local'  => ['label' => 'Local Backup', 'status' => 'connected', 'is_default' => true, 'description' => 'Backup ke server lokal.'],
                    'gdrive' => ['label' => 'Google Drive Backup', 'status' => 'disconnected', 'description' => 'Backup ke Google Drive tenant.'],
                    's3'     => ['label' => 'S3 Backup', 'status' => 'disabled', 'plan' => 'pro', 'description' => 'Backup ke S3/R2.'],
                    'nas'    => ['label' => 'NAS Backup', 'status' => 'disabled', 'plan' => 'enterprise', 'description' => 'Backup ke NAS.'],
                ],
            ],
            'location' => [
                'label' => 'Lokasi & Peta', 'icon' => 'map-pin',
                'providers' => [
                    'openstreetmap' => ['label' => 'OpenStreetMap', 'status' => 'connected', 'is_default' => true, 'description' => 'Gratis — geocoding dasar.'],
                    'google_maps'   => ['label' => 'Google Maps', 'status' => 'disabled', 'description' => 'Geocoding akurat (perlu API key).'],
                ],
            ],
            'marketplace' => [
                'label' => 'Marketplace (Future)', 'icon' => 'store', 'future' => true,
                'providers' => [
                    'tokopedia' => ['label' => 'Tokopedia', 'status' => 'disabled', 'future' => true],
                    'shopee'    => ['label' => 'Shopee', 'status' => 'disabled', 'future' => true],
                    'tiktok'    => ['label' => 'TikTok Shop', 'status' => 'disabled', 'future' => true],
                    'lazada'    => ['label' => 'Lazada', 'status' => 'disabled', 'future' => true],
                ],
            ],
            'ai' => [
                'label' => 'AI Assistant (Future)', 'icon' => 'sparkles', 'future' => true,
                'providers' => [
                    'openai'    => ['label' => 'OpenAI (GPT-4)', 'status' => 'disabled', 'future' => true],
                    'gemini'    => ['label' => 'Gemini', 'status' => 'disabled', 'future' => true],
                    'claude'    => ['label' => 'Claude', 'status' => 'disabled', 'future' => true],
                    'deepseek'  => ['label' => 'DeepSeek', 'status' => 'disabled', 'future' => true],
                    'local_llm' => ['label' => 'Local LLM', 'status' => 'disabled', 'future' => true],
                ],
            ],
        ];
    }

    /**
     * Get a single provider config by category and key.
     */
    public static function getProvider(string $category, string $key): ?array
    {
        return self::getAll()[$category]['providers'][$key] ?? null;
    }

    /**
     * Get all providers in a flat array.
     */
    public static function getAllFlat(): array
    {
        $flat = [];
        foreach (self::getAll() as $catKey => $category) {
            foreach ($category['providers'] as $provKey => $provider) {
                $flat["{$catKey}.{$provKey}"] = array_merge($provider, [
                    'category_key' => $catKey,
                    'category_label' => $category['label'],
                    'category_icon' => $category['icon'],
                ]);
            }
        }
        return $flat;
    }
}
