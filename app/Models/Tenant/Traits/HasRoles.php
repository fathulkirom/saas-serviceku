<?php

namespace App\Models\Tenant\Traits;

trait HasRoles
{
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isHeadStore(): bool
    {
        return $this->role === 'head_store';
    }

    public function isCs(): bool
    {
        return $this->role === 'cs';
    }

    public function isTechnician(): bool
    {
        return $this->role === 'technician';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isCourier(): bool
    {
        return $this->role === 'courier';
    }

    public function canWorkOnServices(): bool
    {
        return in_array($this->role, ['owner', 'admin', 'manager', 'head_store', 'cs', 'technician']);
    }

    public function canManageUsers(): bool
    {
        return $this->isOwner();
    }

    public function canManageProducts(): bool
    {
        return $this->isOwner() || $this->isAdmin() || $this->isManager();
    }

    public function canManageCustomers(): bool
    {
        return $this->isOwner() || $this->isAdmin() || $this->isManager() || $this->isCs();
    }

    public function canManageFinance(): bool
    {
        return $this->isOwner() || $this->isAdmin() || $this->isManager() || $this->isHeadStore();
    }

    public function canManageSales(): bool
    {
        return $this->isOwner() || $this->isAdmin() || $this->isManager() || $this->isHeadStore() || $this->isCashier();
    }

    public function canVoidTransaction(): bool
    {
        return $this->isOwner() || $this->isAdmin();
    }

    public function canManageSettings(): bool
    {
        return $this->isOwner();
    }

    public function canDeleteUser(): bool
    {
        return $this->isOwner();
    }

    public function canAssignTechnician(): bool
    {
        return $this->isOwner() || $this->isAdmin() || $this->isCs();
    }

    public function canManageBranch(): bool
    {
        return $this->isOwner();
    }

    public function canManageCashRegister(): bool
    {
        return $this->isOwner() || $this->isAdmin() || $this->isCashier() || $this->isManager();
    }

    public function canConfirmDeposit(): bool
    {
        return $this->isOwner() || $this->isAdmin();
    }

    public function canManagePurchases(): bool
    {
        return $this->isOwner() || $this->isAdmin() || $this->isManager();
    }

    public function canDeleteModel(): bool
    {
        return $this->isOwner() || $this->isAdmin();
    }
}
