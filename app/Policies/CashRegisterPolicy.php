<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\CashRegister;

class CashRegisterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageCashRegister();
    }

    public function view(User $user, CashRegister $cashRegister): bool
    {
        return $user->canManageCashRegister();
    }

    public function open(User $user): bool
    {
        return $user->isCashier() || $user->isOwner();
    }

    public function close(User $user, CashRegister $cashRegister): bool
    {
        return $user->isCashier() || $user->isOwner();
    }
}
