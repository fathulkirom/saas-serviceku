<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CashRegister;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashRegisterController extends Controller
{
    /**
     * Daftar shift kasir.
     */
    public function index()
    {
        return redirect()->route('kas.index')->with('info', 'Kas register sudah dipindah ke Kas.');
    }

    /**
     * Buka shift baru.
     */
    public function open(Request $request)
    {
        $this->authorize('open', CashRegister::class);
        $user = Auth::user();

        // Cek apakah masih ada shift open
        $openShift = CashRegister::where('branch_id', $user->branch_id)
            ->where('status', 'open')
            ->first();

        if ($openShift) {
            return back()->with('error', 'Masih ada shift yang terbuka. Tutup shift terlebih dahulu.');
        }

        $validated = $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ]);

        $register = CashRegister::create([
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_balance' => $validated['opening_balance'],
            'status' => 'open',
        ]);

        ActivityLog::log('cash_register_open', 'Buka shift kasir #' . $register->id . ' dengan modal Rp ' . number_format($validated['opening_balance'], 0, ',', '.'));

        return back()->with('success', 'Shift kasir berhasil dibuka.');
    }

    /**
     * Tutup shift (blind close).
     */
    public function close(Request $request)
    {
        $user = Auth::user();

        $openShift = CashRegister::where('branch_id', $user->branch_id)
            ->where('status', 'open')
            ->first();
        if ($openShift) {
            $this->authorize('close', $openShift);
        }

        if (!$openShift) {
            return back()->with('error', 'Tidak ada shift yang terbuka.');
        }

        $validated = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
        ]);

        // Hitung expected balance (modal + penjualan tunai)
        $cashSales = \App\Models\Tenant\Sale::where('branch_id', $user->branch_id)
            ->where('payment_method', 'cash')
            ->where('created_at', '>=', $openShift->opened_at)
            ->sum('total');

        $expectedBalance = $openShift->opening_balance + $cashSales;

        $openShift->update([
            'closed_at' => now(),
            'closing_balance' => $validated['closing_balance'],
            'expected_balance' => $expectedBalance,
            'status' => 'closed',
        ]);

        $difference = $validated['closing_balance'] - $expectedBalance;

        ActivityLog::log('cash_register_close', 'Tutup shift kasir #' . $openShift->id . ' - Selisih: Rp ' . number_format($difference, 0, ',', '.'));

        return back()->with('success', 'Shift kasir berhasil ditutup. Selisih: Rp ' . number_format($difference, 0, ',', '.'));
    }
}
