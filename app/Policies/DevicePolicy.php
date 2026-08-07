<?php

namespace App\Policies;

use App\Models\Tenant\User;
use App\Models\Tenant\Device;
use App\Services\BranchAccessService;

class DevicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageCustomers(); // Devices usually tied to customer management
    }

    public function view(User $user, Device $device): bool
    {
        // Check through customer
        if ($device->customer && !BranchAccessService::canAccess($user, $device->customer->branch_id)) {
            return false;
        }
        return $user->canManageCustomers() || $user->canWorkOnServices();
    }

    public function create(User $user): bool
    {
        return $user->canManageCustomers() || $user->canWorkOnServices();
    }

    public function update(User $user, Device $device): bool
    {
        if ($device->customer && !BranchAccessService::canAccess($user, $device->customer->branch_id)) {
            return false;
        }
        return $user->canManageCustomers() || $user->canWorkOnServices();
    }

    public function delete(User $user, Device $device): bool
    {
        if ($device->customer && !BranchAccessService::canAccess($user, $device->customer->branch_id)) {
            return false;
        }
        return $user->canDeleteModel();
    }
}
