<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;

/**
 * Canonical event: ANY workflow transition completed.
 * Replaces the old WorkflowTransitioned event.
 *
 * Subscribers: AutomationEngine, TimelineProjector, ActivityProjector,
 *              AuditProjector, NotificationProjector, WebhookProjector
 */
class WorkflowStateChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Model  $entity,
        public readonly string $workflowKey,
        public readonly string $fromState,
        public readonly string $toState,
        public readonly string $action,
        public readonly array  $context = [],
    ) {}
}
