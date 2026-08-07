<?php

namespace App\Enterprise\Automation\Listeners;

use App\Enterprise\Automation\AutomationContext;
use App\Enterprise\Automation\AutomationRunner;
use App\Enterprise\Automation\TriggerType;
use App\Models\Tenant\Service;
use Illuminate\Support\Facades\Log;

/**
 * ServiceAutomationListener
 *
 * Listens to Service model events and dispatches to Automation Engine.
 * Wire this in AppServiceProvider or EventServiceProvider.
 *
 * Events:
 * - Service created (service.created)
 * - Status changed (status.changed)
 * - Service completed (service.finished)
 * - Payment received (payment.success)
 * - Warranty expired (warranty.expired)
 */
class ServiceAutomationListener
{
    public function __construct(
        protected AutomationRunner $runner,
    ) {}

    /**
     * Handle service status change.
     * Fires: status.changed → checks conditions → dispatches actions
     */
    public function handleStatusChanged(Service $service, string $oldStatus, string $newStatus): void
    {
        $context = new AutomationContext(
            triggerEvent: 'status.changed',
            subject: $service,
            original: ['status' => $oldStatus],
            changes: ['status' => $newStatus],
            user: auth()->user(),
            extra: [
                'service_id' => $service->id,
                'tracking_code' => $service->tracking_code,
            ],
        );

        $results = $this->runner->run(TriggerType::STATUS_CHANGED, $context);

        Log::info('[ServiceAutomation] Status changed', [
            'service_id' => $service->id,
            'from' => $oldStatus,
            'to' => $newStatus,
            'automations_triggered' => count($results),
        ]);
    }

    /**
     * Handle service completed.
     * Fires: service.finished → WhatsApp + Timeline + Notification
     */
    public function handleServiceCompleted(Service $service): void
    {
        $context = new AutomationContext(
            triggerEvent: 'service.finished',
            subject: $service,
            changes: ['status' => 'selesai'],
            user: auth()->user(),
            extra: [
                'customer_name' => $service->customer?->name,
                'customer_phone' => $service->customer?->phone,
                'tracking_code' => $service->tracking_code,
            ],
        );

        $results = $this->runner->run(TriggerType::SERVICE_FINISHED, $context);

        Log::info('[ServiceAutomation] Service completed', [
            'service_id' => $service->id,
            'automations_triggered' => count($results),
        ]);
    }

    /**
     * Handle payment success.
     * Fires: payment.success
     */
    public function handlePaymentSuccess(Service $service): void
    {
        $context = new AutomationContext(
            triggerEvent: 'payment.success',
            subject: $service,
            user: auth()->user(),
            extra: ['amount' => $service->total_cost],
        );

        $this->runner->run(TriggerType::PAYMENT_SUCCESS, $context);
    }

    /**
     * Handle warranty near expiry.
     * Can be called from a scheduled command.
     */
    public function handleWarrantyExpiring(Service $service, int $daysRemaining): void
    {
        if ($daysRemaining > 3) {
            return;
        } // Only alert for < 3 days

        $context = new AutomationContext(
            triggerEvent: 'warranty.expiring',
            subject: $service,
            extra: ['days_remaining' => $daysRemaining],
        );

        $this->runner->run(TriggerType::DATE_REACHED, $context);
    }

    /**
     * Handle technician assigned.
     */
    public function handleTechnicianAssigned(Service $service): void
    {
        $context = new AutomationContext(
            triggerEvent: 'technician.assigned',
            subject: $service,
            changes: ['technician_id' => $service->technician_id],
            user: auth()->user(),
            extra: ['technician_name' => $service->technician?->name],
        );

        $this->runner->run(TriggerType::FIELD_CHANGED, $context);
    }
}
