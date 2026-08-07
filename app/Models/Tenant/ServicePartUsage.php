<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * ServicePartUsage — Audit log of stock consumption during service repair.
 * Created when ServiceRequiredPart::use() is called.
 *
 * Table: service_part_usages (created in 2026_08_02_000017_refine_service_part_flow)
 */
class ServicePartUsage extends Model
{
    protected $table = 'service_part_usages';

    public $timestamps = false; // Uses 'created_at' column with useCurrent()

    protected $fillable = [
        'service_id',
        'product_id',
        'service_required_part_id',
        'quantity',
        'cost_price',
        'selling_price',
        'discount',
        'subtotal',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
