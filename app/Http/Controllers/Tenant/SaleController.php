<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Sale;
use Illuminate\Http\Request;

/** @deprecated Use consolidated controller instead. See FinanceController. */
class SaleController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('keuangan.index', ['tab' => 'penjualan'])
            ->with('info', 'Halaman penjualan sudah dipindah ke Keuangan.');
    }

    public function create(Request $request)
    {
        return redirect()->route('keuangan.index', ['tab' => 'penjualan'])
            ->with('info', 'Buat penjualan melalui menu Keuangan.');
    }

    public function show(Sale $sale)
    {
        return redirect()->route('keuangan.index', ['tab' => 'penjualan'])
            ->with('info', 'Detail penjualan #' . $sale->id . ' bisa dilihat di Keuangan.');
    }
}
