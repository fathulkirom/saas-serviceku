<?php

namespace App\Actions\Request;

use App\Models\Tenant\Request;
use App\Models\Tenant\RequestHistory;
use App\Models\Tenant\RequestTimeline;
use App\Models\Tenant\WorkOrder;

/**
 * Complete a Request — the final step in the business flow.
 * Validates that all Work Orders are done before completing.
 */
class CompleteRequestAction
{
    public function execute(Request $request, array $data = []): Request
    {
        if ($request->isTerminal()) {
            throw new \RuntimeException('Request is already terminal.');
        }

        // Validate: all work orders must be done
        $openWos = $request->workOrders()->whereNotIn('status', ['done', 'cancelled'])->count();
        if ($openWos > 0) {
            throw new \RuntimeException("Masih ada {$openWos} Work Order yang belum selesai.");
        }

        $oldStatus = $request->status;
        $request->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // History
        RequestHistory::create([
            'request_id' => $request->id,
            'from_status' => $oldStatus,
            'to_status' => 'completed',
            'actor_id' => auth()->id(),
            'note' => $data['note'] ?? 'Request selesai.',
        ]);

        // Timeline
        RequestTimeline::record($request->id, 'request_completed', 'Request Selesai', $data['note'] ?? null);

        return $request;
    }
}
