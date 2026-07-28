<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantStat extends Model
{
    protected $connection = 'central';
    protected $fillable = [
        'tenant_id',
        'users_count',
        'services_count',
        'sales_count',
        'total_revenue',
        'products_count',
        'storage_used_mb',
        'last_active_at',
        'last_synced_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function syncStats(Tenant $tenant): void
    {
        try {
            tenancy()->initialize($tenant);

            $stats = [
                'users_count' => \App\Models\Tenant\User::count(),
                'services_count' => \App\Models\Tenant\Service::count(),
                'sales_count' => \App\Models\Tenant\Sale::count(),
                'total_revenue' => \App\Models\Tenant\Sale::sum('total'),
                'products_count' => \App\Models\Tenant\Product::count(),
                'last_active_at' => now(),
                'last_synced_at' => now(),
            ];

            tenancy()->end();

            static::updateOrCreate(
                ['tenant_id' => $tenant->id],
                $stats
            );
        } catch (\Exception $e) {
            report($e);
        }
    }
}
