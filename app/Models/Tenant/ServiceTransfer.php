<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * BR-FIX-02 (BR-004) — Cross-branch custody transfer.
 *
 * A service's ORIGIN branch (service.branch_id / from_branch_id) is preserved.
 * Custody moves along the status workflow requested → sent → received. Pickup
 * may then occur at the custody (received) branch without rewriting origin.
 */
class ServiceTransfer extends Model
{
    public const STATUS_REQUESTED = 'requested';
    public const STATUS_SENT = 'sent';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'service_id',
        'from_branch_id',
        'to_branch_id',
        'note',
        'transferred_by',
        'status',
        'requested_by',
        'processed_by',
        'sent_at',
        'received_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function transferor()
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /** Send the transfer (requested → sent). */
    public function send(int $byUserId): void
    {
        if ($this->status !== self::STATUS_REQUESTED) {
            throw new \RuntimeException('Transfer tidak dalam status request.');
        }
        $this->update([
            'status' => self::STATUS_SENT,
            'processed_by' => $byUserId,
            'sent_at' => now(),
        ]);
        $this->refresh();
    }

    /** Receive the transfer (sent → received). Idempotent. */
    public function receive(int $byUserId): void
    {
        if ($this->status === self::STATUS_RECEIVED) {
            return; // idempotent — no duplicate side effects
        }
        if ($this->status !== self::STATUS_SENT) {
            throw new \RuntimeException('Transfer harus dikirim sebelum diterima.');
        }
        $this->update([
            'status' => self::STATUS_RECEIVED,
            'processed_by' => $byUserId,
            'received_at' => now(),
        ]);
        $this->refresh();
    }

    /** Cancel an open transfer (requested/sent → cancelled). */
    public function cancel(int $byUserId, ?string $reason = null): void
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return;
        }
        if ($this->status === self::STATUS_RECEIVED) {
            throw new \RuntimeException('Transfer sudah diterima — tidak dapat dibatalkan.');
        }
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'processed_by' => $byUserId,
            'note' => trim(($this->note ?? '') . ($reason ? ' | ' . $reason : '')),
        ]);
        $this->refresh();
    }
}
