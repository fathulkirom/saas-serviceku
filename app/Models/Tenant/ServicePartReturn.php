<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServicePartReturn extends Model
{
    protected $fillable = [
        'service_id',
        'product_id',
        'service_required_part_id',
        'service_part_usage_id',
        'quantity',
        'reason',
        'status',
        'requested_by',
        'processed_by',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function requiredPart()
    {
        return $this->belongsTo(ServiceRequiredPart::class, 'service_required_part_id');
    }

    public function usage()
    {
        return $this->belongsTo(ServicePartUsage::class, 'service_part_usage_id');
    }
}
