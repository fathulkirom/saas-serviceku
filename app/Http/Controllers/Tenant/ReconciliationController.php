<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PaymentReconciliation;
use App\Models\Tenant\Sale;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class ReconciliationController extends Controller
{
    public function index()
    {
        $reconciliations = PaymentReconciliation::with(['sale.customer', 'creator'])
            ->where('branch_id', auth()->user()->branch_id)
            ->latest()->paginate(20);
        return redirect()->route('kas.index', ['tab' => 'rekonsiliasi']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'bank_name' => 'nullable|string|max:100',
            'reference_number' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $validated['created_by'] = auth()->id();
        $validated['branch_id'] = auth()->user()->branch_id;
        PaymentReconciliation::create($validated);
        ActivityLog::log('reconciliation', 'Tambah rekonsiliasi untuk sale #' . $validated['sale_id']);
        return back()->with('success', 'Rekonsiliasi berhasil ditambahkan.');
    }

    public function updateStatus(Request $request, PaymentReconciliation $paymentReconciliation)
    {
        $validated = $request->validate([
            'status' => 'required|in:belum_dicocokkan,cocok,tidak_cocok,diinvestigasi',
        ]);
        $paymentReconciliation->update(['status' => $validated['status']]);
        return back()->with('success', 'Status rekonsiliasi berhasil diupdate.');
    }
}
