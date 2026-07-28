<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class ServiceTransferController extends Controller
{
    public function create()
    {
        return redirect()->route('servis-tools.index')->with('info', 'Transfer servis sudah dipindah ke Servis Tools.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'to_branch_id' => 'required|exists:branches,id',
            'note' => 'nullable|string',
        ]);

        $service = Service::findOrFail($validated['service_id']);

        if ($service->branch_id !== Auth::user()->branch_id) {
            return back()->with('error', 'Anda hanya bisa mentransfer servis dari cabang sendiri.');
        }

        $service->update([
            'branch_id' => $validated['to_branch_id'],
            'status' => 'menunggu_alokasi',
            'technician_id' => null,
            'condition_note' => ($service->condition_note ?? '') . "\n[TRANSFER] " . ($validated['note'] ?? 'Transfer antar cabang'),
        ]);

        return redirect()->route('services.show', $service->id)
            ->with('success', 'Servis berhasil ditransfer ke cabang tujuan.');
    }
}
