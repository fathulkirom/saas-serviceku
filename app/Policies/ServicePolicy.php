<?php
namespace App\Policies;
use App\Models\Tenant\User;
use App\Models\Tenant\Service;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canWorkOnServices();
    }

    public function view(User $user, Service $service): bool
    {
        return $user->canWorkOnServices();
    }

    public function create(User $user): bool
    {
        return $user->canWorkOnServices();
    }

    public function update(User $user, Service $service): bool
    {
        return $user->isOwner() || $user->isAdmin() || $user->id === $service->technician_id;
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->isOwner() || $user->isAdmin();
    }

    public function assign(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin() || $user->isCs();
    }

    public function accept(User $user, Service $service): bool
    {
        return ($user->isTechnician() || $user->isOwner()) && $service->status === 'menunggu_alokasi';
    }

    public function start(User $user, Service $service): bool
    {
        return ($user->isOwner() || $user->id === $service->technician_id) && $service->status === 'diterima';
    }

    public function finish(User $user, Service $service): bool
    {
        return ($user->isOwner() || $user->id === $service->technician_id) && $service->status === 'dikerjakan';
    }

    public function cancel(User $user, Service $service): bool
    {
        return $user->isOwner() || $user->id === $service->technician_id;
    }

    public function confirm(User $user, Service $service): bool
    {
        return ($user->isOwner() || $user->id === $service->technician_id) && $service->status === 'dikerjakan';
    }

    public function approve(User $user, Service $service): bool
    {
        return $user->isOwner();
    }

    public function partner(User $user, Service $service): bool
    {
        return $user->isOwner() || $user->canWorkOnServices();
    }

    public function reallocate(User $user, Service $service): bool
    {
        return ($user->isOwner() || $user->id === $service->technician_id) && in_array($service->status, ['diterima', 'dikerjakan']);
    }

    public function takeOver(User $user, Service $service): bool
    {
        return !$user->isTechnician();
    }
}
