<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\InventoryMutation;

class InventoryMutationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageProducts();
    }

    public function view(User $user, InventoryMutation $inventoryMutation): bool
    {
        return $user->canManageProducts();
    }

    public function create(User $user): bool
    {
        return $user->canManageProducts();
    }

    public function approve(User $user, InventoryMutation $inventoryMutation): bool
    {
        return $user->isOwner();
    }
}
