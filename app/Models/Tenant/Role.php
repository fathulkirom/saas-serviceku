<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['key', 'label', 'is_system', 'description'];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Permissions attached to this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Users with this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role');
    }

    /**
     * Check if this role has a specific permission key.
     */
    public function hasPermission(string $permissionKey): bool
    {
        return $this->permissions->contains('key', $permissionKey);
    }

    /**
     * Check if this is a system role (cannot be deleted).
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }

    /**
     * Scope: only non-system roles (custom roles).
     */
    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    /**
     * Scope: only system roles.
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }
}
