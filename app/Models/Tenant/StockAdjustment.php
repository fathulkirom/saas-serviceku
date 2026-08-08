<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = [
        'product_id', 'branch_id', 'user_id',
        'previous_quantity', 'new_quantity', 'difference',
        'reason', 'notes',
    ];

    protected $casts = [
        'previous_quantity' => 'integer',
        'new_quantity'      => 'integer',
        'difference'        => 'integer',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function branch()  { return $this->belongsTo(Branch::class); }
    public function user()    { return $this->belongsTo(User::class); }
}
