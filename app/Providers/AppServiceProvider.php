<?php

namespace App\Providers;

use App\Events\WorkflowStateChanged;
use App\Events\WorkflowTransitioned;
use App\Listeners\EventLogger;
use App\Listeners\TriggerAutomationRules;
use App\Services\MailConfigService;
use App\Services\ProviderAdapter;
use App\Subscribers\AutomationSubscriber;
use App\Subscribers\WorkflowPersistenceSubscriber;
use App\Workspace\WorkspaceRegistry;
use App\Workspace\Definitions\ServiceWorkspace;
use App\Enterprise\Automation\AutomationRegistry;
use App\Enterprise\Automation\Definitions\ServiceAutomations;
use App\Enterprise\Reporting\ReportRegistry;
use App\Enterprise\Reporting\Definitions\ServiceReports;
use App\Enterprise\Definitions\InventoryDefinitions;
use App\Enterprise\Definitions\PurchasingDefinitions;
use App\Enterprise\Definitions\CRMDefinitions;
use App\Enterprise\Definitions\FinanceDefinitions;
use App\Enterprise\Definitions\HRMDefinitions;
use App\Enterprise\Definitions\AssetDefinitions;
use App\Enterprise\Definitions\ProjectDefinitions;
use App\Enterprise\Definitions\POSDefinitions;
use App\Enterprise\Definitions\ManufacturingDefinitions;
use App\Enterprise\Definitions\WarehouseDefinitions;
use App\Enterprise\Definitions\DocumentDefinitions;
use App\Enterprise\Definitions\AIDefinitions;
use App\Enterprise\Definitions\IntegrationDefinitions;
use App\Enterprise\Definitions\PlatformDefinitions;
use App\Enterprise\Definitions\PortalDefinitions;
use App\Enterprise\Definitions\NotificationDefinitions;
use App\Enterprise\Definitions\WorkflowDefinitions;
use App\Enterprise\Definitions\GRCDefinitions;
use App\Enterprise\Definitions\EPOCDefinitions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Sprint 7.2D — Provider Adapter (singleton)
        $this->app->singleton(ProviderAdapter::class);

        // Sprint 10.0 — Workspace Engine (singleton registry)
        $this->app->singleton(WorkspaceRegistry::class, function () {
            $registry = new WorkspaceRegistry();

            // Register all workspace definitions
            $registry->registerAll([
                new ServiceWorkspace(),
                InventoryDefinitions::workspace(),
                PurchasingDefinitions::workspace(),
                PurchasingDefinitions::supplierWorkspace(),
                CRMDefinitions::customerWorkspace(),
                FinanceDefinitions::workspace(),
                HRMDefinitions::workspace(),
                AssetDefinitions::workspace(),
                ProjectDefinitions::workspace(),
                POSDefinitions::workspace(),
                ManufacturingDefinitions::workspace(),
                WarehouseDefinitions::workspace(),
                DocumentDefinitions::workspace(),
                AIDefinitions::workspace(),
                IntegrationDefinitions::workspace(),
                PlatformDefinitions::workspace(),
                PortalDefinitions::customerPortal(),
                PortalDefinitions::technicianPortal(),
                NotificationDefinitions::workspace(),
                WorkflowDefinitions::workspace(),
                GRCDefinitions::workspace(),
                EPOCDefinitions::workspace(),
            ]);

            return $registry;
        });

        // Sprint 13.0 — Automation Engine (singleton registry)
        $this->app->singleton(AutomationRegistry::class, function () {
            $registry = new AutomationRegistry();
            $registry->registerAll(array_merge(
                ServiceAutomations::all(),
                InventoryDefinitions::automations(),
                PurchasingDefinitions::automations(),
                CRMDefinitions::automations(),
                FinanceDefinitions::automations(),
                HRMDefinitions::automations(),
                AssetDefinitions::automations(),
                ProjectDefinitions::automations(),
                POSDefinitions::automations(),
                ManufacturingDefinitions::automations(),
                WarehouseDefinitions::automations(),
                DocumentDefinitions::automations(),
                AIDefinitions::automations(),
                IntegrationDefinitions::automations(),
                PlatformDefinitions::automations(),
                PortalDefinitions::automations(),
                NotificationDefinitions::automations(),
                GRCDefinitions::automations(),
                EPOCDefinitions::automations(),
                WorkflowDefinitions::automations(),
            ));
            return $registry;
        });

        // Sprint 14.0 — Reporting Engine (singleton registry)
        $this->app->singleton(ReportRegistry::class, function () {
            $registry = new ReportRegistry();
            $registry->registerAll(array_merge(
                ServiceReports::all(),
                InventoryDefinitions::reports(),
                PurchasingDefinitions::reports(),
                CRMDefinitions::reports(),
                FinanceDefinitions::reports(),
                HRMDefinitions::reports(),
                AssetDefinitions::reports(),
                ProjectDefinitions::reports(),
                POSDefinitions::reports(),
                ManufacturingDefinitions::reports(),
                WarehouseDefinitions::reports(),
                DocumentDefinitions::reports(),
                AIDefinitions::reports(),
                IntegrationDefinitions::reports(),
                PlatformDefinitions::reports(),
                PortalDefinitions::reports(),
                NotificationDefinitions::reports(),
                GRCDefinitions::reports(),
                EPOCDefinitions::reports(),
                WorkflowDefinitions::reports(),
            ));
            return $registry;
        });
    }

    public function boot(): void
    {
        // BR-FIX-02 — Register TenantUserPolicy for User.
        // AuthServiceProvider is not present in bootstrap/providers.php, so its
        // explicit $policies map is never applied. Laravel auto-discovery maps
        // most tenant models to App\Policies\{Model}Policy, but the User model
        // needs TenantUserPolicy (no UserPolicy class exists) — without this,
        // user-management authorization (create/update/delete) always 403s.
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\Tenant\User::class,
            \App\Policies\TenantUserPolicy::class
        );

        // BR-FIX-03 — Register DelegationPolicy for the Delegation model so
        // grant/revoke authorization (delegation.grant/delegation.revoke) is
        // enforced through the same Gate mechanism as every other capability.
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\Tenant\Delegation::class,
            \App\Policies\DelegationPolicy::class
        );

        // Force HTTPS for all generated URLs if not accessed via localhost
        $isLocalhost = str_contains(request()->getHost(), 'localhost') || str_contains(request()->getHost(), '127.0.0.1');
        if (!$isLocalhost) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Set root URL
        try {
            if (request()->getHost()) {
                $secure = !$isLocalhost || request()->secure() || request()->header('X-Forwarded-Proto') === 'https';
                $scheme = $secure ? 'https' : 'http';
                $host = request()->getHost();
                $port = request()->getPort();
                $portSuffix = ($port !== 80 && $port !== 443) ? ':' . $port : '';
                \Illuminate\Support\Facades\URL::forceRootUrl("{$scheme}://{$host}{$portSuffix}");
            }
        } catch (\Exception $e) {
            // Fallback
        }

        // Apply mail config from database settings
        try {
            MailConfigService::apply();
        } catch (\Exception $e) {
            // Silently fail
        }

        // ======== SPRINT 7.2D — LARAVEL NATIVE EVENT ARCHITECTURE ========

        // Wildcard: Log ALL events to event_logs (replaces EventBus)
        Event::listen('*', [EventLogger::class, 'handle']);

        // WorkflowStateChanged → persistence (history, timeline, activity)
        Event::listen(WorkflowStateChanged::class, [WorkflowPersistenceSubscriber::class, 'handle']);

        // WorkflowStateChanged → automation rule evaluation
        Event::listen(WorkflowStateChanged::class, [AutomationSubscriber::class, 'handle']);

        // Legacy backward compat: old WorkflowTransitioned event
        Event::listen(WorkflowTransitioned::class, [TriggerAutomationRules::class, 'handle']);
    }
}
