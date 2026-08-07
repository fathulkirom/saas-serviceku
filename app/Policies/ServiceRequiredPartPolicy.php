<?php

namespace App\Policies;

use App\Models\Tenant\User;
use App\Models\Tenant\ServiceRequiredPart;
use App\Services\BranchAccessService;

/**
 * BR-FIX-01 — Authorization for the part lifecycle.
 *
 * Respects official ServiceKU roles (owner / admin / manager / cs / cashier /
 * technician). NO new official role is created.
 *
 *   Technician       → request a part, see request state, cancel own request
 *   Admin/Manager    → approve / reject (authorized warehouse function)
 *   CS / Kasir       → confirm billable usage (consume) and process returns
 *   Owner            → all of the above (global permission)
 */
class ServiceRequiredPartPolicy
{
    protected function inScope(User $user, ?ServiceRequiredPart $part): bool
    {
        if (!$part) {
            return true;
        }

        return BranchAccessService::canAccess($user, $part->service?->branch_id);
    }

    /** Technician requests a part for a service they work on. */
    public function request(User $user, ServiceRequiredPart $part): bool
    {
        if (!$this->inScope($user, $part)) {
            return false;
        }

        $service = $part->service;

        return $user->isOwner()
            || $user->isAdmin()
            || $user->isManager()
            || ($user->isTechnician() && $service && $service->technician_id === $user->id);
    }

    /** Admin/authorized warehouse approves → reserves. */
    public function approve(User $user, ServiceRequiredPart $part): bool
    {
        if (!$this->inScope($user, $part)) {
            return false;
        }

        return $user->isOwner() || $user->isAdmin() || $user->isManager();
    }

    /** Admin/authorized warehouse rejects. */
    public function reject(User $user, ServiceRequiredPart $part): bool
    {
        return $this->approve($user, $part);
    }

    /** Technician (own request) or admin/manager/owner may cancel. */
    public function cancel(User $user, ServiceRequiredPart $part): bool
    {
        if (!$this->inScope($user, $part)) {
            return false;
        }

        $service = $part->service;

        return $user->isOwner()
            || $user->isAdmin()
            || $user->isManager()
            || ($user->isTechnician() && ($part->requested_by === $user->id || ($service && $service->technician_id === $user->id)));
    }

    /** CS / Kasir or authorized billing actor confirms consumption. */
    public function consume(User $user, ServiceRequiredPart $part): bool
    {
        if (!$this->inScope($user, $part)) {
            return false;
        }

        return in_array($user->role, ['owner', 'admin', 'manager', 'cs', 'cashier'], true);
    }

    /** CS / Kasir or authorized actor processes a part return. */
    public function returnPart(User $user, ServiceRequiredPart $part): bool
    {
        if (!$this->inScope($user, $part)) {
            return false;
        }

        return in_array($user->role, ['owner', 'admin', 'manager', 'cs', 'cashier'], true);
    }
}
