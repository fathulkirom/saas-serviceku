<?php

namespace App\Actions\Permission;

use App\Models\Tenant\Role;
use App\Models\Tenant\User;

class RemoveRoleAction
{
    public function execute(User $user, Role $role): void
    {
        // Guard: don't remove the last owner
        if ($role->key === 'owner') {
            $ownerCount = $role->users()->count();
            if ($ownerCount <= 1 && $user->roles()->where('key', 'owner')->exists()) {
                throw new \RuntimeException('Cannot remove the last owner.');
            }
        }

        $user->roles()->detach($role->id);
        $user->clearPermissionCache();
    }
}
