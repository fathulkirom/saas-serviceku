<?php

namespace App\Services;

use App\Models\Tenant\Role;
use App\Models\Tenant\User;

class RoleService
{
    /**
     * Get all roles.
     */
    public function getAllRoles()
    {
        return Role::all();
    }

    /**
     * Get roles assigned to a user.
     */
    public function getUserRoles(User $user)
    {
        return $user->roles;
    }

    /**
     * Get role by key.
     */
    public function findByKey(string $key): ?Role
    {
        return Role::where('key', $key)->first();
    }

    /**
     * Create a custom role.
     */
    public function createCustomRole(array $data): Role
    {
        $data['is_system'] = false;
        return Role::create($data);
    }

    /**
     * Delete a custom role. System roles cannot be deleted.
     */
    public function deleteRole(Role $role): void
    {
        if ($role->isSystem()) {
            throw new \RuntimeException("System role '{$role->key}' cannot be deleted.");
        }
        $role->delete();
    }

    /**
     * Get role keys for a user (for backward-compatible middleware).
     */
    public function getRoleKeysForUser(User $user): array
    {
        if ($user->roles()->exists()) {
            return $user->roles()->pluck('key')->toArray();
        }
        // Fallback: use legacy role column
        return [$user->role];
    }
}
