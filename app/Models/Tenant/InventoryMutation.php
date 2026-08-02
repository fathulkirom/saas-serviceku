<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class InventoryMutation extends Model
{
    protected $fillable = [
        'branch_id', 'location_id',
        'product_id',
        'type',
        'quantity', 'before_stock', 'after_stock', 'unit_cost',
        'reference_type', 'reference_id',
        'note', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'before_stock' => 'integer',
        'after_stock' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function location()
    {
        return $this->belongsTo(StockLocation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
