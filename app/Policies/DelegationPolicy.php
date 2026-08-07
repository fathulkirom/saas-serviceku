<?php

namespace App\Policies;

use App\Models\Tenant\Delegation;
use App\Models\Tenant\User;
use App\Services\BranchAccessService;

/**
 * BR-FIX-03 — Authorization for granting/revoking granular delegations.
 *
 * Only users holding the `delegation.grant` / `delegation.revoke`
 * capabilities (owner, admin, manager) may manage delegations. The grant
 * itself is further constrained in DelegationController::store (the granter
 * must hold the delegated capability and only within their branch reach).
 */
class DelegationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canViaPermission('delegation.grant')
            || $user->canViaPermission('delegation.revoke');
    }

    public function grant(User $user): bool
    {
        return $user->canViaPermission('delegation.grant');
    }

    public function revoke(User $user, Delegation $delegation): bool
    {
        if (!$user->canViaPermission('delegation.revoke')) {
            return false;
        }

        // Owner can revoke any delegation; others only within their reach.
        if ($user->isOwner()) {
            return true;
        }

        return BranchAccessService::canAccess($user, $delegation->branch_id);
    }
}
