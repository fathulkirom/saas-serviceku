<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PurchaseReturn;
use App\Models\Tenant\PurchaseReturnItem;
use App\Models\Tenant\Product;
use App\Models\Tenant\InventoryMutation;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class PurchaseReturnController extends Controller
{
    public function index()
    {
        return redirect()->route('keuangan.index', ['tab' => 'retur'])->with('info', 'Retur pembelian sudah dipindah ke Keuangan.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'nullable|exists:purchases,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.condition' => 'nullable|string',
        ]);

        $return = PurchaseReturn::create([
            'purchase_id' => $validated['purchase_id'],
            'supplier_id' => $validated['supplier_id'],
            'reason' => $validated['reason'],
            'status' => 'dikirim',
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['items'] as $item) {
            PurchaseReturnItem::create([
                'purchase_return_id' => $return->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'] ?? 0,
                'condition' => $item['condition'] ?? null,
            ]);

            Product::where('id', $item['product_id'])->decrement('stock_quantity', $item['quantity']);
            InventoryMutation::create([
                'branch_id' => auth()->user()->branch_id,
                'product_id' => $item['product_id'],
                'type' => 'keluar',
                'quantity' => $item['quantity'],
                'reference' => 'retur_pembelian',
                'reference_id' => $return->id,
                'description' => 'Retur pembelian #' . $return->return_number,
                'created_by' => auth()->id(),
            ]);
        }

        ActivityLog::log('purchase_return', 'Buat retur pembelian: ' . $return->return_number);
        return redirect()->route('purchase-returns.index')->with('success', 'Retur pembelian berhasil dibuat.');
    }

    public function updateStatus(Request $request, PurchaseReturn $purchaseReturn)
    {
        $validated = $request->validate(['status' => 'required|in:dikirim,diproses_supplier,selesai,ditolak']);
        $purchaseReturn->update(['status' => $validated['status']]);
        ActivityLog::log('purchase_return', 'Update status retur: ' . $purchaseReturn->return_number . ' -> ' . $validated['status']);
        return back()->with('success', 'Status retur berhasil diupdate.');
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        foreach ($purchaseReturn->items as $item) {
            Product::where('id', $item->product_id)->increment('stock_quantity', $item->quantity);
        }
        $purchaseReturn->delete();
        ActivityLog::log('purchase_return', 'Hapus retur pembelian');
        return back()->with('success', 'Retur pembelian berhasil dihapus.');
    }
}
