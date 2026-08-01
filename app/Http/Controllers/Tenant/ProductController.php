<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use App\Models\Tenant\InventoryMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class ProductController extends Controller
{
    public function index()
    {
        return redirect()->route('inventaris.index')->with('info', 'Manajemen produk sudah dipindah ke Inventaris.');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Product::class);
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:products,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:20',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;

        $product = Product::create($validated);

        // Catat mutasi stok awal
        if ($product->stock_quantity > 0) {
            InventoryMutation::create([
                'branch_id' => $product->branch_id,
                'product_id' => $product->id,
                'type' => 'masuk',
                'quantity' => $product->stock_quantity,
                'reference_type' => 'purchase_order',
                'note' => 'Stok awal',
                'created_by' => Auth::id(),
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:products,code,' . $product->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:20',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ]);

        $product->update($validated);

        return back()->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Quick edit stok (langsung dari halaman index).
     * Owner atau staf yang diberi akses bisa mengubah stok langsung.
     */
    public function quickStock(Request $request, Product $product)
    {
        $this->authorize('quickStock', $product);
        $user = auth()->user();

        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $oldStock = $product->stock_quantity;
        $newStock = $validated['stock_quantity'];
        $difference = $newStock - $oldStock;

        if ($difference !== 0) {
            $product->update(['stock_quantity' => $newStock]);

            \App\Models\Tenant\InventoryMutation::create([
                'branch_id' => $product->branch_id,
                'product_id' => $product->id,
                'type' => $difference > 0 ? 'in' : 'out',
                'quantity' => abs($difference),
                'reference_type' => 'adjustment',
                'reference_id' => 'quick_' . time(),
                'note' => 'Quick Edit by ' . $user->name,
                'created_by' => $user->id,
            ]);
        }

        return back()->with('success', 'Stok berhasil diperbarui.');
    }
}
