<?php

namespace App\Actions\Permission;

use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;

class RevokePermissionAction
{
    public function execute(Role $role, Permission $permission): void
    {
        $role->permissions()->detach($permission->id);
    }
}
