<?php

namespace App\Actions\Request;

use App\Models\Tenant\Request;
use App\Models\Tenant\RequestHistory;
use App\Models\Tenant\Service;

/**
 * Fork a Request into a Service Order (ADR-001).
 * Each device in the Request becomes one Service Order.
 */
class ForkToServiceOrderAction
{
    public function execute(Request $request, int $deviceId, array $serviceData = []): Service
    {
        // Guard: Request must be in a processable state
        if ($request->isTerminal()) {
            throw new \RuntimeException('Request is terminal — cannot fork.');
        }

        // Get device from pivot
        $pivot = $request->devices()->where('device_id', $deviceId)->first();
        if (!$pivot) {
            throw new \RuntimeException('Device not found in this request.');
        }

        // Create Service Order
        $service = Service::create(array_merge([
            'request_id' => $request->id,
            'customer_id' => $request->customer_id,
            'branch_id' => $request->branch_id,
            'status' => 'menunggu_alokasi',
            'notes' => $pivot->pivot->issue_description,
        ], $serviceData));

        // Transition Request → processing
        if ($request->status !== 'processing') {
            $oldStatus = $request->status;
            $request->update(['status' => 'processing']);

            RequestHistory::create([
                'request_id' => $request->id,
                'from_status' => $oldStatus,
                'to_status' => 'processing',
                'actor_id' => auth()->id(),
                'note' => 'Fork ke Service Order #' . ($service->service_number ?? $service->id),
            ]);
        }

        return $service;
    }
}
