<?php

namespace App\Services;

use App\Models\Tenant\TenantSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Settings Service — unified API for ALL tenant settings.
 * Reads/writes to tenant_settings key-value store.
 * Uses SettingsRegistry for structure/validation/defaults.
 */
class SettingsService
{
    /**
     * Get a single setting value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::remember("tenant_settings:{$key}", 300, fn() =>
            TenantSetting::getValue($key)
        );

        if ($value === null) {
            return $default ?? SettingsRegistry::getDefault($key);
        }

        // Type coercion
        $config = SettingsRegistry::getAllFlat()[$key] ?? null;
        if ($config) {
            return match ($config['type']) {
                'boolean' => (bool) $value,
                'number' => (int) $value,
                default => $value,
            };
        }

        return $value;
    }

    /**
     * Set a single setting value.
     */
    public function set(string $key, mixed $value): void
    {
        TenantSetting::setValue($key, (string) $value);
        Cache::forget("tenant_settings:{$key}");
    }

    /**
     * Set multiple settings at once.
     */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Get all settings grouped by category, with current values.
     */
    public function getAllGrouped(): array
    {
        return Cache::remember('tenant_settings:all_grouped', 60, function () {
            $result = [];
            $flat = SettingsRegistry::getAllFlat();
            $stored = TenantSetting::all()->pluck('value', 'key')->toArray();

            foreach (SettingsRegistry::getAll() as $groupKey => $group) {
                $groupData = ['label' => $group['label'], 'icon' => $group['icon'], 'settings' => []];
                foreach ($group['settings'] as $settingKey => $config) {
                    $value = $stored[$settingKey] ?? $config['default'] ?? null;
                    $groupData['settings'][$settingKey] = array_merge($config, ['value' => $value]);
                }
                $result[$groupKey] = $groupData;
            }

            // Inject dynamic modules (from Sprint 7.1B)
            $result['modules']['settings'] = $this->getModuleSettings();

            return $result;
        });
    }

    /**
     * Get module activation settings from FeatureEngine.
     */
    public function getModuleSettings(): array
    {
        $engine = app(FeatureEngine::class);
        $tenant = tenant();

        if (!$tenant) return [];

        $modules = \App\Models\Tenant\Module::active()->orderBy('sort_order')->get();
        $activeKeys = $engine->getActiveModuleKeys($tenant);
        $result = [];

        foreach ($modules as $module) {
            $deps = $module->requires ?? [];
            $unmet = array_diff($deps, $activeKeys);
            $result[$module->key] = [
                'label' => $module->name,
                'description' => $module->description,
                'type' => 'boolean',
                'default' => in_array($module->key, $activeKeys),
                'value' => in_array($module->key, $activeKeys),
                'icon' => $module->icon,
                'requires' => $module->requires,
                'unmet_dependencies' => !empty($unmet) ? $unmet : null,
                'status' => $module->status,
            ];
        }

        return $result;
    }

    /**
     * Toggle a module on/off for the tenant.
     */
    public function toggleModule(string $tenantId, string $moduleKey, bool $enabled): void
    {
        $module = \App\Models\Tenant\Module::where('key', $moduleKey)->firstOrFail();

        \DB::table('tenant_modules')->updateOrInsert(
            ['module_id' => $module->id, 'tenant_id' => $tenantId],
            ['enabled' => $enabled, 'updated_at' => now()]
        );

        app(FeatureEngine::class)->clearCache(tenant());
        Cache::forget('tenant_settings:all_grouped');
    }

    /**
     * Get all settings as a flat key-value array (for Inertia sharing).
     */
    public function getAllFlat(): array
    {
        $result = [];
        $stored = TenantSetting::all()->pluck('value', 'key')->toArray();

        foreach (SettingsRegistry::getAllFlat() as $key => $config) {
            $result[$key] = $stored[$key] ?? $config['default'] ?? null;
        }

        return $result;
    }

    /**
     * Clear all settings cache.
     */
    public function clearCache(): void
    {
        Cache::forget('tenant_settings:all_grouped');
        foreach (SettingsRegistry::getAllFlat() as $key => $config) {
            Cache::forget("tenant_settings:{$key}");
        }
    }
}
