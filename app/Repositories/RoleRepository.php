<?php

namespace App\Repositories;

use App\Models\Tenant\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    public function all(): Collection
    {
        return Role::all();
    }

    public function find(int $id): ?Role
    {
        return Role::find($id);
    }

    public function findByKey(string $key): ?Role
    {
        return Role::where('key', $key)->first();
    }

    public function getSystemRoles(): Collection
    {
        return Role::system()->get();
    }

    public function getCustomRoles(): Collection
    {
        return Role::custom()->get();
    }

    public function save(Role $role): void
    {
        $role->save();
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }
}
