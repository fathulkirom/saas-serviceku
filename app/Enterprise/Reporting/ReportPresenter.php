<?php

namespace App\Enterprise\Reporting;

class ReportPresenter
{
    public function __construct(
        protected ReportRegistry $registry,
        protected AggregationEngine $aggregator,
        protected ChartEngine $chartEngine,
    ) {}

    public function build(string $reportId, array $filterValues = []): array
    {
        $user = auth()->user();
        $tenant = tenant();
        $userRole = $user?->role ?? 'admin';
        $planAccess = $tenant?->getAllEffectiveFeatureAccess() ?? [];
        $rolePermissions = $this->getRolePermissions($userRole);

        $report = $this->registry->get($reportId);
        if (! $report) {
            return ['error' => "Report '{$reportId}' not found."];
        }

        if (! $report->isAccessible($userRole, $planAccess, $rolePermissions)) {
            return ['error' => 'Access denied.', 'schema' => $report->toArray(false)];
        }

        $query = $report->buildQuery($filterValues);
        $rows = $query ? $query->get() : collect();

        $computedMetrics = [];
        foreach ($report->metrics as $metric) {
            $computedMetrics[$metric->id] = $this->aggregator->compute($metric, $rows);
        }

        $groupedData = $rows->toArray();
        if (! empty($report->dimensions)) {
            $dimensionFields = array_map(fn ($dimension) => $dimension->field, $report->dimensions);
            $groupedData = [];
            foreach ($rows->groupBy($dimensionFields) as $key => $group) {
                $row = [];
                foreach ($report->dimensions as $index => $dimension) {
                    $row[$dimension->field] = is_array($key) ? ($key[$dimension->field] ?? $key[$index] ?? $key) : $key;
                }
                foreach ($report->metrics as $metric) {
                    $row[$metric->id] = $this->aggregator->compute($metric, $group);
                }
                $groupedData[] = $row;
            }
        }

        return [
            'schema' => $report->toArray(true),
            'data' => $groupedData,
            'metrics' => $computedMetrics,
            'chartData' => $this->chartEngine->formatForChart($report, $groupedData, []),
            'filters' => $filterValues,
            'user' => ['role' => $userRole, 'permissions' => $rolePermissions],
            'generatedAt' => now()->toISOString(),
        ];
    }

    public function getAccessibleReports(): array
    {
        $user = auth()->user();
        $tenant = tenant();

        return array_map(fn ($report) => [
            'id' => $report->id,
            'title' => $report->title,
            'type' => $report->type,
            'chartType' => $report->chartType,
        ], $this->registry->accessible(
            $user?->role ?? 'admin',
            $tenant?->getAllEffectiveFeatureAccess() ?? [],
            $this->getRolePermissions($user?->role ?? 'admin')
        ));
    }

    private function getRolePermissions(string $role): array
    {
        $map = [
            'owner' => ['manage_users', 'manage_settings', 'manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'manage_purchases', 'manage_branches', 'manage_indents', 'void_transactions', 'assign_technician', 'work_on_services', 'delete_models', 'quick_stock'],
            'admin' => ['manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'manage_purchases', 'manage_indents', 'void_transactions', 'assign_technician', 'work_on_services', 'delete_models'],
            'manager' => ['manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'manage_purchases', 'manage_indents', 'work_on_services'],
            'head_store' => ['manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'work_on_services'],
            'cs' => ['manage_customers', 'manage_indents', 'assign_technician', 'work_on_services'],
            'technician' => ['work_on_services'],
            'cashier' => ['manage_sales', 'manage_cash_register'],
            'courier' => [],
            'custom' => [],
        ];

        return $map[$role] ?? [];
    }
}
