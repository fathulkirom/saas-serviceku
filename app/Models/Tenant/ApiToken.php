<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'name', 'token',
        'scopes', 'last_used_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'scopes'       => 'json',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
        'is_active'    => 'boolean',
    ];

    protected $hidden = ['token'];

    public function user()   { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        return true;
    }

    public static function generateToken(): string
    {
        return hash('sha256', Str::random(40));
    }

    public function can(string $scope): bool
    {
        $scopes = $this->scopes ?? [];
        return in_array($scope, $scopes) || in_array('*', $scopes);
    }

    public function touchLastUsed(): void
    {
        $this->updateQuietly(['last_used_at' => now()]);
    }
}
