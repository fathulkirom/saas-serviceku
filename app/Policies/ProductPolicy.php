<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\Product;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageProducts() || $user->isCs() || $user->isCashier() || $user->isTechnician() || $user->isHeadStore();
    }

    public function view(User $user, Product $product): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->canManageProducts();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->canManageProducts();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->canDeleteModel();
    }

    public function quickStock(User $user): bool
    {
        return $user->isOwner();
    }
}
