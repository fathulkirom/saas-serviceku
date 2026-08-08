<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\DailyDeposit;
use Illuminate\Http\Request;

class DailyDepositController extends Controller
{
    public function index()
    {
        return redirect()->route('kas.index')->with('info', 'Setoran harian sudah dipindah ke Kas.');
    }

    public function store(Request $request)
    {
        $this->authorize('create', DailyDeposit::class);
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'deposit_date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;
        $validated['created_by'] = auth()->id();

        DailyDeposit::create($validated);

        return back()->with('success', 'Setoran berhasil dicatat.');
    }
}
