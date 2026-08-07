<?php

namespace App\Listeners;

use App\Events\WorkflowTransitioned;
use App\Events\Entity\DiagnosisCompleted;
use App\Events\Entity\QuotationCreated;
use App\Events\Entity\CustomerApprovedRepair;
use App\Events\Entity\QuotationRejected;
use App\Services\AutomationEngine;

/**
 * Listens to workflow transitions AND entity events, triggers automation rule evaluation.
 * Sprint v1.0: Extended to handle Diagnosis, Quotation, and Approval events.
 */
class TriggerAutomationRules
{
    public function handle(WorkflowTransitioned $event): void
    {
        $this->evaluateWorkflowTransition($event);
    }

    /** Sprint v1.0: Handle DiagnosisCompleted event */
    public function handleDiagnosisCompleted(DiagnosisCompleted $event): void
    {
        $engine = app(AutomationEngine::class);
        $engine->evaluate('service.diagnosis_completed', $event->diagnosis, 'service', [
            'diagnosis_id' => $event->diagnosis->id,
            'service_id' => $event->diagnosis->service_id,
        ]);
    }

    /** Sprint v1.0: Handle QuotationCreated event */
    public function handleQuotationCreated(QuotationCreated $event): void
    {
        $engine = app(AutomationEngine::class);
        $engine->evaluate('service.quotation_created', $event->quotation, 'service', [
            'quotation_id' => $event->quotation->id,
            'service_id' => $event->quotation->service_id,
            'total_cost' => $event->quotation->total_cost,
        ]);
    }

    /** Sprint v1.0: Handle CustomerApprovedRepair event */
    public function handleCustomerApproved(CustomerApprovedRepair $event): void
    {
        $engine = app(AutomationEngine::class);
        $engine->evaluate('service.approval_completed', $event->quotation, 'service', [
            'quotation_id' => $event->quotation->id,
            'service_id' => $event->quotation->service_id,
            'status' => $event->quotation->status,
        ]);
    }

    /** Sprint v2.0D: Handle QuotationRejected event */
    public function handleQuotationRejected(QuotationRejected $event): void
    {
        $engine = app(AutomationEngine::class);
        $engine->evaluate('service.quotation_rejected', $event->quotation, 'service', [
            'quotation_id' => $event->quotation->id,
            'service_id' => $event->quotation->service_id,
            'reason' => $event->reason,
        ]);
    }

    private function evaluateWorkflowTransition(WorkflowTransitioned $event): void
    {
        $engine = app(AutomationEngine::class);

        $entityClass = class_basename($event->entity);
        $events = [
            'workflow.transitioned',
            "{$event->workflowKey}.transitioned",
            "{$event->workflowKey}.status_changed",
            "{$event->workflowKey}.{$event->fromState}_to_{$event->toState}",
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
