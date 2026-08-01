<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ServiceChecklistController extends Controller
{
    public function saveChecklist(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);

        $validated = $request->validate([
            'checklist_template_id' => 'required|exists:checklist_templates,id',
            'type' => 'required|in:masuk,keluar',
            'checked_items' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $checklist = $service->checklists()->where('type', $validated['type'])->first();

        if ($checklist) {
            $checklist->update(['checklist_template_id' => $validated['checklist_template_id'], 'checked_items' => $validated['checked_items'] ?? [], 'notes' => $validated['notes'] ?? '']);
        } else {
            $service->checklists()->create(['checklist_template_id' => $validated['checklist_template_id'], 'type' => $validated['type'], 'checked_items' => $validated['checked_items'] ?? [], 'notes' => $validated['notes'] ?? '']);
        }

        ActivityLog::log('checklist_' . $validated['type'], 'Checklist ' . $validated['type'] . ' servis #' . $service->id, $service);
        return back()->with('success', 'Checklist ' . $validated['type'] . ' berhasil disimpan.');
    }

    private function ensureServiceBranchAccess(Service $service, $userBranchId): void
    {
        if (!$userBranchId || !$service->branch_id) {
            return;
        }

        if ((string) $service->branch_id !== (string) $userBranchId) {
            throw ValidationException::withMessages([
                'service' => 'Servis tidak berada pada cabang aktif Anda.',
            ]);
        }
    }
}
