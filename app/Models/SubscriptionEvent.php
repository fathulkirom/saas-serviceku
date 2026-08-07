<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * UPGRADE-02: Subscription Event — immutable audit log.
 *
 * Every subscription state change is recorded: who, what, from, to, when, why.
 */
class SubscriptionEvent extends Model
{
    protected $connection = 'central';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'event', 'actor_type', 'actor_id',
        'old_value', 'new_value', 'reason', 'context',
        'created_at',
    ];

    protected $casts = [
        'context'    => 'json',
        'created_at' => 'datetime',
    ];

    public static function log(
        string $tenantId,
        string $event,
        ?string $actorId = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        ?string $reason = null,
        array $context = []
    ): self {
        return static::create([
            'tenant_id'  => $tenantId,
            'event'      => $event,
            'actor_type' => $actorId ? 'central' : 'system',
            'actor_id'   => $actorId,
            'old_value'  => $oldValue,
            'new_value'  => $newValue,
            'reason'     => $reason,
            'context'    => $context,
            'created_at' => now(),
        ]);
    }
}
