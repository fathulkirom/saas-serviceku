<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Canonical Event Log — the SINGLE source of truth for all events.
 * Replaces: workflow_history, request_history, request_timeline,
 *           automation_logs, activity_logs (as projections).
 *
 * Every Timeline, History, Audit, Analytics, Dashboard, and Activity
 * must derive from this table via projections.
 */
class EventLog extends Model
{
    protected $table = 'event_logs';

    public $timestamps = false;

    protected $fillable = [
        'entity_type', 'entity_id', 'event_key', 'event_class',
        'actor_id', 'branch_id', 'tenant_id',
        'correlation_id', 'causation_id', 'module', 'source', 'severity', 'version',
        'metadata', 'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'json',
        'occurred_at' => 'datetime',
        'version' => 'int',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // ======== SCOPES — Projection Views ========

    /** Timeline view: specific entity events ordered chronologically */
    public function scopeTimeline($query, string $entityType, int $entityId)
    {
        return $query->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('occurred_at');
    }

    /** Activity view: events by a specific actor */
    public function scopeActivityByActor($query, int $actorId)
    {
        return $query->where('actor_id', $actorId)->orderBy('occurred_at', 'desc');
    }

    /** Audit view: all status change events across entities */
    public function scopeAuditTrail($query, ?string $entityType = null)
    {
        $q = $query->whereIn('event_key', ['WorkflowStateChanged', 'RequestCreated', 'RequestCompleted',
            'RequestCancelled', 'ServiceCreated', 'ServiceCompleted', 'ServiceCancelled',
            'WorkOrderCreated', 'WorkOrderCompleted', 'PaymentReceived']);
        if ($entityType) {
            $q->where('entity_type', $entityType);
        }

        return $q->orderBy('occurred_at', 'desc');
    }

    /** Analytics view: aggregated events for dashboards */
    public function scopeAnalytics($query, string $eventKey, $from, $to)
    {
        return $query->where('event_key', $eventKey)
            ->whereBetween('occurred_at', [$from, $to]);
    }
}
