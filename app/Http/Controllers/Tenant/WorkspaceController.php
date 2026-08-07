<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Workspace\WorkspaceService;
use Inertia\Inertia;

/**
 * WorkspaceController
 * 
 * Universal controller for ALL Enterprise Workspaces.
 * Thin — only renders Inertia pages.
 * 
 * Route pattern: /workspace/{module}/{id}
 * Example: /workspace/service/123 → Service Workspace for service #123
 */
class WorkspaceController extends Controller
{
    public function __construct(
        protected WorkspaceService $workspaceService,
    ) {}

    /**
     * GET /workspace/{module}/{id}
     * 
     * Render any workspace by module ID.
     */
    public function show(string $module, $id)
    {
        // Resolve module data (delegated to module-specific repository)
        $dataContext = $this->resolveDataContext($module, $id);

        if (!$dataContext) {
            abort(404, "Module '{$module}' dengan ID {$id} tidak ditemukan.");
        }

        $workspace = $this->workspaceService->build($module, $dataContext);

        if (!$workspace['workspace']) {
            abort(403, $workspace['error'] ?? 'Akses ditolak.');
        }

        return Inertia::render('Enterprise/Workspace/Index', [
            'workspaceConfig' => $workspace,
        ]);
    }

    /**
     * POST /workspace/{module}/{id}/action
     * 
     * Execute a workspace action (e.g., status transition).
     */
    public function execute(string $module, $id)
    {
        $validated = request()->validate([
            'action' => 'required|string',
            'payload' => 'array',
        ]);

        // Delegate to module-specific action handler
        $result = $this->executeModuleAction($module, $id, $validated['action'], $validated['payload'] ?? []);

        return response()->json($result);
    }

    /**
     * GET /workspace/switcher
     * 
     * Get all accessible workspaces for the workspace switcher.
     */
    public function switcher()
    {
        return response()->json([
            'workspaces' => $this->workspaceService->getAccessibleWorkspaces(),
        ]);
    }

    // ── Private resolvers ──

    private function resolveDataContext(string $module, $id): ?array
    {
        return match ($module) {
            'service' => $this->resolveServiceContext($id),
            // Future modules:
            // 'inventory' => $this->resolveInventoryContext($id),
            // 'pos'       => $this->resolvePosContext($id),
            // 'finance'   => $this->resolveFinanceContext($id),
            default => null,
        };
    }

    private function resolveServiceContext($id): ?array
    {
        $service = \App\Models\Tenant\Service::with([
            'customer', 'technician', 'creator', 'branch',
            'spareparts.product', 'diagnosis', 'photos.uploader',
            'sale.items', 'sale.payments', 'worklogs.user',
            'checklists.checklistTemplate.items',
        ])->find($id);

        if (!$service) return null;

        return [
            'id' => $service->id,
            'tracking_code' => $service->tracking_code,
            'status' => $service->status,
            'status_label' => $service->getStatusLabel(),
            'device_type' => $service->tipe_unit,
            'imei_sn' => $service->imei_sn,
            'problem_description' => $service->problem_description,
            'total_cost' => (float) $service->total_cost,
            'service_charge' => (float) $service->service_charge,
            'customer' => $service->customer?->only(['id', 'name', 'phone']),
            'technician' => $service->technician?->only(['id', 'name']),
            'spareparts' => $service->spareparts?->toArray(),
            'photos' => $service->photos?->toArray(),
            'diagnosis' => $service->diagnosis?->toArray(),
            'sale' => $service->sale?->toArray(),
            'worklogs' => $service->worklogs?->toArray(),
        ];
    }

    private function executeModuleAction(string $module, $id, string $action, array $payload): array
    {
        return match ($module) {
            'service' => $this->executeServiceAction($id, $action, $payload),
            default => ['success' => false, 'error' => "Unknown module: {$module}"],
        };
    }

    private function executeServiceAction($id, string $action, array $payload): array
    {
        $service = \App\Models\Tenant\Service::find($id);
        if (!$service) return ['success' => false, 'error' => 'Service not found'];

        // Status transitions
        $transitionMap = [
            'start' => 'dikerjakan',
            'complete' => 'selesai',
            'ready' => 'siap_diambil',
            'cancel' => 'cancel',
        ];

        if (isset($transitionMap[$action])) {
            $newStatus = $transitionMap[$action];
            if (!$service->canTransitionTo($newStatus)) {
                return ['success' => false, 'error' => "Cannot transition to {$newStatus}"];
            }
            $service->status = $newStatus;
            $service->save();

            // Worklog
            $service->worklogs()->create([
                'user_id' => auth()->id(),
                'action' => 'status_change',
                'description' => $payload['note'] ?? "Status: {$newStatus}",
                'metadata' => ['from' => $service->getOriginal('status'), 'to' => $newStatus],
            ]);

            return ['success' => true, 'newStatus' => $newStatus];
        }

        return ['success' => false, 'error' => "Unknown action: {$action}"];
    }
}
