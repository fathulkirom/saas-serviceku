<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Universal Activity Log — polymorphic, append-only.
 * Foundation for ActivityEngine (full impl in later sprint).
 *
 * Schema (Sprint 1.0 — actual migration):
 *   user_id, action, subject_type, subject_id, description, properties, ip_address, user_agent, created_at
 */
class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id',
        'description', 'properties', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'properties' => 'json',
        'created_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function entity()
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }

    /**
     * Backward-compatible log method.
     */
    public static function log(string $action, ?string $description = null, $subject = null, array $properties = []): self
    {
        return static::create([
            'action'       => $action,
            'description'  => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject ? $subject->getKey() : null,
            'properties'   => $properties ? json_encode($properties) : null,
            'user_id'      => auth()->id(),
            'ip_address'   => request()->ip(),
        ]);
    }

    /**
     * Alias for backward compatibility — some code references 'event' not 'action'.
     */
    public function getEventAttribute(): ?string
    {
        return $this->action;
    }

    /**
     * Alias for backward compatibility — some code references 'entity_type'.
     */
    public function getEntityTypeAttribute(): ?string
    {
        return $this->subject_type;
    }

    /**
     * Alias for backward compatibility — some code references 'entity_id'.
     */
    public function getEntityIdAttribute(): ?string
    {
        return $this->subject_id;
    }

    /**
     * Alias for backward compatibility — some code references 'actor_id'.
     */
    public function getActorIdAttribute(): ?int
    {
        return $this->user_id;
    }

    /**
     * Alias for backward compatibility — some code references 'metadata'.
     */
    public function getMetadataAttribute()
    {
        return $this->properties;
    }
}
