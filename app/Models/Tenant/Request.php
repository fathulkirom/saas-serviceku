<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Request — Core Entry Point (ADR-001, Blueprint v1.0).
 * All operational interactions MUST begin as a Request before forking to
 * ServiceOrder, SalesOrder, WarrantyClaim, Booking, etc.
 */
class Request extends Model
{
    use SoftDeletes;

    protected $table = 'requests';

    protected $fillable = [
        'request_number', 'customer_id', 'customer_contact_name', 'customer_contact_phone', 'customer_contact_type',
        'branch_id', 'pickup_branch_id',
        'type', 'source', 'channel',
        'status', 'scheduled_at', 'arrived_at', 'completed_at', 'delivered_at',
        'pickup_address', 'customer_note', 'internal_note', 'priority',
        'created_by', 'assigned_to',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'arrived_at' => 'datetime',
        'completed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // ======== Relationships ========

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function pickupBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'pickup_branch_id');
    }

    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'request_devices')
            ->withPivot('issue_description', 'condition', 'notes')
            ->withTimestamps();
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(Service::class, 'request_id');
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(Sale::class, 'request_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(RequestHistory::class, 'request_id')->orderBy('created_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function pickupDelivery(): HasMany
    {
        return $this->hasMany(PickupDelivery::class, 'request_id');
    }

    // Sprint 7.2B — Work Orders + Timeline
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'request_id')->orderBy('sort_order');
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(RequestTimeline::class, 'request_id')->orderBy('created_at');
    }

    // ======== Business Methods ========

    public function isTerminal(): bool
    {
        return in_array($this->status, ['closed', 'cancelled', 'rejected', 'expired']);
    }

    public function canTransitionTo(string $status): bool
    {
        return !$this->isTerminal();
    }

    public function hasDevices(): bool
    {
        return $this->devices()->count() > 0;
    }

    /** Check if all work orders are completed (ready to close). */
    public function allWorkOrdersDone(): bool
    {
        return $this->workOrders()->count() > 0
            && $this->workOrders()->whereNotIn('status', ['done', 'cancelled'])->count() === 0;
    }

    /** Evolve request type (inspection → service → warranty) — BR-019. */
    public function evolveType(string $newType): void
    {
        $oldType = $this->type;
        $this->type = $newType;
        $this->save();

        RequestTimeline::record(
            $this->id,
            'request_evolved',
            "Request berevolusi dari {$oldType} → {$newType}",
            deviceId: null,
        );
    }
}
