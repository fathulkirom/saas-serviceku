<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class TechnicianBonusConfig extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'bonus_type', 'percentage',
        'fixed_amount', 'category_rates', 'base_salary',
        'exclude_warranty_rework', 'is_active',
    ];

    protected $casts = [
        'percentage'             => 'decimal:2',
        'fixed_amount'           => 'decimal:2',
        'base_salary'            => 'decimal:2',
        'category_rates'         => 'json',
        'exclude_warranty_rework'=> 'boolean',
        'is_active'              => 'boolean',
    ];

    public function user()   { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }

    /** Calculate bonus for a given service. */
    public function calculate(Service $service, ?string $category = null): float
    {
        if ($this->exclude_warranty_rework && $service->is_warranty_claim) {
            return 0;
        }

        $serviceCharge = (float) ($service->service_charge ?? $service->total_cost ?? 0);

        return match ($this->bonus_type) {
            'fixed'       => (float) $this->fixed_amount,
            'per_category'=> $category ? (float) ($this->category_rates[$category] ?? 0) : 0,
            'combined'    => ($serviceCharge * (float) $this->percentage / 100) + (float) $this->fixed_amount,
            default       => $serviceCharge * (float) $this->percentage / 100, // percentage
        };
    }
}

class TechnicianBonusRecord extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'service_id', 'amount',
        'bonus_type', 'category', 'status', 'approved_at',
        'approved_by', 'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'approved_at'  => 'datetime',
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function branch()     { return $this->belongsTo(Branch::class); }
    public function service()    { return $this->belongsTo(Service::class); }
    public function approver()   { return $this->belongsTo(User::class, 'approved_by'); }
}
