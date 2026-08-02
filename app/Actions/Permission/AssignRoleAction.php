<?php

namespace App\Actions\Permission;

use App\Models\Tenant\Role;
use App\Models\Tenant\User;

class AssignRoleAction
{
    public function execute(User $user, Role $role): void
    {
        $user->roles()->syncWithoutDetaching([$role->id]);
        $user->clearPermissionCache();
    }
}
