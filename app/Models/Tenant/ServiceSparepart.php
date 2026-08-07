<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceSparepart extends Model
{
    protected $fillable = [
        'service_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        // BR-FIX-04: upstream supplier/distributor warranty (distinct from store)
        'supplier_id',
        'supplier_warranty_days',
        'supplier_warranty_lifetime',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'supplier_warranty_lifetime' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
