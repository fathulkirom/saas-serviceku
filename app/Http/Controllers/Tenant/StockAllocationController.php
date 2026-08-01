<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\StockAllocation;
use App\Models\Tenant\Product;
use App\Models\Tenant\InventoryMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class StockAllocationController extends Controller
{
    public function index()
    {
        return redirect()->route('inventaris.index')->with('info', 'Alokasi stok sudah dipindah ke Inventaris.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $product = Product::findOrFail($validated['product_id']);

        if ($user->branch_id && $product->branch_id && (string) $product->branch_id !== (string) $user->branch_id) {
            throw ValidationException::withMessages([
                'product_id' => 'Produk tidak berada pada cabang aktif Anda.',
            ]);
        }

        // Cek stok cukup
        if ($product->stock_quantity < $validated['quantity']) {
            return back()->with('error', 'Stok tidak mencukupi. Stok saat ini: ' . $product->stock_quantity);
        }

        // Kurangi stok
        $product->reduceStock($validated['quantity']);

        // Catat mutasi keluar
        InventoryMutation::create([
            'branch_id' => $user->branch_id,
            'product_id' => $product->id,
            'type' => 'keluar',
            'quantity' => $validated['quantity'],
            'reference_type' => 'transfer',
            'reference_id' => 'alloc_' . strtotime('now'),
            'note' => 'Transfer stok ke cabang',
            'created_by' => $user->id,
        ]);

        // Buat alokasi
        StockAllocation::create([
            'from_branch_id' => $user->branch_id,
            'to_branch_id' => $validated['to_branch_id'],
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'status' => 'pending',
            'allocated_by' => $user->id,
        ]);

        return redirect()->route('stock-allocations.index')
            ->with('success', 'Transfer stok berhasil dikirim. Menunggu konfirmasi cabang tujuan.');
    }

    public function confirm(StockAllocation $stockAllocation)
    {
        $user = Auth::user();

        if ($user->branch_id && (string) $stockAllocation->to_branch_id !== (string) $user->branch_id) {
            return back()->with('error', 'Transfer ini ditujukan untuk cabang lain.');
        }

        if ($stockAllocation->status !== 'pending') {
            return back()->with('error', 'Transfer sudah diproses sebelumnya.');
        }

        // Terima
        $stockAllocation->update([
            'status' => 'diterima',
            'confirmed_by' => $user->id,
        ]);

        // Tambah stok di cabang tujuan
        $product = Product::find($stockAllocation->product_id);
        if ($product) {
            $product->increaseStock($stockAllocation->quantity);

            InventoryMutation::create([
                'branch_id' => $stockAllocation->to_branch_id,
                'product_id' => $product->id,
                'type' => 'masuk',
                'quantity' => $stockAllocation->quantity,
                'reference_type' => 'transfer',
                'reference_id' => $stockAllocation->id,
                'note' => 'Penerimaan transfer stok dari cabang',
                'created_by' => $user->id,
            ]);
        }

        return back()->with('success', 'Transfer stok diterima.');
    }

    public function reject(StockAllocation $stockAllocation)
    {
        $user = Auth::user();

        if ($user->branch_id && (string) $stockAllocation->to_branch_id !== (string) $user->branch_id) {
            return back()->with('error', 'Transfer ini ditujukan untuk cabang lain.');
        }

        if ($stockAllocation->status !== 'pending') {
            return back()->with('error', 'Transfer sudah diproses sebelumnya.');
        }

        $stockAllocation->update([
            'status' => 'ditolak',
            'confirmed_by' => $user->id,
        ]);

        // Kembalikan stok ke cabang asal
        $product = Product::find($stockAllocation->product_id);
        if ($product) {
            $product->increaseStock($stockAllocation->quantity);
        }

        return back()->with('success', 'Transfer stok ditolak.');
    }
}
