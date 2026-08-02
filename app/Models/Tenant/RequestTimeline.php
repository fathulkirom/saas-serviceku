<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Request Timeline — append-only rich event log.
 * Captures ALL significant events in a Request's lifecycle,
 * not just status transitions.
 */
class RequestTimeline extends Model
{
    protected $table = 'request_timeline';
    public $timestamps = false; // Only created_at

    protected $fillable = [
        'request_id', 'device_id', 'work_order_id',
        'event', 'label', 'description', 'metadata',
        'actor_id', 'branch_id',
    ];

    protected $casts = [
        'metadata' => 'json',
        'created_at' => 'datetime',
    ];

    public function request() { return $this->belongsTo(Request::class); }
    public function device() { return $this->belongsTo(Device::class, 'device_id'); }
    public function workOrder() { return $this->belongsTo(WorkOrder::class, 'work_order_id'); }
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
    public function branch() { return $this->belongsTo(Branch::class, 'branch_id'); }

    /**
     * Record a timeline event.
     */
    public static function record(int $requestId, string $event, string $label, ?string $desc = null, array $meta = [], ?int $deviceId = null, ?int $woId = null): self
    {
        return static::create([
            'request_id' => $requestId,
            'device_id' => $deviceId,
            'work_order_id' => $woId,
            'event' => $event,
            'label' => $label,
            'description' => $desc,
            'metadata' => $meta ? json_encode($meta) : null,
            'actor_id' => auth()->id(),
            'branch_id' => session('current_branch_id'),
        ]);
    }
}
