<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceWarrantyClaim extends Model
{
    protected $fillable = [
        'service_warranty_id', 'customer_id', 'service_id',
        'claim_number', 'problem_description', 'status',
        'checked_by', 'approved_by', 'approval_note', 'completed_at',
    ];

    protected $casts = ['completed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function ($claim) {
            if (empty($claim->claim_number)) {
                $claim->claim_number = 'WCL' . date('ymd') . strtoupper(substr(uniqid(), -4));
            }
        });
    }

    public function warranty() { return $this->belongsTo(ServiceWarranty::class, 'service_warranty_id'); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function service()  { return $this->belongsTo(Service::class); }
    public function checker()  { return $this->belongsTo(User::class, 'checked_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    public function approve(int $userId, ?string $note = null): void
    {
        $this->update(['status' => 'approved', 'approved_by' => $userId, 'approval_note' => $note]);
        event(new \App\Events\Entity\WarrantyClaimApproved($this));
    }

    public function reject(int $userId, string $reason): void
    {
        $this->update(['status' => 'rejected', 'approved_by' => $userId, 'approval_note' => $reason]);
        event(new \App\Events\Entity\WarrantyClaimRejected($this));
    }

    public function complete(): void
    {
        $this->update(['status' => 'completed', 'completed_at' => now()]);
    }
}
