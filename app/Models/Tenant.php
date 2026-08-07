<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $connection = 'central';

    protected $fillable = [
        'tenant_name',
        'slug',
        'email',
        'phone',
        'subdomain',
        'plan_id',
        'subscription_status',
        'trial_ends_at',
        'subscribed_at',
        'subscription_ends_at',
        'is_active',
        'data',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscribed_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'is_active' => 'boolean',
        'data' => 'json',
    ];

    /**
     * Override VirtualColumn: kolom-kolom ini adalah real database columns,
     * BUKAN disimpan di JSON 'data'. Mencegah konflik data ganda.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'tenant_name',
            'slug',
            'email',
            'phone',
            'subdomain',
            'plan_id',
            'subscription_status',
            'trial_ends_at',
            'subscribed_at',
            'subscription_ends_at',
            'is_active',
            'data',
            'created_at',
            'updated_at',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * PLATFORM-SYNC-01 (STEP 21): slugs reserved for platform/central use.
     * Single source of truth — shared by registration (slug collision guard)
     * and tenant lookup (exclude reserved slugs from store search).
     */
    public static function reservedSlugs(): array
    {
        return ['admin', 'kirom', 'www', 'api', 'mail', 'ftp', 'dev', 'staging', 'test', 'demo', 'app', 'web', 'blog', 'shop', 'help', 'support'];
    }

    public function stats()
    {
        return $this->hasOne(TenantStat::class);
    }

    public function isTrial(): bool
    {
        return $this->subscription_status === 'trial';
    }

    public function trialEnded(): bool
    {
        return $this->trial_ends_at && now()->gt($this->trial_ends_at);
    }

    public function isSubscriptionActive(): bool
    {
        if ($this->subscription_status === 'active') {
            return true;
        }

        if ($this->isTrial() && !$this->trialEnded()) {
            return true;
        }

        return false;
    }

    /**
     * Dapatkan tipe bisnis tenant.
     */
    public function getBusinessType(): string
    {
        return $this->data['business_type'] ?? 'full_service';
    }

    /**
     * Set tipe bisnis tenant.
     */
    public function setBusinessType(string $type): void
    {
        $data = $this->data ?? [];
        $data['business_type'] = $type;
        $this->data = $data;
        $this->save();
    }

    /**
     * Apakah tenant menerima servis?
     */
    public function acceptsServices(): bool
    {
        return !in_array($this->getBusinessType(), ['retail_only']);
    }

    /**
     * Apakah tenant bisa mengerjakan servis sendiri (tidak dilempar)?
     */
    public function canWorkOnServices(): bool
    {
        return in_array($this->getBusinessType(), ['full_service', 'gadget_full']);
    }

    /**
     * Daftar tipe bisnis yang tersedia.
     */
    public static function getBusinessTypes(): array
    {
        return [
            'full_service' => '🔧 Servis & Jual Sparepart',
            'aksesoris_service' => '📱 Aksesoris + Terima Servis (Dilempar)',
            'aksespare_service' => '🛠️ Aksesoris & Sparepart + Ada Teknisi',
            'gadget_full' => '💻 HP/Laptop/MacBook Baru & Second + Servis',
            'retail_only' => '🏪 Jualan Saja (Tidak Terima Servis)',
        ];
    }

    /**
     * Label untuk tipe bisnis.
     */
    public function getBusinessTypeLabel(): string
    {
        $types = self::getBusinessTypes();
        return $types[$this->getBusinessType()] ?? $this->getBusinessType();
    }

    /**
     * Dapatkan daftar fitur yang didukung oleh setiap tipe bisnis.
     */
    public static function getBusinessTypeFeatures(): array
    {
        return [
            'full_service' => [
                'services', 'customers', 'products', 'sales', 'reports', 'settings',
                'monitoring', 'multi_branch', 'transfer_stock', 'users', 'expenses',
                'purchases', 'deposits', 'checklist', 'indents'
            ],
            'aksesoris_service' => [
                'services', 'customers', 'products', 'sales', 'reports', 'settings',
                'monitoring', 'multi_branch', 'transfer_stock', 'users', 'expenses',
                'purchases', 'deposits', 'checklist', 'indents'
            ],
            'aksespare_service' => [
                'services', 'customers', 'products', 'sales', 'reports', 'settings',
                'monitoring', 'multi_branch', 'transfer_stock', 'users', 'expenses',
                'purchases', 'deposits', 'checklist', 'indents'
            ],
            'gadget_full' => [
                'services', 'customers', 'products', 'sales', 'reports', 'settings',
                'monitoring', 'multi_branch', 'transfer_stock', 'users', 'expenses',
                'purchases', 'deposits', 'checklist', 'indents'
            ],
            'retail_only' => [
                'customers', 'products', 'sales', 'reports', 'settings',
                'monitoring', 'multi_branch', 'transfer_stock', 'users', 'expenses',
                'purchases', 'deposits', 'indents'
            ],
        ];
    }

    /**
     * Dapatkan level akses fitur menggunakan FeatureEngine (Sprint 7.1B).
     * Unified resolver: Module activation → Plan feature → Business type constraint.
     */
    public function getFeatureAccessLevel(string $feature): string
    {
        return app(\App\Services\FeatureEngine::class)->getAccessLevel($this, $feature);
    }

    /**
     * Dapatkan semua level akses fitur efektif.
     */
    public function getAllEffectiveFeatureAccess(): array
    {
        return app(\App\Services\FeatureEngine::class)->getAllFeatures($this);
    }
}
