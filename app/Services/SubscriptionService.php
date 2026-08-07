<?php

namespace App\Services;

use App\Models\SubscriptionEvent;
use App\Models\Tenant;
use App\Models\TenantAddon;
use App\Enums\SubscriptionModule;
use App\Enums\SubscriptionFeature;
use App\Enums\SubscriptionCapacity;
use Illuminate\Support\Facades\Log;

/**
 * UPGRADE-07: Subscription Lifecycle Service.
 *
 * Handles plan changes, add-on management, upgrade/downgrade with the
 * GOLDEN RULE: data never deleted, access/capacity may change.
 */
class SubscriptionService
{
    protected EntitlementService $entitlement;

    public function __construct(protected Tenant $tenant)
    {
        $this->entitlement = new EntitlementService($tenant);
    }

    // ── Plan Upgrade ──────────────────────────────────────────────────────

    /**
     * Upgrade tenant to a new plan.
     * Simple: capability opens immediately, no data migration.
     */
    public function upgradePlan(string $newPlanSlug, ?string $actorId = null, ?string $reason = null): void
    {
        $oldPlan = $this->tenant->plan?->slug ?? 'trial';
        $planConfig = config("subscription.plans.{$newPlanSlug}");
        if (!$planConfig) {
            throw new \InvalidArgumentException("Unknown plan: {$newPlanSlug}");
        }

        $plan = \App\Models\Plan::where('slug', $newPlanSlug)->first();
        if (!$plan) {
            throw new \InvalidArgumentException("Plan not found in database: {$newPlanSlug}");
        }

        $this->tenant->update([
            'plan_id'             => $plan->id,
            'subscription_status' => $newPlanSlug === 'trial' ? 'trial' : 'active',
            'trial_ends_at'       => $newPlanSlug === 'trial' ? now()->addDays($planConfig['trial_days'] ?? 14) : null,
            'subscribed_at'       => $newPlanSlug !== 'trial' ? now() : null,
            'is_active'           => true,
        ]);

        SubscriptionEvent::log(
            $this->tenant->id, 'plan_changed',
            $actorId, $oldPlan, $newPlanSlug, $reason,
            ['plan_name' => $planConfig['name']]
        );

        Log::info("Tenant {$this->tenant->id} upgraded plan: {$oldPlan} → {$newPlanSlug}");
    }

    // ── Plan Downgrade ────────────────────────────────────────────────────

    /**
     * Downgrade tenant to a lower plan.
     * GOLDEN RULE: never delete data. Excess users → inactive, excess
     * branches → locked, removed modules → read-only.
     */
    public function downgradePlan(string $newPlanSlug, ?string $actorId = null, ?string $reason = null): void
    {
        $oldPlan = $this->tenant->plan?->slug ?? 'trial';
        $planConfig = config("subscription.plans.{$newPlanSlug}");
        if (!$planConfig) {
            throw new \InvalidArgumentException("Unknown plan: {$newPlanSlug}");
        }

        // ── 1. Preserve snapshot of current state ──
        $beforeSnapshot = $this->entitlement->snapshot();

        // ── 2. Update plan ──
        $plan = \App\Models\Plan::where('slug', $newPlanSlug)->first();
        $this->tenant->update([
            'plan_id'             => $plan->id,
            'subscription_status' => 'active',
        ]);

        // ── 3. Handle over-limit users ──
        $userLimit = (int) ($planConfig['max_users'] ?? 0);
        $activeUserCount = \App\Models\Tenant\User::where('active', true)->count();
        if ($activeUserCount > $userLimit) {
            // Suspend excess users (most recently created first).
            $excess = $activeUserCount - $userLimit;
            \App\Models\Tenant\User::where('active', true)
                ->latest()
                ->take($excess)
                ->update(['active' => false]);

            Log::info("Downgrade: {$excess} users suspended for tenant {$this->tenant->id}");
        }

        // ── 4. Handle over-limit branches ──
        $branchLimit = (int) ($planConfig['max_branches'] ?? 1);
        $activeBranchCount = \Illuminate\Support\Facades\DB::table('branches')
            ->where('is_active', true)->count();
        if ($activeBranchCount > $branchLimit) {
            $excess = $activeBranchCount - $branchLimit;
            \Illuminate\Support\Facades\DB::table('branches')
                ->where('is_active', true)->latest()->take($excess)
                ->update(['is_active' => false]);

            Log::info("Downgrade: {$excess} branches locked for tenant {$this->tenant->id}");
        }

        // ── 5. Audit ──
        SubscriptionEvent::log(
            $this->tenant->id, 'plan_changed',
            $actorId, $oldPlan, $newPlanSlug, $reason,
            ['before' => $beforeSnapshot, 'plan_name' => $planConfig['name']]
        );

        Log::info("Tenant {$this->tenant->id} downgraded plan: {$oldPlan} → {$newPlanSlug}");
    }

