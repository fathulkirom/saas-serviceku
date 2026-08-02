<?php

namespace App\Policies;

use App\Models\Tenant\Request;
use App\Models\Tenant\User;

class RequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViaPermission('request.view') || $user->canWorkOnServices();
    }

    public function view(User $user, Request $request): bool
    {
        return $user->canViaPermission('request.view')
            || $user->canWorkOnServices();
    }

    public function create(User $user): bool
    {
        return $user->canViaPermission('request.create')
            || $user->canWorkOnServices();
    }

    public function update(User $user, Request $request): bool
    {
        return $user->canViaPermission('request.update')
            || ($user->canWorkOnServices() && !$request->isTerminal());
    }

    public function assign(User $user): bool
    {
        return $user->canViaPermission('request.assign')
            || $user->isOwner() || $user->isAdmin() || $user->isCs();
    }

    public function cancel(User $user, Request $request): bool
    {
        if ($request->isTerminal()) return false;
        return $user->canViaPermission('request.cancel')
            || $user->isOwner() || $user->isAdmin();
    }

    public function override(User $user): bool
    {
        return $user->canViaPermission('request.override')
            || $user->isOwner();
    }

    public function delete(User $user, Request $request): bool
    {
        return $user->canViaPermission('request.delete')
            || $user->isOwner() || $user->isAdmin();
    }
}
