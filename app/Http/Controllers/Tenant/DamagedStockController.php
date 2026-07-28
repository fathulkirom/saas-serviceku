<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\DamagedStock;
use App\Models\Tenant\Product;
use App\Models\Tenant\InventoryMutation;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class DamagedStockController extends Controller
{
    public function index()
    {
        return redirect()->route('inventaris.index')->with('info', 'Stok rusak sudah dipindah ke Inventaris.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:rusak,reject,expired',
            'source' => 'required|in:pembelian,retur_customer,opname',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['branch_id'] = auth()->user()->branch_id;

        Product::where('id', $validated['product_id'])->decrement('stock_quantity', $validated['quantity']);

        DamagedStock::create($validated);

        InventoryMutation::create([
            'branch_id' => auth()->user()->branch_id,
            'product_id' => $validated['product_id'],
            'type' => 'keluar',
            'quantity' => $validated['quantity'],
            'reference' => 'stock_rusak',
            'description' => 'Stock ' . $validated['type'] . ' - ' . $validated['source'],
            'created_by' => auth()->id(),
        ]);

        ActivityLog::log('damaged_stock', 'Catat stock ' . $validated['type']);
        return back()->with('success', 'Stock ' . $validated['type'] . ' berhasil dicatat.');
    }
}
