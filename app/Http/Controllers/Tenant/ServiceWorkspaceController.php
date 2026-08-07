<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Services\ServiceWorkspaceService;
use Inertia\Inertia;

/**
 * ServiceWorkspaceController
 * 
 * Controller hanya bertanggung jawab untuk routing Inertia.
 * Semua business logic ada di ServiceWorkspaceService.
 * Semua data aggregation ada di ServiceWorkspaceRepository.
 */
class ServiceWorkspaceController extends Controller
{
    public function __construct(
        protected ServiceWorkspaceService $workspaceService,
    ) {}

    /**
     * GET /services/{service}/workspace
     * 
     * Enterprise Service Workspace — pusat aktivitas servis.
     * Digunakan oleh CS, Teknisi, Manager, Owner sesuai permission.
     */
    public function show(Service $service)
    {
        // Authorization via Policy
        $this->authorize('view', $service);

        // Build complete workspace data
        $workspace = $this->workspaceService->build($service);

        return Inertia::render('ServiceWorkspace/Index', [
            'workspace' => $workspace,
        ]);
    }

    /**
     * POST /services/{service}/workspace/transition
     * 
     * Execute status transition (via workspace action bar).
     */
    public function transition(Service $service)
    {
        $this->authorize('update', $service);

        $validated = request()->validate([
            'status' => 'required|string',
            'note' => 'nullable|string|max:500',
            'technician_id' => 'nullable|exists:users,id',
        ]);

        $service = $this->workspaceService->transition(
            $service,
            $validated['status'],
            ['note' => $validated['note'] ?? null]
        );

        if ($validated['technician_id'] ?? null) {
            $service->technician_id = $validated['technician_id'];
            $service->save();
        }

        // Return fresh workspace data
        return response()->json([
            'success' => true,
            'workspace' => $this->workspaceService->build($service->fresh()),
        ]);
    }

    /**
     * GET /services/{service}/workspace/refresh
     * 
     * Refresh workspace data (partial reload).
     */
    public function refresh(Service $service)
    {
        $this->authorize('view', $service);

        return response()->json([
            'workspace' => $this->workspaceService->build($service->fresh()),
        ]);
    }
}
