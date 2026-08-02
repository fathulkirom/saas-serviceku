<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\Tenant\Module;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Feature Engine — unified feature resolution (Blueprint v1.0 §Feature Engine).
 *
 * Resolution order (first blocking match wins):
 *   1. Module explicitly disabled by tenant → 'none'
 *   2. Plan feature access level → 'full' / 'read_only' / 'none'
 *   3. Business type structural constraint (legacy fallback) → 'none' for retail_only+services/checklist
 *
 * Completely backward compatible — existing tenants without tenant_modules entries
 * pass through to plan check, which is the original behavior.
 */
class FeatureEngine
{
    public function getAccessLevel(Tenant $tenant, string $feature): string
    {
        $cacheKey = "tenant:{$tenant->id}:feature:{$feature}";
        return Cache::remember($cacheKey, 300, function () use ($tenant, $feature) {
            // Layer 1: Module explicitly disabled?
            $moduleLevel = $this->checkModuleActivation($tenant, $feature);
            if ($moduleLevel === 'none') return 'none';

            // Layer 2: Plan feature
            $planLevel = $this->checkPlanFeature($tenant, $feature);
            if ($planLevel === 'none') return 'none';
            if ($planLevel === 'read_only') return 'read_only';

            // Layer 3: Business type constraint (legacy)
            return $this->checkBusinessTypeConstraint($tenant, $feature);
        });
    }

    public function getAllFeatures(Tenant $tenant): array
    {
        $result = [];
        foreach ($this->getAllFeatureKeys() as $f) {
            $result[$f] = $this->getAccessLevel($tenant, $f);
        }
        return $result;
    }

    public function can(Tenant $tenant, string $feature, string $minLevel = 'read_only'): bool
    {
        $level = $this->getAccessLevel($tenant, $feature);
        return $minLevel === 'read_only' ? $level !== 'none' : $level === 'full';
    }

    public function getActiveModuleKeys(Tenant $tenant): array
    {
        return Cache::remember("tenant:{$tenant->id}:active_modules", 300, function () use ($tenant) {
            $disabled = DB::table('tenant_modules')
                ->where('tenant_id', $tenant->id)
                ->where('enabled', false)
                ->pluck('module_id');
            return Module::active()->whereNotIn('id', $disabled)->pluck('key')->toArray();
        });
    }

    public function getAllFeatureKeys(): array
    {
        return Cache::remember('module:all_feature_keys', 3600, fn() =>
            Module::active()->get()->flatMap->getFeatureKeys()->unique()->values()->toArray()
        );
    }

    protected function checkModuleActivation(Tenant $tenant, string $feature): ?string
    {
        $module = Module::active()->whereJsonContains('features', $feature)->first();
        if (!$module) return null;
        $row = DB::table('tenant_modules')->where('module_id', $module->id)->where('tenant_id', $tenant->id)->first();
        if ($row && !$row->enabled) return 'none';
        return null; // no entry or enabled=true → pass through
    }

    protected function checkPlanFeature(Tenant $tenant, string $feature): ?string
    {
        $plan = $tenant->plan;
        if (!$plan) return 'none';
        return $plan->featureAccessLevel($feature);
    }

    protected function checkBusinessTypeConstraint(Tenant $tenant, string $feature): string
    {
        if ($tenant->getBusinessType() === 'retail_only' && in_array($feature, ['services', 'checklist'])) {
            return 'none';
        }
        return 'full';
    }

    public function clearCache(Tenant $tenant): void
    {
        foreach ($this->getAllFeatureKeys() as $key) {
            Cache::forget("tenant:{$tenant->id}:feature:{$key}");
        }
        Cache::forget("tenant:{$tenant->id}:active_modules");
    }
}
