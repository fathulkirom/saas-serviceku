<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Commission;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index()
    {
        return redirect()->route('kas.index')->with('info', 'Komisi sudah dipindah ke Kas.');
    }

    public function pay(Commission $commission)
    {
        $commission->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by' => auth()->id(),
        ]);
        ActivityLog::log('commission', 'Bayar komisi teknisi');
        return back()->with('success', 'Komisi berhasil dibayarkan.');
    }

    public function payBulk(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:commissions,id']);
        Commission::whereIn('id', $request->ids)->where('status', 'pending')->update([
            'status' => 'paid', 'paid_at' => now(), 'paid_by' => auth()->id(),
        ]);
        return back()->with('success', 'Komisi terpilih berhasil dibayarkan.');
    }
}
