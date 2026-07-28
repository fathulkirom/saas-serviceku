<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\DailyDeposit;

class DailyDepositPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageCashRegister();
    }

    public function view(User $user, DailyDeposit $dailyDeposit): bool
    {
        return $user->canManageCashRegister();
    }

    public function create(User $user): bool
    {
        return $user->isCashier() || $user->isOwner();
    }

    public function confirm(User $user, DailyDeposit $dailyDeposit): bool
    {
        return $user->canConfirmDeposit();
    }
}
