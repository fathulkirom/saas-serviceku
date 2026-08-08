<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * BR-020: Service Reopen.
 *
 * Service yang sudah selesai bisa direopen (administrative or rework).
 * - Reason wajib
 * - Approval oleh owner/admin/manager
 * - Type: administrative (koreksi data) vs rework (pekerjaan ulang)
 * - Service snapshot preserved (immutable history)
 * - Tidak menggandakan invoice/payment/stock/commission
 */
class ServiceReopen extends Model
{
    protected $fillable = [
        'service_id', 'requested_by', 'approved_by',
        'reason', 'type', 'status', 'rejection_reason',
        'service_snapshot', 'approved_at',
    ];

    protected $casts = [
        'service_snapshot' => 'json',
        'approved_at'      => 'datetime',
    ];

    public function service()    { return $this->belongsTo(Service::class); }
    public function requester()  { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver()   { return $this->belongsTo(User::class, 'approved_by'); }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRework(): bool   { return $this->type === 'rework'; }

    /** Approve and capture a snapshot of the service before changes. */
    public function approve(int $approverId): void
    {
        $this->update([
            'status'           => 'approved',
            'approved_by'      => $approverId,
            'approved_at'      => now(),
            'service_snapshot' => $this->service->only([
                'id', 'status', 'total_cost', 'service_charge',
                'technician_id', 'problem_description', 'resolution',
            ]),
        ]);

        // Set service back to active status so it can be edited.
        $this->service->update(['status' => 'dikerjakan']);
    }

    /** Reject with reason. */
    public function reject(int $approverId, string $rejectionReason): void
    {
        $this->update([
            'status'           => 'rejected',
            'approved_by'      => $approverId,
            'rejection_reason' => $rejectionReason,
            'approved_at'      => now(),
        ]);
    }

    public function typeLabel(): string
    {
        return $this->type === 'rework' ? 'Pekerjaan Ulang' : 'Administratif';
    }
}
