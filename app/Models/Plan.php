<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $connection = 'central'; // Central DB
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'promo_price',
        'promo_start',
        'promo_end',
        'trial_days',
        'features',
        'business_types',
        'is_active',
    ];

    protected $casts = [
        'features' => 'json',
        'business_types' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'promo_price' => 'decimal:2',
        'promo_start' => 'date',
        'promo_end' => 'date',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Cek apakah fitur tersedia (full access).
     */
    public function hasFeature(string $feature, ?string $businessType = null): bool
    {
        return $this->featureAccessLevel($feature, $businessType) === 'full';
    }

    /**
     * Dapatkan level akses untuk suatu fitur.
     * Returns: 'full', 'read_only', atau 'none'
     */
    public function featureAccessLevel(string $feature, ?string $businessType = null): string
    {
        if (!$businessType && tenancy()->initialized) {
            $businessType = tenancy()->tenant->getBusinessType();
        }

        $features = $this->features ?? [];
        
        // 1. Coba baca format bersarang (per tipe bisnis)
        if ($businessType && isset($features[$businessType]) && is_array($features[$businessType])) {
            $value = $features[$businessType][$feature] ?? false;
        } else {
            // 2. Fallback ke format flat (lama/default)
            $value = $features[$feature] ?? false;
        }

        if ($value === true || $value === 'full' || $value === 1 || $value === '1') {
            return 'full';
        }

        if ($value === 'read_only') {
            return 'read_only';
        }

        return 'none';
    }

    /**
     * Dapatkan nilai maksimal untuk fitur numerik (max_users, max_branches).
     */
    public function maxValue(string $feature, ?string $businessType = null): int
    {
        if (!$businessType && tenancy()->initialized) {
            $businessType = tenancy()->tenant->getBusinessType();
        }

        $features = $this->features ?? [];

        // 1. Coba baca format bersarang
        if ($businessType && isset($features[$businessType]) && is_array($features[$businessType])) {
            if (isset($features[$businessType][$feature])) {
                return (int) $features[$businessType][$feature];
            }
        }
        
        // 2. Fallback ke root limit
        if (isset($features[$feature])) {
            return (int) $features[$feature];
        }

        return 0;
    }

    /**
     * Dapatkan daftar semua fitur dengan level aksesnya.
     */
    public function getAllFeatureAccess(?string $businessType = null): array
    {
        $allFeatures = [
            'services',
            'customers',
            'products',
            'sales',
            'reports',
            'settings',
            'monitoring',
            'multi_branch',
            'transfer_stock',
            'users',
            'expenses',
            'purchases',
            'deposits',
            'checklist',
            'indents',
            'cash_register',
            'master_data',
        ];

        $result = [];
        foreach ($allFeatures as $feature) {
            $result[$feature] = $this->featureAccessLevel($feature, $businessType);
        }
        return $result;
    }

    /**
     * Dapatkan label untuk level akses.
     */
    public static function accessLevelLabel(string $level): string
    {
        return match ($level) {
            'full' => 'Full Akses',
            'read_only' => 'Read Only',
            'none' => 'Tidak Ada',
            default => 'Tidak Ada',
        };
    }

    /**
     * Dapatkan daftar semua feature keys yang dikenal.
     */
    public static function getKnownFeatures(): array
    {
        return [
            'services' => 'Manajemen Servis',
            'customers' => 'Data Pelanggan',
            'products' => 'Manajemen Produk',
            'sales' => 'POS & Penjualan',
            'reports' => 'Laporan',
            'settings' => 'Pengaturan Toko',
            'monitoring' => 'Monitoring',
            'multi_branch' => 'Multi Cabang',
            'transfer_stock' => 'Transfer Stok',
            'users' => 'Manajemen User',
            'expenses' => 'Pengeluaran',
            'purchases' => 'Pembelian',
            'deposits' => 'Setor Harian',
            'checklist' => 'Template Ceklis',
            'indents' => 'Indent / Pre-order',
            'cash_register' => 'Manajemen Shift Kasir',
            'master_data' => 'Master Data',
        ];
    }

    /**
     * Dapatkan daftar semua menu yang tersedia di sistem.
     */
    public static function getAllAvailableMenus(): array
    {
        return [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'group' => 'Utama'],
            ['id' => 'services', 'label' => 'Servis', 'group' => 'Utama'],
            ['id' => 'customers', 'label' => 'Pelanggan', 'group' => 'Utama'],
            ['id' => 'keuangan', 'label' => 'Keuangan', 'group' => 'Transaksi'],
            ['id' => 'kas', 'label' => 'Kas', 'group' => 'Transaksi'],
            ['id' => 'inventaris', 'label' => 'Inventaris', 'group' => 'Manajemen'],
            ['id' => 'servis_tools', 'label' => 'Servis Tools', 'group' => 'Manajemen'],
            ['id' => 'laporan', 'label' => 'Laporan', 'group' => 'Manajemen'],
            ['id' => 'sistem', 'label' => 'Sistem', 'group' => 'Manajemen'],
            ['id' => 'dokumen', 'label' => 'Dokumen', 'group' => 'Manajemen'],
            ['id' => 'pengaturan', 'label' => 'Pengaturan', 'group' => 'Manajemen'],
            ['id' => 'monitoring', 'label' => 'Monitoring', 'group' => 'Manajemen'],
            ['id' => 'qr_scanner', 'label' => 'QR Scanner', 'group' => 'Manajemen'],
        ];
    }

    /**
     * Dapatkan role keys yang ada.
     */
    public static function getAvailableRoleKeys(): array
    {
        return ['owner', 'admin', 'manager', 'head_store', 'cs', 'technician', 'cashier', 'courier', 'custom'];
    }

    /**
     * Dapatkan default menu built-in untuk suatu role (jika admin belum atur).
     */
    public static function getBuiltInDefaultMenus(string $role): array
    {
        $allMenus = [
            'dashboard', 'services', 'customers',
            'keuangan', 'kas',
            'inventaris', 'servis_tools', 'laporan', 'sistem', 'dokumen', 'pengaturan',
            'monitoring', 'qr_scanner',
        ];

        $roleDefaults = [
            'owner'      => $allMenus,
            'admin'      => $allMenus,
            'manager'    => ['dashboard', 'services', 'customers', 'keuangan', 'kas', 'inventaris', 'laporan'],
            'head_store' => ['dashboard', 'services', 'customers', 'keuangan', 'kas', 'inventaris'],
            'cs'         => ['dashboard', 'services', 'customers', 'servis_tools', 'dokumen', 'qr_scanner'],
            'technician' => ['dashboard', 'services', 'servis_tools', 'qr_scanner'],
            'cashier'    => ['dashboard', 'services', 'customers', 'keuangan', 'kas', 'qr_scanner'],
            'courier'    => ['dashboard', 'qr_scanner'],
            'custom'     => ['dashboard'],
        ];

        return $roleDefaults[$role] ?? ['dashboard'];
    }

    /**
     * Dapatkan default menu untuk suatu role (dari plan jika ada, atau built-in).
     */
    public function getDefaultMenusForRole(string $role): array
    {
        $defaults = $this->features['default_menus'] ?? [];
        if (isset($defaults[$role]) && is_array($defaults[$role])) {
            return $defaults[$role];
        }
        return self::getBuiltInDefaultMenus($role);
    }

    /**
     * Simpan default menu untuk semua role (dipanggil admin).
     */
    public function setDefaultMenus(array $defaultMenus): void
    {
        $features = $this->features ?? [];
        $features['default_menus'] = $defaultMenus;
        $this->features = $features;
        $this->save();
    }

    /**
     * Cek apakah plan sedang dalam masa promo.
     */
    public function isPromoActive(): bool
    {
        if (!$this->promo_price || $this->promo_price <= 0) {
            return false;
        }

        $now = now()->startOfDay();

        if ($this->promo_start && $now->lt($this->promo_start)) {
            return false;
        }

        if ($this->promo_end && $now->gt($this->promo_end)) {
            return false;
        }

        return true;
    }

    /**
     * Dapatkan harga efektif (promo jika aktif, atau harga normal).
     */
    public function effectivePrice(): float
    {
        return $this->isPromoActive() ? (float) $this->promo_price : (float) $this->price;
    }

    /**
     * Dapatkan persentase diskon.
     */
    public function discountPercent(): int
    {
        if (!$this->isPromoActive() || $this->price <= 0) {
            return 0;
        }
        return (int) round((($this->price - $this->promo_price) / $this->price) * 100);
    }

    /**
     * Dapatkan daftar semua tipe bisnis yang tersedia.
     */
    public static function getAvailableBusinessTypes(): array
    {
        return [
            'full_service' => 'Servis & Sparepart',
            'aksesoris_service' => 'Aksesoris & Servis',
            'aksespare_service' => 'Pusat Servis & Sparepart',
            'gadget_full' => 'Gadget & Servis',
            'retail_only' => 'Retail Saja',
        ];
    }

    /**
     * Cek apakah plan mendukung tipe bisnis tertentu.
     */
    public function supportsBusinessType(string $type): bool
    {
        $types = $this->business_types ?? [];
        // Jika tidak ada batasan, semua tipe didukung
        if (empty($types)) {
            return true;
        }
        return in_array($type, $types);
    }
}