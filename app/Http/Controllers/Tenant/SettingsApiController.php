<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Settings API Controller — CRUD operations for the unified Settings Engine (Sprint 7.1C).
 * Used by the Vue settings UI for dynamic settings management.
 */
class SettingsApiController extends Controller
{
    public function __construct(private SettingsService $service) {}

    /**
     * GET /tenant/api/settings — Get all settings grouped.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'settings' => $this->service->getAllGrouped(),
        ]);
    }

    /**
     * GET /tenant/api/settings/{key} — Get a single setting.
     */
    public function show(string $key): JsonResponse
    {
        return response()->json([
            'key' => $key,
            'value' => $this->service->get($key),
        ]);
    }

    /**
     * POST /tenant/api/settings — Save settings (single or batch).
     */
    public function store(Request $request): JsonResponse
    {
        $values = $request->input('settings', []);
        if (empty($values)) {
            return response()->json(['error' => 'No settings provided.'], 422);
        }

        $this->service->setMany($values);

        return response()->json([
            'message' => 'Pengaturan berhasil disimpan.',
            'updated' => array_keys($values),
        ]);
    }

    /**
     * POST /tenant/api/settings/modules/{key}/toggle — Toggle a module on/off.
     */
    public function toggleModule(string $key, Request $request): JsonResponse
    {
        $enabled = $request->boolean('enabled', true);
        $tenantId = tenant()->id;

        $this->service->toggleModule($tenantId, $key, $enabled);

        return response()->json([
            'message' => $enabled ? "Modul '{$key}' diaktifkan." : "Modul '{$key}' dinonaktifkan.",
            'module' => $key,
            'enabled' => $enabled,
        ]);
    }
}
