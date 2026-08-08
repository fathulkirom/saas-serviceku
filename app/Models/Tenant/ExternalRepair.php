<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ExternalPartner extends Model
{
    protected $fillable = ['branch_id', 'name', 'phone', 'specialty', 'address', 'notes', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function branch() { return $this->belongsTo(Branch::class); }
    public function repairs() { return $this->hasMany(ExternalRepair::class, 'partner_id'); }
}

class ExternalRepair extends Model
{
    protected $fillable = [
        'service_id', 'partner_id', 'branch_id', 'user_id',
        'status', 'partner_cost', 'customer_charge', 'store_margin',
        'problem_description', 'resolution',
        'sent_at', 'estimated_return', 'returned_at', 'tracking_notes',
    ];

    protected $casts = [
        'partner_cost'    => 'decimal:2',
        'customer_charge' => 'decimal:2',
        'store_margin'    => 'decimal:2',
        'sent_at'         => 'datetime',
        'estimated_return'=> 'datetime',
        'returned_at'     => 'datetime',
    ];

    public function service() { return $this->belongsTo(Service::class); }
    public function partner() { return $this->belongsTo(ExternalPartner::class); }
    public function branch()  { return $this->belongsTo(Branch::class); }
    public function user()    { return $this->belongsTo(User::class); }

    /** Auto-calculate margin: customer charge - partner cost. */
    public function calculateMargin(): void
    {
        $this->store_margin = $this->customer_charge - $this->partner_cost;
    }

    public function isOverdue(): bool
    {
        return $this->estimated_return && $this->estimated_return->isPast() && !$this->returned_at;
    }
}
