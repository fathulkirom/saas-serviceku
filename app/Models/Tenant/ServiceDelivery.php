<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceDelivery extends Model
{
    protected $fillable = [
        'service_id', 'pickup_branch_id', 'ready_at', 'picked_up_at',
        'received_by', 'receiver_phone', 'receiver_relation',
        'identity_type', 'identity_number', 'identity_photo',
        'signature_image', 'handover_photo',
        'payment_verified', 'payment_verified_by', 'handled_by',
        'notes',
    ];

    protected $casts = [
        'ready_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'payment_verified' => 'boolean',
    ];

    public function service() { return $this->belongsTo(Service::class); }
    public function handler() { return $this->belongsTo(User::class, 'handled_by'); }
    public function paymentVerifier() { return $this->belongsTo(User::class, 'payment_verified_by'); }

    /** BR-FIX-02: branch where the unit is handed over (custody/pickup branch). */
    public function pickupBranch() { return $this->belongsTo(Branch::class, 'pickup_branch_id'); }

    public function complete(string $receivedBy, string $phone, array $extra = []): void
    {
        $this->update([
            'received_by' => $receivedBy,
            'receiver_phone' => $phone,
            'picked_up_at' => now(),
            'signature_image' => $extra['signature'] ?? null,
            'handover_photo' => $extra['handover_photo'] ?? null,
            'identity_type' => $extra['identity_type'] ?? null,
            'identity_number' => $extra['identity_number'] ?? null,
            'receiver_relation' => $extra['relation'] ?? 'self',
        ]);
    }

    public function verifyPayment(int $userId): void
    {
        $this->update(['payment_verified' => true, 'payment_verified_by' => $userId]);
    }
}
