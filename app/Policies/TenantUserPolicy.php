<?php
namespace App\Policies;
use App\Models\Tenant\User;

class TenantUserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, User $target): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(User $user, User $target): bool
    {
        return $user->canManageUsers();
    }

    public function delete(User $user, User $target): bool
    {
        if ($target->isOwner()) {
            return false;
        }
        return $user->canDeleteUser();
    }
}
