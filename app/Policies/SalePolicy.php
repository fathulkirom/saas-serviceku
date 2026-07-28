<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\Sale;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageSales();
    }

    public function view(User $user, Sale $sale): bool
    {
        return $user->canManageSales();
    }

    public function create(User $user): bool
    {
        return $user->canManageSales();
    }

    public function void(User $user, Sale $sale): bool
    {
        return $user->canVoidTransaction();
    }

    public function update(User $user, Sale $sale): bool
    {
        return $user->canVoidTransaction();
    }
}
