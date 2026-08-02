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
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Sprint 7.2D — Provider Adapter (singleton)
        $this->app->singleton(ProviderAdapter::class);
    }

    public function boot(): void
    {
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
