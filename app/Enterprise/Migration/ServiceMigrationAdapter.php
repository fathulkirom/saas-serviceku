<?php

namespace App\Enterprise\Migration;

use App\Enterprise\Data\DataPresenter;
use App\Enterprise\Data\TableRegistry;
use App\Enterprise\Form\FormPresenter;
use App\Enterprise\Form\FormRegistry;
use App\Enterprise\Reporting\ReportPresenter;
use App\Enterprise\Reporting\ReportRegistry;
use App\Models\Tenant\Service;
use Inertia\Inertia;

/**
 * ServiceMigrationAdapter
 * 
 * Bridges the existing ServiceController to Enterprise Engines.
 * Does NOT modify existing controllers — provides drop-in replacements
 * that can be swapped route-by-route.
 * 
 * Usage (in controller):
 *   return ServiceMigrationAdapter::index();
 *   return ServiceMigrationAdapter::create();
 *   return ServiceMigrationAdapter::show($service);
 */
class ServiceMigrationAdapter
{
    /**
     * Service List — migrated to Enterprise Data Engine.
     * Replaces: ServiceController@index
     */
    public static function index(): \Inertia\Response
    {
        $params = request()->only(['search', 'sort', 'filters', 'page', 'perPage']);

        $query = Service::query()
            ->with(['customer', 'technician', 'branch'])
            ->selectRaw('services.*, customers.name as customer_name, users.name as technician_name')
            ->leftJoin('customers', 'customers.id', '=', 'services.customer_id')
            ->leftJoin('users', 'users.id', '=', 'services.technician_id');

        // Apply query from DataEngine
        $registry = app(TableRegistry::class);
        $def = $registry->get('service.index');
        if ($def) {
            $query = $def->applyToQuery($query, $params);
        }

        $paginator = $query->paginate($params['perPage'] ?? 25);

        $presenter = app(DataPresenter::class);
        $tableProps = $presenter->build('service.index', $paginator, $params);

        return Inertia::render('Services/Index', [
            'tableProps' => $tableProps,
            'stats' => self::getServiceStats(),
        ]);
    }

    /**
     * Service Create — migrated to Form Engine.
     * Replaces: ServiceController@create
     */
    public static function create(): \Inertia\Response
    {
        $presenter = app(FormPresenter::class);
        $formSchema = $presenter->build('service.create');

        // Extra context for async selects
        $formSchema['extra'] = [
            'customers' => [], // Will be loaded async via autocomplete
            'categories' => self::getMasterData('kategori_perangkat'),
            'brands' => self::getMasterData('merek'),
        ];

        return Inertia::render('Services/Create', [
            'formSchema' => $formSchema['schema'],
            'extra' => $formSchema['extra'],
        ]);
    }

    /**
     * Service Edit — migrated to Form Engine.
     * Replaces: ServiceController@edit
     */
    public static function edit(Service $service): \Inertia\Response
    {
        $presenter = app(FormPresenter::class);
        $formSchema = $presenter->build('service.edit', $service);

        return Inertia::render('Services/Edit', [
            'formSchema' => $formSchema['schema'],
            'service' => $service,
        ]);
    }

    /**
     * Service Workspace — migrated to Workspace Engine.
     * Replaces: ServiceController@show
     * 
     * Uses the Workspace Engine from Sprint 10.0.
     */
    public static function show(Service $service): \Inertia\Response
    {
        // Delegate to the existing WorkspaceService (Sprint 10.0)
        $workspaceService = app(\App\Workspace\WorkspaceService::class);
        $workspace = $workspaceService->build('service', self::transformService($service));

        // Also load report data for dashboard widgets
        $reportPresenter = app(ReportPresenter::class);
        $serviceReports = $reportPresenter->getAccessibleReports();

        return Inertia::render('ServiceWorkspace/Index', [
            'workspaceConfig' => $workspace,
            'reportConfig' => [
                'availableReports' => $serviceReports,
            ],
        ]);
    }

    /**
     * Get dashboard stats for service index.
     */
    private static function getServiceStats(): array
    {
        return [
            'services_today' => Service::whereDate('created_at', today())->count(),
            'active_services' => Service::whereIn('status', ['menunggu_alokasi','diterima','diagnosa','dikerjakan','indent','onpartner'])->count(),
            'low_stock' => 0, // From Product model
            'revenue_today' => Service::whereDate('created_at', today())->sum('total_cost'),
            'statusCounts' => Service::selectRaw('status, count(*) as total')
                ->groupBy('status')->pluck('total', 'status')->toArray(),
        ];
    }

    private static function transformService(Service $service): array
    {
        return [
            'id' => $service->id,
            'tracking_code' => $service->tracking_code,
            'status' => $service->status,
            'status_label' => $service->getStatusLabel(),
            'status_color' => $service->getStatusColor(),
            'device_type' => $service->tipe_unit,
            'imei_sn' => $service->imei_sn,
            'problem_description' => $service->problem_description,
            'condition_note' => $service->condition_note,
            'total_cost' => (float) $service->total_cost,
            'service_charge' => (float) $service->service_charge,
            'payment_status' => $service->payment_status,
            'is_warranty_claim' => (bool) $service->is_warranty_claim,
            'warranty_days' => (int) $service->warranty_days,
            'created_at' => $service->created_at?->toISOString(),
            'updated_at' => $service->updated_at?->toISOString(),
            'dikerjakan_at' => $service->dikerjakan_at?->toISOString(),
            'selesai_at' => $service->selesai_at?->toISOString(),
            'customer' => $service->customer?->only(['id', 'name', 'phone']),
            'technician' => $service->technician?->only(['id', 'name']),
            'creator' => $service->creator?->only(['id', 'name']),
            'branch' => $service->branch?->only(['id', 'name']),
            'spareparts' => $service->spareparts?->toArray(),
            'photos' => $service->photos?->toArray(),
            'diagnosis' => $service->diagnosis?->toArray(),
            'sale' => $service->sale?->toArray(),
            'worklogs' => $service->worklogs?->with('user')->latest()->limit(50)->get()->toArray(),
            'checklists' => $service->checklists?->toArray(),
        ];
    }

    private static function getMasterData(string $type): array
    {
        return \App\Models\Tenant\MasterData::where('type', $type)
            ->get(['id', 'name', 'code'])
            ->toArray();
    }
}
