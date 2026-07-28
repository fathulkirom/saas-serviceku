<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\Supplier;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManagePurchases();
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->canManagePurchases();
    }

    public function create(User $user): bool
    {
        return $user->canManagePurchases();
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->canManagePurchases();
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->canDeleteModel();
    }
}
