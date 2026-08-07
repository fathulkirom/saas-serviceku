<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * UPGRADE-02: Tenant Add-on.
 *
 * Each row is a single add-on purchase by a tenant — could be a module,
 * feature, or capacity extension. Combined with the base plan, these
 * determine the tenant's effective entitlement.
 */
class TenantAddon extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id', 'type', 'key', 'quantity',
        'status', 'started_at', 'expires_at',
        'billing_cycle', 'price', 'metadata',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'started_at'   => 'datetime',
        'expires_at'   => 'datetime',
        'price'        => 'decimal:2',
        'metadata'     => 'json',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        return true;
    }

    /**
     * Active add-ons of a given type for a tenant.
     */
    public static function activeFor(string $tenantId, string $type): array
    {
        return static::where('tenant_id', $tenantId)
            ->where('type', $type)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get()
            ->all();
    }
}
