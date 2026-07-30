<?php

namespace App\Services;

use App\Models\SystemSetting;

/**
 * Feature Flag Service
 * 
 * Super Admin dapat mengaktifkan/menonaktifkan fitur secara global
 * melalui SystemSetting. Ini adalah lapisan kontrol di atas plan-based features.
 */
class FeatureFlagService
{
    private static ?array $cache = null;

    /**
     * Daftar semua feature flags yang bisa di-toggle.
     */
    public static function getAllFlags(): array
    {
        return [
            'registration' => [
                'label' => 'Registrasi Tenant Baru',
                'description' => 'Izinkan pengguna baru mendaftar',
                'default' => true,
                'group' => 'registration',
                'key' => 'registration_open',
            ],
            'two_factor_auth' => [
                'label' => 'Autentikasi 2FA',
                'description' => 'Izinkan tenant mengaktifkan 2FA (Google Authenticator)',
                'default' => true,
                'group' => 'features',
                'key' => 'feature_two_factor_auth',
            ],
            'email_verification' => [
                'label' => 'Verifikasi Email',
                'description' => 'Wajibkan verifikasi email untuk user tenant',
                'default' => false,
                'group' => 'features',
                'key' => 'feature_email_verification',
            ],
            'custom_fields' => [
                'label' => 'Kolom Kustom Form',
                'description' => 'Izinkan owner membuat kolom kustom di form',
                'default' => true,
                'group' => 'features',
                'key' => 'feature_custom_fields',
            ],
            'maintenance_mode' => [
                'label' => 'Mode Pemeliharaan',
                'description' => 'Nonaktifkan akses ke semua tenant',
                'default' => false,
                'group' => 'maintenance',
                'key' => 'maintenance_mode',
            ],
        ];
    }

    /**
     * Cek apakah suatu fitur aktif secara global.
     */
    public static function isEnabled(string $flagKey): bool
    {
        $flags = self::all();
        return $flags[$flagKey] ?? true;
    }

    /**
     * Cek apakah suatu fitur aktif secara global DAN di plan tenant.
     */
    public static function isEnabledForTenant(string $flagKey, ?string $planFeature = null): bool
    {
        // Cek global flag
        if (!self::isEnabled($flagKey)) {
            return false;
        }

        // Cek plan feature (jika ada)
        if ($planFeature && tenancy()->initialized) {
            $tenant = tenancy()->tenant;
            $access = $tenant->getFeatureAccessLevel($planFeature);
            if ($access === 'none') {
                return false;
            }
        }

        return true;
    }

    /**
     * Ambil semua nilai feature flags.
     */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $flags = [];
        foreach (self::getAllFlags() as $key => $config) {
            $stored = SystemSetting::getValue($config['key']);
            $flags[$key] = $stored !== null ? ($stored === 'true') : $config['default'];
        }

        self::$cache = $flags;
        return $flags;
    }

    /**
     * Set nilai feature flag.
     */
    public static function set(string $flagKey, bool $value): void
    {
        $flags = self::getAllFlags();
        if (!isset($flags[$flagKey])) {
            throw new \InvalidArgumentException("Unknown feature flag: {$flagKey}");
        }

        $config = $flags[$flagKey];
        SystemSetting::setValue($config['key'], $value ? 'true' : 'false', $config['group']);
        self::$cache = null; // Reset cache
    }

    /**
     * Set multiple flags at once.
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            self::set($key, (bool) $value);
        }
    }

    /**
     * Reset cache.
     */
    public static function resetCache(): void
    {
        self::$cache = null;
    }
}
