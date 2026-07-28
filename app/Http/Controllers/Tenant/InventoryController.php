<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\InventoryMutation;
use App\Models\Tenant\Product;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceSparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class InventoryController extends Controller
{
    /**
     * Kartu stok (riwayat mutasi).
     */
    public function mutations(Request $request)
    {
        return redirect()->route('inventaris.index')->with('info', 'Mutasi stok sudah dipindah ke Inventaris.');
    }

    /**
     * Penyesuaian stok manual (stock opname).
     */
    public function adjustment(Request $request)
    {
        $this->authorize('create', InventoryMutation::class);
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $product = Product::findOrFail($validated['product_id']);

        if ($validated['type'] === 'in') {
            $product->increaseStock($validated['quantity']);
        } else {
            if ($product->stock_quantity < $validated['quantity']) {
                return back()->with('error', 'Stok tidak mencukupi. Stok saat ini: ' . $product->stock_quantity);
            }
            $product->reduceStock($validated['quantity']);
        }

        InventoryMutation::create([
            'branch_id' => $user->branch_id,
            'product_id' => $product->id,
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'reference_type' => 'adjustment',
            'reference_id' => 'adj_' . time(),
            'note' => $validated['note'] ?? 'Penyesuaian manual',
            'created_by' => $user->id,
        ]);

        ActivityLog::log('stock_adjustment', 'Penyesuaian stok ' . $product->name . ': ' . ($validated['type'] === 'in' ? '+' : '-') . $validated['quantity'], $product, [
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'note' => $validated['note'] ?? '',
        ]);

        return back()->with('success', 'Stok berhasil disesuaikan.');
    }

    public function reorderAlerts()
    {
        return redirect()->route('inventaris.index')->with('info', 'Reorder alerts sudah dipindah ke Inventaris.');
    }

    public function forecast()
    {
        return redirect()->route('inventaris.index')->with('info', 'Forecast stok sudah dipindah ke Inventaris.');
    }
}
