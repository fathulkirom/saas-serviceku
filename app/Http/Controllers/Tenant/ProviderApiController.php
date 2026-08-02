<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\ProviderRegistry;
use App\Services\ProviderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Provider API Controller — Sprint 7.1D + 7.2E.
 * REST API + Inertia page for managing all providers.
 */
class ProviderApiController extends Controller
{
    public function __construct(private ProviderService $service) {}

    /**
     * Inertia page: Provider Center UI (Sprint 7.2E)
     */
    public function page(): \Inertia\Response
    {
        return Inertia::render('Pengaturan/Providers', [
            'providers' => $this->service->getAllWithStatus(),
        ]);
    }

    /**
     * GET /tenant/api/providers — List all providers with status (JSON).
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'providers' => $this->service->getAllWithStatus(),
        ]);
    }

    /**
     * GET /tenant/api/providers/{category}/{key} — Get single provider detail.
     */
    public function show(string $category, string $key): JsonResponse
    {
        $provider = ProviderRegistry::getProvider($category, $key);
        if (!$provider) {
            return response()->json(['error' => 'Provider not found.'], 404);
        }

        return response()->json([
            'provider' => array_merge($provider, [
                'category' => $category,
                'key' => $key,
                'connection_status' => $this->service->getConnectionStatus($category, $key),
                'health' => $this->service->getHealth($category, $key),
            ]),
        ]);
    }

    /**
     * POST /tenant/api/providers/{category}/{key}/test — Test connection.
     */
    public function test(string $category, string $key): JsonResponse
    {
        $result = $this->service->testConnection($category, $key);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * POST /tenant/api/providers/{category}/{key}/toggle — Enable/disable a provider.
     */
    public function toggle(string $category, string $key, Request $request): JsonResponse
    {
        $enabled = $request->boolean('enabled', true);

        // Save to settings (Sprint 7.1C)
        $settings = app(\App\Services\SettingsService::class);
        $settings->set("provider_{$category}_{$key}_enabled", $enabled ? '1' : '0');

        return response()->json([
            'message' => $enabled ? "Provider '{$key}' diaktifkan." : "Provider '{$key}' dinonaktifkan.",
            'provider' => $key,
            'enabled' => $enabled,
        ]);
    }

    /**
     * GET /tenant/api/providers/{category}/{key}/health — Get health status.
     */
    public function health(string $category, string $key): JsonResponse
    {
        return response()->json([
            'provider' => $key,
            'category' => $category,
            'status' => $this->service->getConnectionStatus($category, $key),
            'health' => $this->service->getHealth($category, $key),
        ]);
    }
}
