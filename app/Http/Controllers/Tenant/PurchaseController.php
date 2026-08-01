<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\Supplier;
use App\Models\Tenant\Product;
use App\Models\Tenant\InventoryMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class PurchaseController extends Controller
{
    public function index()
    {
        return redirect()->route('keuangan.index', ['tab' => 'pembelian'])->with('info', 'Halaman pembelian sudah dipindah ke Keuangan.');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Purchase::class);
        $validated = $request->validate([
            'type' => 'required|in:po,cash',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_name' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $supplierId = $validated['supplier_id'];
        if (!$supplierId && !empty($validated['supplier_name'])) {
            $supplier = Supplier::create(['name' => $validated['supplier_name']]);
            $supplierId = $supplier->id;
        }

        $refNumber = 'PO-' . date('Ymd') . '-' . str_pad(Purchase::count() + 1, 4, '0', STR_PAD_LEFT);
        $total = collect($validated['items'])->sum(fn($item) => $item['quantity'] * $item['unit_price']);

        $purchase = Purchase::create([
            'branch_id' => $user->branch_id,
            'reference_number' => $refNumber,
            'type' => $validated['type'],
            'supplier_id' => $supplierId,
            'total' => $total,
            'note' => $validated['note'] ?? '',
            'created_by' => $user->id,
        ]);

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($user->branch_id && $product->branch_id && (string) $product->branch_id !== (string) $user->branch_id) {
                throw ValidationException::withMessages([
                    'items' => 'Produk ' . $product->name . ' bukan milik cabang aktif.',
                ]);
            }

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['quantity'] * $item['unit_price'],
            ]);
            $product->increaseStock($item['quantity']);
            InventoryMutation::create([
                'branch_id' => $user->branch_id,
                'product_id' => $product->id,
                'type' => 'masuk',
                'quantity' => $item['quantity'],
                'reference_type' => 'purchase_order',
                'reference_id' => (string) $purchase->id,
                'note' => 'Pembelian ' . $validated['type'] . ' - ' . $refNumber,
                'created_by' => $user->id,
            ]);
        }

        return redirect()->route('purchases.index')->with('success', 'Pembelian berhasil dicatat.');
    }
}
