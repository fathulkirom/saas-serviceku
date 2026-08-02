<?php

namespace App\Repositories;

use App\Models\Tenant\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository
{
    public function all(): Collection
    {
        return Permission::all();
    }

    public function findByKey(string $key): ?Permission
    {
        return Permission::where('key', $key)->first();
    }

    public function getForModule(string $module): Collection
    {
        return Permission::forModule($module)->get();
    }

    public function getKeysByModule(): array
    {
        return Permission::all()
            ->groupBy('module')
            ->map(fn($perms) => $perms->pluck('key')->toArray())
            ->toArray();
    }
}
