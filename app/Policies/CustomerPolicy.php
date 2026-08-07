<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\Customer;
use App\Services\BranchAccessService;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageCustomers();
    }

    public function view(User $user, Customer $customer): bool
    {
        if (!BranchAccessService::canAccess($user, $customer->branch_id)) return false;

        if ($user->canManageCustomers()) {
            return true;
        }

        // BR-FIX-03 (BR-001): a granular delegation that grants CS-intake
        // (service.create) necessarily needs to read/select the customer being
        // served — the customer read follows the intake capability, scoped to
        // the same branch. It does NOT grant customer write/manage.
        return $user->canViaPermissionInBranch('service.create', $customer->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->canManageCustomers();
    }

    public function update(User $user, Customer $customer): bool
    {
        if (!BranchAccessService::canAccess($user, $customer->branch_id)) return false;
        return $user->canManageCustomers();
    }

    public function delete(User $user, Customer $customer): bool
    {
        if (!BranchAccessService::canAccess($user, $customer->branch_id)) return false;
        return $user->canDeleteModel();
    }
}
