<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\Service;
use App\Services\BranchAccessService;

class ServicePolicy
{
    /**
     * Permission-based authorization (Blueprint v1.0 §11).
     * Uses canViaPermission() with automatic fallback to legacy HasRoles trait.
     */
    public function viewAny(User $user): bool
    {
        return $user->canViaPermission('service.view') || $user->canWorkOnServices();
    }

    public function view(User $user, Service $service): bool
    {
        if (!BranchAccessService::canAccess($user, $service->branch_id)) return false;
        return $user->canViaPermission('service.view') || $user->canWorkOnServices();
    }

    public function create(User $user): bool
    {
        // BR-FIX-03 (BR-001): creating a CS intake requires the service.create
        // capability — owner/admin/manager/head_store/cs hold it by role, or a
        // user may hold an ACTIVE granular service.create delegation (granted
        // temporarily while keeping their original role). Technicians may NOT
        // create intake by default (the old canWorkOnServices() fallback is
        // removed so a restricted technician is not implicitly authorized).
        return $user->canViaPermission('service.create');
    }

    public function update(User $user, Service $service): bool
    {
        if (!BranchAccessService::canAccess($user, $service->branch_id)) return false;
        return $user->canViaPermission('service.update')
            || $user->isOwner() || $user->isAdmin() || $user->id === $service->technician_id;
    }

    public function delete(User $user, Service $service): bool
    {
        if (!BranchAccessService::canAccess($user, $service->branch_id)) return false;
        return $user->canViaPermission('service.delete')
            || $user->isOwner() || $user->isAdmin();
    }

    public function assign(User $user): bool
    {
        return $user->canViaPermission('service.assign')
            || $user->isOwner() || $user->isAdmin() || $user->isCs();
    }

    public function accept(User $user, Service $service): bool
    {
        if (!BranchAccessService::canAccess($user, $service->branch_id)) return false;
        return $user->canViaPermission('service.work')
            && ($user->isTechnician() || $user->isOwner())
            && $service->status === 'menunggu_alokasi';
    }

    public function start(User $user, Service $service): bool
    {
        if (!BranchAccessService::canAccess($user, $service->branch_id)) return false;
        return $user->canViaPermission('service.work')
            && ($user->isOwner() || $user->id === $service->technician_id)
            && $service->status === 'diterima';
    }

    public function finish(User $user, Service $service): bool
    {
        if (!BranchAccessService::canAccess($user, $service->branch_id)) return false;
        return $user->canViaPermission('service.finish')
            && ($user->isOwner() || $user->id === $service->technician_id)
            && $service->status === 'dikerjakan';
    }

    public function cancel(User $user, Service $service): bool
    {
        if (!BranchAccessService::canAccess($user, $service->branch_id)) return false;
        return $user->canViaPermission('service.void')
            || $user->isOwner() || $user->id === $service->technician_id;
    }

    public function confirm(User $user, Service $service): bool
    {
        if (!BranchAccessService::canAccess($user, $service->branch_id)) return false;
        return $user->canViaPermission('service.update')
            && ($user->isOwner() || $user->id === $service->technician_id)
            && $service->status === 'dikerjakan';
    }

    public function approve(User $user, Service $service): bool
    {
        if (!BranchAccessService::canAccess($user, $service->branch_id)) return false;
        return $user->isOwner();
    }

    public function partner(User $user, Service $service): bool
    {
        if (!BranchAccessService::canAccess($user, $service->branch_id)) return false;
        return $user->isOwner() || $user->canWorkOnServices();
    }

    public function reallocate(User $user, Service $service): bool
    {
        if (!BranchAccessService::canAccess($user, $service->branch_id)) return false;
        return ($user->isOwner() || $user->id === $service->technician_id) && in_array($service->status, ['diterima', 'dikerjakan']);
    }

    public function takeOver(User $user, Service $service): bool
    {
        if (!BranchAccessService::canAccess($user, $service->branch_id)) return false;
        return !$user->isTechnician();
    }
}
