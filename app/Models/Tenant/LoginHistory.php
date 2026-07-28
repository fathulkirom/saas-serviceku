<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $table = 'login_history';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'status',
        'failure_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record($userId, string $status = 'success', string $reason = null): self
    {
        return static::create([
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => $status,
            'failure_reason' => $reason,
        ]);
    }
}
