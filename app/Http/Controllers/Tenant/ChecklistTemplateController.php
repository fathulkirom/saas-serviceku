<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ChecklistTemplate;
use App\Models\Tenant\ChecklistItem;
use Illuminate\Http\Request;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class ChecklistTemplateController extends Controller
{
    public function index()
    {
        return redirect()->route('servis-tools.index')->with('info', 'Template checklist sudah dipindah ke Servis Tools.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:masuk,keluar',
            'items' => 'nullable|array',
            'items.*.item_name' => 'required|string|max:255',
        ]);

        $template = ChecklistTemplate::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => true,
        ]);

        if ($request->has('items')) {
            foreach ($validated['items'] as $index => $item) {
                ChecklistItem::create([
                    'checklist_template_id' => $template->id,
                    'item_name' => $item['item_name'],
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('checklist-templates.index')
            ->with('success', 'Template ceklis berhasil dibuat.');
    }

    public function update(Request $request, ChecklistTemplate $checklistTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|exists:checklist_items,id',
            'items.*.item_name' => 'required|string|max:255',
        ]);

        $checklistTemplate->update([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? $checklistTemplate->is_active,
        ]);

        // Sync items
        if ($request->has('items')) {
            $existingIds = $checklistTemplate->items()->pluck('id')->toArray();
            $newIds = [];

            foreach ($validated['items'] as $index => $item) {
                if (isset($item['id'])) {
                    ChecklistItem::where('id', $item['id'])->update([
                        'item_name' => $item['item_name'],
                        'sort_order' => $index,
                    ]);
                    $newIds[] = $item['id'];
                } else {
                    $new = ChecklistItem::create([
                        'checklist_template_id' => $checklistTemplate->id,
                        'item_name' => $item['item_name'],
                        'sort_order' => $index,
                    ]);
                    $newIds[] = $new->id;
                }
            }

            // Hapus item yang tidak ada di list baru
            $toDelete = array_diff($existingIds, $newIds);
            ChecklistItem::whereIn('id', $toDelete)->delete();
        }

        return back()->with('success', 'Template ceklis berhasil diperbarui.');
    }

    public function destroy(ChecklistTemplate $checklistTemplate)
    {
        $checklistTemplate->delete();
        return redirect()->route('checklist-templates.index')
            ->with('success', 'Template ceklis berhasil dihapus.');
    }
}
