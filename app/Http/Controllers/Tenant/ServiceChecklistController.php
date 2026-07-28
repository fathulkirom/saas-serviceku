<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;

class ServiceChecklistController extends Controller
{
    public function saveChecklist(Request $request, Service $service)
    {
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
}
