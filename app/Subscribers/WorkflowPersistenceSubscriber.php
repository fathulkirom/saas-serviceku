<?php

namespace App\Subscribers;

use App\Events\WorkflowStateChanged;
use App\Models\Tenant\WorkflowHistory;
use App\Models\Tenant\RequestTimeline;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\Request;

/**
 * Handles ALL persistence side effects of workflow transitions.
 * Subscribes to: WorkflowStateChanged
 *
 * Responsibilities:
 *   ✅ Record workflow_history
 *   ✅ Record request_timeline
 *   ✅ Record activity_logs
 *   ✅ Record request_history (legacy compat)
 */
class WorkflowPersistenceSubscriber
{
    public function handle(WorkflowStateChanged $event): void
    {
        $ctx = $event->context;

        // 1. Workflow History (polymorphic)
        WorkflowHistory::create([
            'entity_type'    => get_class($event->entity),
            'entity_id'      => $event->entity->getKey(),
            'workflow_id'    => $ctx['workflow_id'] ?? null,
            'transition_id'  => $ctx['transition_id'] ?? null,
            'from_state'     => $event->fromState,
            'to_state'       => $event->toState,
            'action'         => $event->action,
            'metadata'       => json_encode($ctx),
            'actor_id'       => auth()->id(),
        ]);

        // 2. Request Timeline (if entity is Request or linked to one)
        $this->recordTimeline($event, $ctx);

        // 3. Activity Log (universal)
        ActivityLog::log(
            'workflow_transitioned',
            "{$event->workflowKey}: {$event->fromState} → {$event->toState} (via {$event->action})",
            $event->entity,
            $ctx,
        );

        // 4. Legacy request_history (backward compat)
        if ($event->entity instanceof Request) {
            \App\Models\Tenant\RequestHistory::create([
                'request_id'  => $event->entity->id,
                'from_status' => $event->fromState,
                'to_status'   => $event->toState,
                'note'        => $event->action,
                'actor_id'    => auth()->id(),
            ]);
        }
    }

    private function recordTimeline(WorkflowStateChanged $event, array $ctx): void
    {
        $requestId = null;

        if ($event->entity instanceof Request) {
            $requestId = $event->entity->id;
        } elseif (method_exists($event->entity, 'request') && $event->entity->request) {
            $requestId = $event->entity->request->id;
        }

        if ($requestId) {
            RequestTimeline::record(
                $requestId,
                'status_changed',
                "{$event->workflowKey}: {$event->fromState} → {$event->toState}",
                $event->action,
                [
                    'from'         => $event->fromState,
                    'to'           => $event->toState,
                    'action'       => $event->action,
                    'workflow'     => $event->workflowKey,
                    'is_auto'      => $ctx['is_auto'] ?? false,
                ],
            );
        }
    }
}
