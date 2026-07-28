<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TaxSetting;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index()
    {
        $tax = TaxSetting::where('branch_id', auth()->user()->branch_id)->first();
        return redirect()->route('pengaturan.index');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'pkp_status' => 'required|in:pkp,non_pkp',
            'ppn_percentage' => 'nullable|numeric|min:0|max:100',
            'npwp' => 'nullable|string|max:30',
            'pkp_number' => 'nullable|string|max:30',
        ]);
        TaxSetting::updateOrCreate(
            ['branch_id' => auth()->user()->branch_id],
            $validated
        );
        return back()->with('success', 'Pengaturan PPN berhasil disimpan.');
    }

    public function calculate(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:0']);
        $tax = TaxSetting::where('branch_id', auth()->user()->branch_id)->first();
        $percentage = $tax->ppn_percentage ?? 11;
        $ppn = $request->amount * ($percentage / 100);
        return response()->json(['ppn' => round($ppn, 2), 'total' => round($request->amount + $ppn, 2), 'percentage' => $percentage]);
    }
}
