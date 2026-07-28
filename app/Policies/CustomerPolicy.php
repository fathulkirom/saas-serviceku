<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\Customer;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageCustomers();
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->canManageCustomers();
    }

    public function create(User $user): bool
    {
        return $user->canManageCustomers();
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->canManageCustomers();
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->canDeleteModel();
    }
}
