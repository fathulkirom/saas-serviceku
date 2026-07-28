<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $connection = 'central';
    protected $fillable = [
        'level',
        'type',
        'tenant_id',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'json',
    ];

    public static function record(string $level, string $type, string $message, array $context = [], ?string $tenantId = null): self
    {
        return static::create([
            'level' => $level,
            'type' => $type,
            'tenant_id' => $tenantId,
            'message' => $message,
            'context' => $context,
        ]);
    }

    public static function info(string $message, array $context = []): self
    {
        return static::record('info', 'system', $message, $context);
    }

    public static function error(string $message, array $context = []): self
    {
        return static::record('error', 'system', $message, $context);
    }

    public static function warning(string $message, array $context = []): self
    {
        return static::record('warning', 'system', $message, $context);
    }
}
