<?php

namespace App\Actions\Request;

use App\Models\Tenant\Request;
use App\Models\Tenant\RequestHistory;
use App\Models\Tenant\RequestTimeline;
use App\Models\Tenant\WorkOrder;

/**
 * Create a Work Order under a Request.
 * Supports multi-technician, multi-WO per request (BR-018).
 */
class CreateWorkOrderAction
{
    public function execute(Request $request, array $data): WorkOrder
    {
        if ($request->isTerminal()) {
            throw new \RuntimeException('Request is terminal — cannot create work order.');
        }

        $wo = WorkOrder::create([
            'request_id' => $request->id,
            'service_id' => $data['service_id'] ?? null,
            'device_id' => $data['device_id'] ?? null,
            'technician_id' => $data['technician_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? 'hardware',
            'status' => 'pending',
            'estimated_minutes' => $data['estimated_minutes'] ?? null,
            'sort_order' => WorkOrder::where('request_id', $request->id)->count() + 1,
        ]);

        // Record timeline
        RequestTimeline::record(
            $request->id,
            'work_order_created',
            "WO: {$data['title']} — Dibuat",
            $data['description'] ?? null,
            deviceId: $data['device_id'] ?? null,
            woId: $wo->id
        );

        // Transition Request to checking if not already processing
        if ($request->status === 'confirmed') {
            $request->update(['status' => 'checking']);
            RequestHistory::create(['request_id' => $request->id, 'from_status' => 'confirmed', 'to_status' => 'checking', 'actor_id' => auth()->id(), 'note' => 'Mulai checking.']);
        }

        return $wo;
    }
}
