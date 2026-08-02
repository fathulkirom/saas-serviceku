<?php

namespace App\Listeners;

use App\Events\WorkflowTransitioned;
use App\Services\AutomationEngine;

/**
 * Listens to all workflow transitions and triggers automation rule evaluation.
 * This is the bridge between WorkflowEngine and AutomationEngine.
 */
class TriggerAutomationRules
{
    public function handle(WorkflowTransitioned $event): void
    {
        $engine = app(AutomationEngine::class);

        // Fire multiple event patterns to match rules
        $entityClass = class_basename($event->entity);
        $events = [
            'workflow.transitioned',                                          // Generic
            "{$event->workflowKey}.transitioned",                             // service.transitioned
            "{$event->workflowKey}.status_changed",                           // service.status_changed
            "{$event->workflowKey}.{$event->fromState}_to_{$event->toState}", // service.draft_to_checking
            // Entity-specific events
            strtolower($entityClass) . '.status_changed',
        ];

        $context = [
            'workflow_key' => $event->workflowKey,
            'from_state' => $event->fromState,
            'to_state' => $event->toState,
            'entity_class' => get_class($event->entity),
        ];

        foreach (array_unique($events) as $evt) {
            $engine->evaluate($evt, $event->entity, $event->workflowKey, $context);
        }
    }
}
