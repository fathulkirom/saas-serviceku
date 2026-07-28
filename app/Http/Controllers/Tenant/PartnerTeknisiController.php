<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PartnerTeknisi;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class PartnerTeknisiController extends Controller
{
    public function index()
    {
        return redirect()->route('servis-tools.index')->with('info', 'Partner teknisi sudah dipindah ke Servis Tools.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'expertise' => 'nullable|in:hp,laptop,semua',
            'tariff' => 'nullable|numeric|min:0',
        ]);
        $validated['branch_id'] = auth()->user()->branch_id;
        PartnerTeknisi::create($validated);
        ActivityLog::log('partner_teknisi', 'Tambah partner: ' . $validated['name']);
        return back()->with('success', 'Partner teknisi berhasil ditambahkan.');
    }

    public function update(Request $request, PartnerTeknisi $partnerTeknisi)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'expertise' => 'nullable|in:hp,laptop,semua',
            'tariff' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $partnerTeknisi->update($validated);
        ActivityLog::log('partner_teknisi', 'Update partner: ' . $validated['name']);
        return back()->with('success', 'Partner teknisi berhasil diupdate.');
    }

    public function destroy(PartnerTeknisi $partnerTeknisi)
    {
        $partnerTeknisi->delete();
        ActivityLog::log('partner_teknisi', 'Hapus partner');
        return back()->with('success', 'Partner teknisi berhasil dihapus.');
    }
}
