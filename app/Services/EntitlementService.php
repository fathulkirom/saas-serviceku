<?php

namespace App\Services;

use App\Enums\SubscriptionCapacity;
use App\Models\Tenant;
use App\Models\TenantAddon;

/**
 * UPGRADE-03: Effective Entitlement Engine.
 *
 * THE single source of truth for "what can this tenant do right now?"
 * All application surfaces (middleware, controllers, views, policies) ask
 * this engine instead of checking plan slugs or manual feature flags.
 *
 * Formula:
 *   Effective Entitlement = Base Plan Baseline + Active Add-ons
 */
class EntitlementService
{
    protected Tenant $tenant;
    protected ?array $planDef = null;
    protected ?array $activeModules = null;
    protected ?array $activeFeatures = null;
    protected ?array $activeCapacities = null;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    // ── Plan ──────────────────────────────────────────────────────────────

    public function planSlug(): string
    {
        return $this->tenant->plan?->slug ?? 'trial';
    }

    /** Baseline plan definition from subscription config. */
    public function planDefinition(): array
    {
        if ($this->planDef === null) {
            $this->planDef = config("subscription.plans.{$this->planSlug()}", config('subscription.plans.trial'));
        }
        return $this->planDef;
    }

    // ── Modules ───────────────────────────────────────────────────────────

    /** All modules the tenant can USE right now (included + add-on). */
    public function activeModules(): array
    {
        if ($this->activeModules === null) {
            $planModules = $this->planDefinition()['modules'] ?? [];
            $addonModules = collect(TenantAddon::activeFor($this->tenant->id, 'module'))
                ->pluck('key')->all();
            $this->activeModules = array_unique(array_merge($planModules, $addonModules));
        }
        return $this->activeModules;
    }

    /** Can the tenant use a specific module? */
    public function canUse(string $module): bool
    {
        return in_array($module, $this->activeModules(), true);
    }

    /**
     * Access level for a module: create | read | update | delete.
     * If the module was removed (downgrade), access becomes read-only.
     */
    public function moduleAccess(string $module): array
    {
        if (in_array($module, $this->planDefinition()['modules'] ?? [], true)) {
            // Included in base plan → full access
            return config('subscription.module_access.full');
        }
        if (in_array($module, $this->activeModules(), true)) {
            // Purchased as add-on → full (while active)
            return config('subscription.module_access.full');
        }
        // Module not active → no access at all
        return config('subscription.module_access.none');
    }

    /** Convenience: can the tenant create in this module? */
    public function canCreate(string $module): bool
    {
        return $this->moduleAccess($module)['create'] ?? false;
    }

    /** Downgrade-safe: can tenant still READ historical data in this module? */
    public function canRead(string $module): bool
    {
        $planModules = $this->planDefinition()['modules'] ?? [];
        // If module was EVER active (in plan OR add-on), read access remains.
        // This preserves data visibility during downgrade.
        $wasActive = in_array($module, $planModules, true)
            || TenantAddon::where('tenant_id', $this->tenant->id)
                ->where('type', 'module')->where('key', $module)->exists();
        return $wasActive;
    }

    // ── Features ───────────────────────────────────────────────────────────

    public function featureAccess(string $feature): string
    {
        $planFeatures = $this->planDefinition()['features'] ?? [];
        return $planFeatures[$feature] ?? 'none';
    }

    public function hasFeature(string $feature): bool
    {
        return $this->featureAccess($feature) !== 'none';
    }

    // ── Capacities ─────────────────────────────────────────────────────────

    /** Effective limit for a capacity (base plan + active add-ons). */
    public function limit(string $capacity): int
    {
        $base = match ($capacity) {
            'users'    => (int) ($this->planDefinition()['max_users'] ?? 0),
            'branches' => (int) ($this->planDefinition()['max_branches'] ?? 0),
            default    => 0,
        };

        // Add active capacity add-ons.
        $addonKey = match ($capacity) {
            'users'    => SubscriptionCapacity::ExtraUsers->value,
            'branches' => SubscriptionCapacity::ExtraBranches->value,
            default    => null,
        };
        if ($addonKey) {
            $extra = TenantAddon::activeFor($this->tenant->id, 'capacity');
            foreach ($extra as $addon) {
                if ($addon->key === $addonKey) {
                    $base += (int) $addon->quantity;
                }
            }
        }

        return $base;
    }

    /** Current usage vs limit. */
    public function usage(string $capacity): array
    {
        $limit = $this->limit($capacity);
        $used  = match ($capacity) {
            'users'    => \App\Models\Tenant\User::count(),
            'branches' => \Illuminate\Support\Facades\DB::table('branches')->count(),
            default    => 0,
        };

        return [
            'used'  => $used,
            'limit' => $limit,
            'available' => max(0, $limit - $used),
            'over_limit' => $used > $limit,
        ];
    }

    /** Full entitlement snapshot for the Tenant Subscription Page. */
    public function snapshot(): array
    {
        return [
            'plan_slug'    => $this->planSlug(),
            'plan_name'    => $this->planDefinition()['name'] ?? 'Unknown',
            'modules'      => $this->activeModules(),
            'plan_modules' => $this->planDefinition()['modules'] ?? [],
            'features'     => $this->planDefinition()['features'] ?? [],
            'limits'       => [
                'users'    => $this->usage('users'),
                'branches' => $this->usage('branches'),
            ],
            'addons'       => TenantAddon::where('tenant_id', $this->tenant->id)
                ->where('status', 'active')->get()->toArray(),
        ];
    }

    // ── Over-limit handling ───────────────────────────────────────────────

    /** Whether the tenant has exceeded any capacity limit. */
    public function isOverLimit(string $capacity): bool
    {
        return $this->usage($capacity)['over_limit'];
    }

    /** List of all exceeded capacities. */
    public function overLimits(): array
    {
        return array_filter(['users', 'branches'], fn($c) => $this->isOverLimit($c));
    }
}
