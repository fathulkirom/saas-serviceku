<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\Expense;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageFinance();
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->canManageFinance();
    }

    public function create(User $user): bool
    {
        return $user->canManageFinance();
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->canManageFinance();
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->canDeleteModel();
    }
}
