<?php

namespace App\Actions\Request;

use App\Models\Tenant\Request;
use App\Models\Tenant\RequestHistory;
use App\Services\SettingsService;

/**
 * Create a new Request (ADR-001 Core Entry Point).
 */
class CreateRequestAction
{
    public function execute(array $data): Request
    {
        // Generate request number
        $data['request_number'] = $this->generateNumber();
        $data['status'] = $data['status'] ?? 'draft';

        $request = Request::create($data);

        // Record creation in history
        RequestHistory::create([
            'request_id' => $request->id,
            'to_status' => $request->status,
            'actor_id' => auth()->id(),
            'note' => 'Request dibuat.',
        ]);

        // Attach devices
        if (!empty($data['devices'])) {
            foreach ($data['devices'] as $device) {
                $request->devices()->attach($device['device_id'], [
                    'issue_description' => $device['issue_description'] ?? null,
                    'condition' => $device['condition'] ?? null,
                    'notes' => $device['notes'] ?? null,
                ]);
            }
        }

        // Dispatch event (if Event architecture is set up)
        // event(new RequestCreated($request));

        return $request->fresh(['devices', 'customer', 'branch']);
    }

    protected function generateNumber(): string
    {
        $tenant = tenant();
        $code = strtoupper(substr($tenant->tenant_name ?? 'SVC', 0, 4));
        $date = now()->format('Ymd');
        $seq = Request::whereDate('created_at', today())->count() + 1;
        return sprintf('REQ-%s-%s-%04d', $code, $date, $seq);
    }
}
