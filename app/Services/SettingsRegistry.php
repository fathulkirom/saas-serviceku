<?php

namespace App\Services;

/**
 * Settings Registry — defines ALL available tenant settings (Blueprint v1.0).
 * Single source of truth for settings structure, groups, types, and defaults.
 *
 * Used by SettingsService for CRUD operations and Vue settings UI rendering.
 */
class SettingsRegistry
{
    /**
     * Get all settings grouped by category.
     */
    public static function getAll(): array
    {
        return [
            'general' => [
                'label' => 'Umum',
                'icon' => 'building',
                'settings' => [
                    'company_name'     => ['label' => 'Nama Toko', 'type' => 'text', 'default' => '', 'required' => true],
                    'company_email'    => ['label' => 'Email Toko', 'type' => 'email', 'default' => '', 'required' => true],
                    'company_phone'    => ['label' => 'Telepon', 'type' => 'text', 'default' => ''],
                    'company_address'  => ['label' => 'Alamat', 'type' => 'textarea', 'default' => ''],
                    'company_logo'     => ['label' => 'Logo', 'type' => 'image', 'default' => null],
                    'business_hours_open'  => ['label' => 'Jam Buka', 'type' => 'time', 'default' => '08:00'],
                    'business_hours_close' => ['label' => 'Jam Tutup', 'type' => 'time', 'default' => '20:00'],
                    'timezone'         => ['label' => 'Zona Waktu', 'type' => 'select', 'default' => 'Asia/Jakarta'],
                    'currency'         => ['label' => 'Mata Uang', 'type' => 'text', 'default' => 'IDR', 'readonly' => true],
                    'language'         => ['label' => 'Bahasa', 'type' => 'select', 'default' => 'id'],
                    'tax_name'         => ['label' => 'Nama Pajak', 'type' => 'text', 'default' => 'PPN'],
                    'tax_rate'         => ['label' => 'Tarif Pajak (%)', 'type' => 'number', 'default' => 11],
                ],
            ],
            'business' => [
                'label' => 'Bisnis',
                'icon' => 'briefcase',
                'settings' => [
                    'business_type' => ['label' => 'Tipe Bisnis', 'type' => 'select', 'default' => 'full_service',
                        'options' => ['full_service' => 'Pusat Service + Sparepart', 'aksesoris_service' => 'Aksesoris + Service', 'gadget_full' => 'HP/Laptop Baru + Service', 'retail_only' => 'Retail'],
                        'description' => 'Template awal — dapat diubah dengan mengaktifkan/menonaktifkan modul.'],
                ],
            ],
            'modules' => [
                'label' => 'Modul',
                'icon' => 'package',
                'settings' => [],
                'dynamic' => true, // Rendered from Module Registry (Sprint 7.1B)
            ],
            'subscription' => [
                'label' => 'Langganan',
                'icon' => 'credit-card',
                'settings' => [
                    'plan_id'          => ['label' => 'Paket', 'type' => 'readonly', 'default' => null],
                    'subscription_status' => ['label' => 'Status', 'type' => 'readonly', 'default' => 'trial'],
                    'trial_ends_at'    => ['label' => 'Trial Berakhir', 'type' => 'readonly', 'default' => null],
                    'subscription_ends_at' => ['label' => 'Langganan Berakhir', 'type' => 'readonly', 'default' => null],
                ],
            ],
            'notification' => [
                'label' => 'Notifikasi',
                'icon' => 'bell',
                'settings' => [
                    'notify_email_enabled' => ['label' => 'Notifikasi Email', 'type' => 'boolean', 'default' => true],
                    'notify_wa_enabled'    => ['label' => 'Notifikasi WhatsApp', 'type' => 'boolean', 'default' => false],
                    'notify_browser_enabled'=> ['label' => 'Notifikasi Browser', 'type' => 'boolean', 'default' => true],
                    'notify_service_created'   => ['label' => 'Servis Baru', 'type' => 'boolean', 'default' => true],
                    'notify_service_completed' => ['label' => 'Servis Selesai', 'type' => 'boolean', 'default' => true],
                    'notify_payment_received'  => ['label' => 'Pembayaran Diterima', 'type' => 'boolean', 'default' => true],
                    'notify_stock_low'         => ['label' => 'Stok Menipis', 'type' => 'boolean', 'default' => true],
                ],
            ],
            'messaging' => [
                'label' => 'WhatsApp',
                'icon' => 'message-circle',
                'settings' => [
                    'wa_provider'       => ['label' => 'Provider', 'type' => 'select', 'default' => 'whatsapp_web',
                        'options' => ['whatsapp_web' => 'WhatsApp Web', 'whatsapp_cloud_api' => 'Cloud API', 'evolution_api' => 'Evolution API']],
                    'wa_api_key'        => ['label' => 'API Key / Token', 'type' => 'password', 'default' => null],
                    'wa_phone_number_id'=> ['label' => 'Phone Number ID', 'type' => 'text', 'default' => null],
                    'wa_template_service_received' => ['label' => 'Template: Servis Diterima', 'type' => 'textarea', 'default' => null],
                    'wa_template_service_finished' => ['label' => 'Template: Servis Selesai', 'type' => 'textarea', 'default' => null],
                ],
            ],
            'email' => [
                'label' => 'Email',
                'icon' => 'mail',
                'settings' => [
                    'mail_provider'     => ['label' => 'Provider', 'type' => 'select', 'default' => 'brevo',
                        'options' => ['brevo' => 'Brevo', 'smtp' => 'SMTP Kustom', 'ses' => 'Amazon SES']],
                    'mail_from_address' => ['label' => 'Alamat Pengirim', 'type' => 'email', 'default' => null],
                    'mail_from_name'    => ['label' => 'Nama Pengirim', 'type' => 'text', 'default' => null],
                ],
            ],
            'storage' => [
                'label' => 'Penyimpanan',
                'icon' => 'hard-drive',
                'settings' => [
                    'storage_provider'  => ['label' => 'Provider', 'type' => 'select', 'default' => 'local',
                        'options' => ['local' => 'Local', 's3' => 'Amazon S3', 'r2' => 'Cloudflare R2', 'gdrive' => 'Google Drive', 'nas' => 'NAS']],
                    'storage_quota_used' => ['label' => 'Terpakai', 'type' => 'readonly', 'default' => 0],
                    'storage_quota_limit'=> ['label' => 'Kuota', 'type' => 'readonly', 'default' => 100],
                ],
            ],
            'printer' => [
                'label' => 'Printer',
                'icon' => 'printer',
                'settings' => [
                    'printer_type'      => ['label' => 'Tipe Printer', 'type' => 'select', 'default' => 'browser',
                        'options' => ['browser' => 'Browser', 'thermal_usb' => 'Thermal USB', 'thermal_bluetooth' => 'Thermal Bluetooth', 'network' => 'Network']],
                    'printer_paper_size'=> ['label' => 'Ukuran Kertas', 'type' => 'select', 'default' => '58mm',
                        'options' => ['58mm' => '58mm (Struk)', '80mm' => '80mm (Nota)', 'A4' => 'A4']],
                ],
            ],
            'theme' => [
                'label' => 'Tema',
                'icon' => 'palette',
                'settings' => [
                    'theme_mode'        => ['label' => 'Mode', 'type' => 'select', 'default' => 'system',
                        'options' => ['light' => 'Terang', 'dark' => 'Gelap', 'system' => 'Sistem']],
                    'theme_primary_color'=> ['label' => 'Warna Utama', 'type' => 'color', 'default' => '#2563EB'],
                    'theme_sidebar_style'=> ['label' => 'Gaya Sidebar', 'type' => 'select', 'default' => 'expanded',
                        'options' => ['expanded' => 'Membuka', 'collapsed' => 'Ringkas']],
                ],
            ],
            'security' => [
                'label' => 'Keamanan',
                'icon' => 'shield',
                'settings' => [
                    'security_2fa_required'    => ['label' => '2FA Wajib', 'type' => 'boolean', 'default' => false],
                    'security_session_timeout'  => ['label' => 'Timeout Sesi (menit)', 'type' => 'number', 'default' => 120],
                    'security_login_history'    => ['label' => 'Riwayat Login', 'type' => 'boolean', 'default' => true],
                    'security_api_enabled'      => ['label' => 'API Aktif', 'type' => 'boolean', 'default' => false],
                    'security_api_key'          => ['label' => 'API Key', 'type' => 'password', 'default' => null],
                ],
            ],
            'backup' => [
                'label' => 'Backup',
                'icon' => 'archive',
                'settings' => [
                    'backup_enabled'     => ['label' => 'Backup Otomatis', 'type' => 'boolean', 'default' => true],
                    'backup_frequency'   => ['label' => 'Frekuensi', 'type' => 'select', 'default' => 'daily',
                        'options' => ['daily' => 'Harian', 'weekly' => 'Mingguan']],
                    'backup_provider'    => ['label' => 'Provider', 'type' => 'select', 'default' => 'local',
                        'options' => ['local' => 'Local', 's3' => 'S3', 'gdrive' => 'Google Drive']],
                ],
            ],
            'about' => [
                'label' => 'Tentang',
                'icon' => 'info',
                'settings' => [
                    'about_version' => ['label' => 'Versi ServiceKU', 'type' => 'readonly', 'default' => '1.0'],
                    'about_tenant_since' => ['label' => 'Pelanggan Sejak', 'type' => 'readonly', 'default' => null],
                ],
            ],
        ];
    }

    /**
     * Get default value for a specific setting key.
     */
    public static function getDefault(string $key): mixed
    {
        foreach (self::getAll() as $group) {
            foreach ($group['settings'] as $settingKey => $config) {
                if ($settingKey === $key) {
                    return $config['default'];
                }
            }
        }
        return null;
    }

    /**
     * Get flat list of all setting keys with their configs.
     */
    public static function getAllFlat(): array
    {
        $flat = [];
        foreach (self::getAll() as $groupKey => $group) {
            foreach ($group['settings'] as $settingKey => $config) {
                $flat[$settingKey] = $config + ['group' => $groupKey, 'group_label' => $group['label'], 'group_icon' => $group['icon']];
            }
        }
        return $flat;
    }
}
