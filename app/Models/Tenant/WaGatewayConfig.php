<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class WaGatewayConfig extends Model
{
    protected $fillable = [
        'tenant_id',
        'provider',
        'api_key',
        'is_active',
        'template_service_received',
        'template_service_finished',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
