<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class InventoryMutation extends Model
{
    protected $fillable = [
        'branch_id',
        'product_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
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
