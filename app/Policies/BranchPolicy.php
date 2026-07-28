<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\Branch;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, Branch $branch): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->canManageBranch();
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->canManageBranch();
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->canManageBranch();
    }
}
