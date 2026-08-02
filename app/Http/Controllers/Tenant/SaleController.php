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
        $this->authorize('create', Sale::class);

        $products = \App\Models\Tenant\Product::where('stock_quantity', '>', 0)
            ->where('branch_id', auth()->user()->branch_id)
            ->take(200)
            ->get();
            
        $customers = \App\Models\Tenant\Customer::orderBy('name')
            ->take(200)
            ->get();

        return \Inertia\Inertia::render('Sales/Create', [
            'products' => $products,
            'customers' => $customers,
        ]);
    }

    public function show(Sale $sale)
    {
        return redirect()->route('keuangan.index', ['tab' => 'penjualan'])
            ->with('info', 'Detail penjualan #' . $sale->id . ' bisa dilihat di Keuangan.');
    }
}
