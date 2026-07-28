<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\Purchase;

class PurchasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManagePurchases();
    }

    public function view(User $user, Purchase $purchase): bool
    {
        return $user->canManagePurchases();
    }

    public function create(User $user): bool
    {
        return $user->canManagePurchases();
    }

    public function void(User $user, Purchase $purchase): bool
    {
        return $user->canVoidTransaction();
    }
}
