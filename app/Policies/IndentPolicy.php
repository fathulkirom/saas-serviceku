<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\Indent;

class IndentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin() || $user->isManager() || $user->isCs() || $user->isTechnician();
    }

    public function view(User $user, Indent $indent): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin() || $user->isManager() || $user->isCs();
    }

    public function update(User $user, Indent $indent): bool
    {
        return $user->isOwner() || $user->isAdmin() || $user->isManager();
    }

    public function delete(User $user, Indent $indent): bool
    {
        return $user->isOwner();
    }
}
