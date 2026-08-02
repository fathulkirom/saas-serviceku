<?php

namespace App\Subscribers;

use App\Events\WorkflowStateChanged;
use App\Models\Tenant\AutomationRule;
use App\Services\AutomationEngine;

/**
 * Triggers automation rule evaluation when workflow state changes.
 * Subscribes to: WorkflowStateChanged
 *
 * Replaces the old TriggerAutomationRules listener.
 * Uses AutomationEngine which now uses ProviderAdapter internally.
 */
class AutomationSubscriber
{
    public function handle(WorkflowStateChanged $event): void
    {
        $engine = app(AutomationEngine::class);
        $entityClass = class_basename($event->entity);

        // Generate all relevant event patterns
        $eventPatterns = [
            'workflow.state_changed',
            "{$event->workflowKey}.state_changed",
            "{$event->workflowKey}.{$event->fromState}_to_{$event->toState}",
            strtolower($entityClass) . '.state_changed',
        ];

        $context = [
            'workflow_key' => $event->workflowKey,
            'from_state'   => $event->fromState,
            'to_state'     => $event->toState,
            'entity_class' => get_class($event->entity),
        ];

        foreach (array_unique($eventPatterns) as $pattern) {
            $engine->evaluate($pattern, $event->entity, $event->workflowKey, $context);
        }
    }
}
