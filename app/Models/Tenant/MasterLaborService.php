<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class MasterLaborService extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'cost_price',
        'selling_price',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
