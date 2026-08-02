<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Work Order — sub-work within a Request/Service.
 * Supports multi-technician per Request (BR-018: Progressive Work Order).
 */
class WorkOrder extends Model
{
    use SoftDeletes;

    // Sprint 7.5F — Standardized status constants
    public const STATUS_ASSIGNED    = 'assigned';
    public const STATUS_ACCEPTED    = 'accepted';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE        = 'done';
    public const STATUS_PAUSED      = 'paused';

    /** Valid status transitions (Sprint 7.5G validation) */
    public const ALLOWED_TRANSITIONS = [
        self::STATUS_ASSIGNED    => [self::STATUS_ACCEPTED],
        self::STATUS_ACCEPTED    => [self::STATUS_IN_PROGRESS],
        self::STATUS_IN_PROGRESS => [self::STATUS_DONE, self::STATUS_PAUSED],
        self::STATUS_PAUSED      => [self::STATUS_IN_PROGRESS],
        self::STATUS_DONE        => [],
    ];

    protected $fillable = [
        'request_id', 'service_id', 'device_id',
        'technician_id', 'title', 'description', 'category',
        'status', 'priority', 'parts_used', 'estimated_minutes', 'actual_minutes',
        'technician_note', 'qc_note', 'qc_by',
        'assigned_at', 'accepted_at', 'started_at', 'completed_at', 'sort_order',
        'work_status', 'paused_at', 'total_paused_minutes',
    ];

    protected $casts = [
        'parts_used' => 'json',
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'paused_at' => 'datetime',
        'total_paused_minutes' => 'integer',
    ];

    /** Check if transition from current status to target is allowed */
    public function canTransitionTo(string $target): bool
    {
        $allowed = self::ALLOWED_TRANSITIONS[$this->status] ?? [];
        return in_array($target, $allowed, true);
    }

    // ======== Relationships ========
    public function request(): BelongsTo { return $this->belongsTo(Request::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function device(): BelongsTo { return $this->belongsTo(Device::class); }
    public function technician(): BelongsTo { return $this->belongsTo(User::class, 'technician_id'); }
    public function qcBy(): BelongsTo { return $this->belongsTo(User::class, 'qc_by'); }
    public function timelineEvents() { return $this->hasMany(RequestTimeline::class); }

    // ======== Sprint 7.3F — Technician Workflow ========
    public function assign(int $techId): void
    {
        $this->update(['technician_id' => $techId, 'status' => self::STATUS_ASSIGNED, 'assigned_at' => now()]);
        event(new \App\Events\Entity\WorkOrderAssigned($this));
    }

    public function accept(): void
    {
        $this->update(['status' => self::STATUS_ACCEPTED, 'accepted_at' => now()]);
        event(new \App\Events\Entity\TechnicianAccepted($this));
    }

    public function markInProgress(): void { $this->update(['status' => self::STATUS_IN_PROGRESS, 'started_at' => now()]); }
    public function markDone(): void { $this->update(['status' => self::STATUS_DONE, 'completed_at' => now()]); }
    public function isCompleted(): bool { return $this->status === self::STATUS_DONE; }

    public function scopeForTechnician($q, int $techId) { return $q->where('technician_id', $techId); }
    // Sprint 7.4B — Daily Operations
    public function addWorklog(string $description): void
    {
        $this->worklogs()->create(['service_id' => $this->service_id, 'description' => $description, 'created_by' => auth()->id()]);
        event(new \App\Events\Entity\WorklogCreated($this, $description));
    }

    public function pause(): void
    {
        if (!$this->canTransitionTo(self::STATUS_PAUSED)) {
            throw new \RuntimeException("Cannot pause WorkOrder with status '{$this->status}'.");
        }
        $this->update(['status' => self::STATUS_PAUSED, 'paused_at' => now()]);
        event(new \App\Events\Entity\RepairPaused($this));
    }

    public function resume(): void
    {
        if (!$this->canTransitionTo(self::STATUS_IN_PROGRESS)) {
            throw new \RuntimeException("Cannot resume WorkOrder with status '{$this->status}'.");
        }
        $pausedMinutes = $this->paused_at ? (int) $this->paused_at->diffInMinutes(now()) : 0;
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'paused_at' => null,
            'total_paused_minutes' => $this->total_paused_minutes + $pausedMinutes,
        ]);
        event(new \App\Events\Entity\RepairResumed($this));
    }

    public function finish(): void
    {
        $actualMinutes = $this->started_at ? (int) $this->started_at->diffInMinutes(now()) : 0;
        $this->update([
            'work_status' => self::STATUS_DONE,
            'actual_minutes' => $actualMinutes - $this->total_paused_minutes,
            'completed_at' => now(),
        ]);
        event(new \App\Events\Entity\RepairFinished($this));
    }

    public function worklogs() { return $this->hasMany(Worklog::class)->latest(); }

    public function scopeByWorkStatus($q, string $status) { return $q->where('work_status', $status); }
}
