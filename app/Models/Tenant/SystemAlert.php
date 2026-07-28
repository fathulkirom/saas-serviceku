<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class SystemAlert extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'severity',
        'context',
        'is_read',
        'resolved_at',
    ];

    protected $casts = [
        'context' => 'json',
        'is_read' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public static function createAlert(string $type, string $title, string $message = null, string $severity = 'info', array $context = []): self
    {
        return static::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'severity' => $severity,
            'context' => $context,
        ]);
    }

    public function markAsResolved(): void
    {
        $this->update(['resolved_at' => now()]);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }
}
