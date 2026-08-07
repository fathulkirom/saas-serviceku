<?php

namespace App\Enterprise\Data;

/**
 * TableRegistry — Central registry for all data table definitions.
 */
class TableRegistry
{
    /** @var DataDefinition[] */
    protected array $tables = [];

    public function register(DataDefinition $def): self
    {
        $this->tables[$def->id] = $def;
        return $this;
    }

    public function get(string $id): ?DataDefinition
    {
        return $this->tables[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return isset($this->tables[$id]);
    }

    public function all(): array
    {
        return $this->tables;
    }
}

/**
 * DataPresenter — Resolves table schemas with user context + data.
 */
class DataPresenter
{
    public function __construct(
        protected TableRegistry $registry,
    ) {}

    /**
     * Build table props for Inertia render.
     * 
     * @param string       $tableId   e.g. 'service.index'
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator
     * @param array        $params    Current query params (search, sort, filters)
     */
    public function build(string $tableId, $paginator, array $params = []): array
    {
        $user = auth()->user();
        $tenant = tenant();

        $userRole = $user?->role ?? 'admin';
        $planAccess = $tenant ? $tenant->getAllEffectiveFeatureAccess() : [];
        $rolePermissions = $this->getRolePermissions($userRole);

        $def = $this->registry->get($tableId);
        if (!$def) return ['error' => "Table '{$tableId}' not found."];

        $schema = $def->toSchema($userRole, $planAccess, $rolePermissions);

        return [
            'schema' => $schema,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'params' => $params,
            'user' => ['role' => $userRole, 'permissions' => $rolePermissions],
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
