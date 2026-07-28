<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Indent extends Model
{
    protected $fillable = [
        'branch_id',
        'customer_id',
        'service_id',
        'product_name',
        'qty',
        'description',
        'cost_estimate',
        'deposit',
        'status',
    ];

    protected $casts = [
        'cost_estimate' => 'decimal:2',
        'deposit' => 'decimal:2',
        'qty' => 'integer',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_DIPROSES = 'diproses';
    const STATUS_SELESAI = 'selesai';
    const STATUS_BATAL = 'batal';

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function getRemainingAmount(): float
    {
        return max(0, $this->cost_estimate - $this->deposit);
    }
}
