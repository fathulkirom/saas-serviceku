<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CustomField;
use Illuminate\Http\Request;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class CustomFieldController extends Controller
{
    public function index()
    {
        return redirect()->route('pengaturan.index')->with('info', 'Custom fields sudah dipindah ke Pengaturan.');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canManageCustomFields()) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola custom fields.');
        }
        $validated = $request->validate([
            'module' => 'required|in:customer,service,device',
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,number,dropdown,date,checkbox',
            'options' => 'nullable|array',
            'is_required' => 'nullable|boolean',
            'ordering' => 'nullable|integer|min:0',
        ]);
        $validated['branch_id'] = auth()->user()->branch_id;
        CustomField::create($validated);
        return back()->with('success', 'Custom field berhasil ditambahkan.');
    }

    public function update(Request $request, CustomField $customField)
    {
        if (!auth()->user()->canManageCustomFields()) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola custom fields.');
        }
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,number,dropdown,date,checkbox',
            'options' => 'nullable|array',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'ordering' => 'nullable|integer|min:0',
        ]);
        $customField->update($validated);
        return back()->with('success', 'Custom field berhasil diupdate.');
    }

    public function destroy(CustomField $customField)
    {
        if (!auth()->user()->canManageCustomFields()) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola custom fields.');
        }
        $customField->values()->delete();
        $customField->delete();
        return back()->with('success', 'Custom field berhasil dihapus.');
    }
}
