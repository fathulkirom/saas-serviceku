<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired every time a workflow transition completes.
 * The AutomationEngine listens to this to evaluate rules.
 */
class WorkflowTransitioned
{
    use Dispatchable, SerializesModels;

    public $entity;
    public string $workflowKey;
    public string $fromState;
    public string $toState;

    public function __construct($entity, string $workflowKey, string $fromState, string $toState)
    {
        $this->entity = $entity;
        $this->workflowKey = $workflowKey;
        $this->fromState = $fromState;
        $this->toState = $toState;
    }
}
