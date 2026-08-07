<?php

namespace App\Enterprise\Form;

/**
 * FormPresenter — Transforms form definitions for Inertia rendering.
 * 
 * Handles user context resolution: role, permissions, feature access, business type.
 */
class FormPresenter
{
    public function __construct(
        protected FormRegistry $registry,
    ) {}

    /**
     * Build complete form props for Inertia render.
     * 
     * @param string $formId        e.g. 'service.create'
     * @param mixed  $data          Existing model data (for edit forms), null for create
     * @param array  $extra         Extra context (product list, customer list, etc.)
     */
    public function build(string $formId, mixed $data = null, array $extra = []): array
    {
        $user = auth()->user();
        $tenant = tenant();

        $userRole = $user?->role ?? 'admin';
        $planAccess = $tenant ? $tenant->getAllEffectiveFeatureAccess() : [];
        $rolePermissions = $this->getRolePermissions($userRole);
        $businessType = $tenant?->getBusinessType() ?? 'full_service';

        $schema = $this->registry->resolve($formId, $userRole, $planAccess, $rolePermissions, $businessType, $data);

        if (!$schema) {
            return ['error' => "Form '{$formId}' not found or inaccessible."];
        }

        return [
            'schema' => $schema,
            'data' => $data,
            'extra' => $extra,
            'validation' => [
                'rules' => $this->registry->get($formId)?->getValidationRules() ?? [],
            ],
            'user' => [
                'role' => $userRole,
                'permissions' => $rolePermissions,
            ],
            'meta' => [
                'businessType' => $businessType,
                'featureAccess' => $planAccess,
                'isEdit' => $data !== null,
            ],
        ];
    }

    private function getRolePermissions(string $role): array
    {
        $map = [
            'owner' => ['manage_users','manage_settings','manage_finance','manage_products','manage_customers','manage_sales','manage_cash_register','manage_deposits','manage_purchases','manage_branches','manage_indents','void_transactions','assign_technician','work_on_services','delete_models','quick_stock'],
            'admin' => ['manage_finance','manage_products','manage_customers','manage_sales','manage_cash_register','manage_deposits','manage_purchases','manage_indents','void_transactions','assign_technician','work_on_services','delete_models'],
            'manager' => ['manage_finance','manage_products','manage_customers','manage_sales','manage_cash_register','manage_deposits','manage_purchases','manage_indents','work_on_services'],
            'head_store' => ['manage_finance','manage_products','manage_customers','manage_sales','manage_cash_register','manage_deposits','work_on_services'],
            'cs' => ['manage_customers','manage_indents','assign_technician','work_on_services'],
            'technician' => ['work_on_services'],
            'cashier' => ['manage_sales','manage_cash_register'],
            'courier' => [],
            'custom' => [],
        ];
        return $map[$role] ?? [];
    }
}
