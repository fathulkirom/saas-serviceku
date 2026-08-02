<?php

namespace App\Services;

use App\Models\Tenant\Permission;
use App\Models\Tenant\User;

class PermissionService
{
    /**
     * Get all permission keys for a user via the new permission engine.
     * Falls back to legacy HasRoles if migration hasn't been run.
     */
    public function getPermissionsForUser(User $user): array
    {
        return $user->getPermissionKeys();
    }

    /**
     * Check if a user has a specific permission.
     */
    public function userCan(User $user, string $permissionKey): bool
    {
        return $user->canViaPermission($permissionKey);
    }

    /**
     * Get all registered permission keys (registry).
     */
    public function getAllPermissions(): array
    {
        return Permission::pluck('key')->toArray();
    }

    /**
     * Get permissions grouped by module.
     */
    public function getPermissionsByModule(): array
    {
        return Permission::all()
            ->groupBy('module')
            ->map(fn($perms) => $perms->pluck('key')->toArray())
            ->toArray();
    }

    /**
     * Invalidate permission cache for a user.
     */
    public function clearUserCache(User $user): void
    {
        $user->clearPermissionCache();
    }
}
