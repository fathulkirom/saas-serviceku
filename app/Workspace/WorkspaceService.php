<?php

namespace App\Workspace;

use App\Services\FeatureEngine;

/**
 * WorkspaceService
 * 
 * Resolves workspace definitions with user context + backend data.
 * Thin orchestration layer — delegates to WorkspaceRegistry.
 */
class WorkspaceService
{
    public function __construct(
        protected WorkspaceRegistry $registry,
        protected FeatureEngine $featureEngine,
    ) {}

    /**
     * Build complete workspace config for a module.
     * 
     * @param string $workspaceId  e.g. 'service', 'inventory', 'pos'
     * @param array  $dataContext  Module-specific data (service, products, etc.)
     */
    public function build(string $workspaceId, array $dataContext = []): array
    {
        $user = auth()->user();
        $tenant = tenant();

        $userRole = $user?->role ?? 'admin';
        $planAccess = $tenant ? $tenant->getAllEffectiveFeatureAccess() : [];
        $rolePermissions = $this->getRolePermissions($userRole);
        $businessType = $tenant?->getBusinessType() ?? 'full_service';

        $definition = $this->registry->resolve(
            $workspaceId, $userRole, $planAccess, $rolePermissions, $businessType, $dataContext
        );

        if (!$definition) {
            return [
                'workspace' => null,
                'error' => "Workspace '{$workspaceId}' tidak ditemukan atau tidak dapat diakses.",
            ];
        }

        return [
            'workspace' => $definition,
            'data' => $dataContext,
            'user' => [
                'id' => $user?->id,
                'name' => $user?->name,
                'role' => $userRole,
                'permissions' => $rolePermissions,
            ],
            'meta' => [
                'businessType' => $businessType,
                'planAccess' => $planAccess,
                'timestamp' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Get all accessible workspaces for the current user (workspace switcher).
     */
    public function getAccessibleWorkspaces(): array
    {
        $user = auth()->user();
        $tenant = tenant();

        $userRole = $user?->role ?? 'admin';
        $planAccess = $tenant ? $tenant->getAllEffectiveFeatureAccess() : [];
        $rolePermissions = $this->getRolePermissions($userRole);
        $businessType = $tenant?->getBusinessType() ?? 'full_service';

        $workspaces = $this->registry->accessible($userRole, $planAccess, $rolePermissions, $businessType);

        return array_map(fn($ws) => [
            'id' => $ws->id,
            'title' => $ws->title,
            'icon' => $ws->icon,
        ], $workspaces);
    }

    // ── Private ──

    private function getRolePermissions(string $role): array
    {
        // Mirrors HandleInertiaRequests::share() role_permissions map
        $map = [
            'owner'      => ['manage_users','manage_settings','manage_finance','manage_products','manage_customers','manage_sales','manage_cash_register','manage_deposits','manage_purchases','manage_branches','manage_indents','void_transactions','assign_technician','work_on_services','delete_models','quick_stock'],
            'admin'      => ['manage_finance','manage_products','manage_customers','manage_sales','manage_cash_register','manage_deposits','manage_purchases','manage_indents','void_transactions','assign_technician','work_on_services','delete_models'],
            'manager'    => ['manage_finance','manage_products','manage_customers','manage_sales','manage_cash_register','manage_deposits','manage_purchases','manage_indents','work_on_services'],
            'head_store' => ['manage_finance','manage_products','manage_customers','manage_sales','manage_cash_register','manage_deposits','work_on_services'],
            'cs'         => ['manage_customers','manage_indents','assign_technician','work_on_services'],
            'technician' => ['work_on_services'],
            'cashier'    => ['manage_sales','manage_cash_register'],
            'courier'    => [],
            'custom'     => [],
        ];

        return $map[$role] ?? [];
    }
}
