<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class StockAllocation extends Model
{
    protected $fillable = [
        'from_branch_id',
        'to_branch_id',
        'product_id',
        'quantity',
        'status',
        'allocated_by',
        'confirmed_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function allocator()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