    // ── Add-on Management ─────────────────────────────────────────────────

    /** Activate a module add-on for the tenant. */
    public function activateModule(string $module, ?string $actorId = null): TenantAddon
    {
        $moduleEnum = SubscriptionModule::tryFrom($module);
        if (!$moduleEnum) {
            throw new \InvalidArgumentException("Unknown module: {$module}");
        }

        $addon = TenantAddon::create([
            'tenant_id'     => $this->tenant->id,
            'type'          => 'module',
            'key'           => $module,
            'quantity'      => 1,
            'status'        => 'active',
            'started_at'    => now(),
            'expires_at'    => null,
            'billing_cycle' => 'monthly',
            'price'         => 0, // pricing TBD
        ]);

        SubscriptionEvent::log(
            $this->tenant->id, 'addon_activated',
            $actorId, null, $module,
            "Module {$moduleEnum->label()} diaktifkan"
        );

        return $addon;
    }

    /** Activate a capacity add-on. */
    public function activateCapacity(string $capacity, int $quantity, ?string $actorId = null): TenantAddon
    {
        $capEnum = SubscriptionCapacity::tryFrom($capacity);
        if (!$capEnum) {
            throw new \InvalidArgumentException("Unknown capacity: {$capacity}");
        }

        $addon = TenantAddon::create([
            'tenant_id'     => $this->tenant->id,
            'type'          => 'capacity',
            'key'           => $capacity,
            'quantity'      => $quantity,
            'status'        => 'active',
            'started_at'    => now(),
            'expires_at'    => null,
            'billing_cycle' => 'monthly',
            'price'         => 0,
        ]);

        SubscriptionEvent::log(
            $this->tenant->id, 'addon_activated',
            $actorId, null, "{$capacity} x{$quantity}",
            "Kapasitas {$capEnum->label()} ditambahkan"
        );

        return $addon;
    }

    /** Expire an add-on (non-destructive — data stays). */
    public function expireAddon(TenantAddon $addon, ?string $actorId = null): void
    {
        $oldStatus = $addon->status;
        $addon->update(['status' => 'expired', 'expires_at' => now()]);

        SubscriptionEvent::log(
            $this->tenant->id, 'addon_expired',
            $actorId, $oldStatus, 'expired',
            "Add-on {$addon->key} kadaluarsa"
        );
    }

    // ── Tenant Lifecycle ──────────────────────────────────────────────────

    public function suspend(?string $actorId = null, ?string $reason = null): void
    {
        $this->tenant->update(['is_active' => false, 'subscription_status' => 'suspended']);
        SubscriptionEvent::log($this->tenant->id, 'tenant_suspended', $actorId, null, null, $reason);
    }

    public function reactivate(?string $actorId = null, ?string $reason = null): void
    {
        $this->tenant->update(['is_active' => true, 'subscription_status' => 'active']);
        SubscriptionEvent::log($this->tenant->id, 'tenant_reactivated', $actorId, null, null, $reason);
    }
}
