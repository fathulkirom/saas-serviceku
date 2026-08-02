<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Universal Activity Log — polymorphic, append-only.
 * Foundation for ActivityEngine (full impl in later sprint).
 *
 * Schema (Sprint 7.2C):
 *   entity_type, entity_id, event, description, metadata, actor_id, branch_id, created_at
 *
 * Backward compatible: log() signature preserved.
 */
class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    public $timestamps = false;

    protected $fillable = [
        'entity_type', 'entity_id', 'event', 'description', 'metadata',
        'actor_id', 'branch_id',
    ];

    protected $casts = [
        'metadata' => 'json',
        'created_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function entity()
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    /**
     * Backward-compatible log method.
     * Old signature: log(string $action, string $description=null, $subject=null, array $properties=[])
     * New columns: event=$action, description=$description, entity=$subject, metadata=$properties
     */
    public static function log(string $action, string $description = null, $subject = null, array $properties = []): self
    {
        return static::create([
            'event'       => $action,
            'description' => $description,
            'entity_type' => $subject ? get_class($subject) : null,
            'entity_id'   => $subject ? $subject->getKey() : null,
            'metadata'    => $properties ? json_encode($properties) : null,
            'actor_id'    => auth()->id(),
            'branch_id'   => session('current_branch_id'),
        ]);
    }
}
