<?php

namespace App\Policies;

use App\Models\Tenant\User;
use App\Models\Tenant\WorkOrder;
use App\Services\BranchAccessService;

class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canWorkOnServices();
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        // Branch Check
        if ($workOrder->service && !BranchAccessService::canAccess($user, $workOrder->service->branch_id)) {
            return false;
        }

        // Technician Check
        if ($user->isTechnician()) {
            return $user->id === $workOrder->technician_id;
        }

        return $user->canWorkOnServices() || $user->isOwner() || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->canWorkOnServices() || $user->isOwner();
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        if ($workOrder->service && !BranchAccessService::canAccess($user, $workOrder->service->branch_id)) {
            return false;
        }

        if ($user->isTechnician()) {
            return $user->id === $workOrder->technician_id;
        }

        return $user->isOwner() || $user->isAdmin();
    }

    public function delete(User $user, WorkOrder $workOrder): bool
    {
        return $user->isOwner() || $user->isAdmin();
    }
}
