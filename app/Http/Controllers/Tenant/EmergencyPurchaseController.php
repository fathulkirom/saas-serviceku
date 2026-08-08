<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\EmergencyPurchase;
use App\Models\Tenant\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * BR-010: Emergency Purchase Controller.
 *
 * Pembelian sparepart darurat saat distributor kosong.
 * Dibayar dari kas toko, part langsung masuk stok, tercatat pengeluaran.
 */
class EmergencyPurchaseController extends Controller
{
    public function index()
    {
        $purchases = EmergencyPurchase::with(['user', 'branch', 'product'])
            ->where('branch_id', auth()->user()->branch_id)
            ->latest()->paginate(20);

        $todayTotal = EmergencyPurchase::where('branch_id', auth()->user()->branch_id)
            ->whereDate('created_at', today())->sum('total');

        $products = Product::where('branch_id', auth()->user()->branch_id)
            ->orderBy('name')->get(['id', 'name', 'stock_quantity']);

        return inertia('Inventaris/EmergencyPurchases', [
            'purchases' => $purchases,
            'todayTotal' => $todayTotal,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name'  => 'required|string|max:255',
            'quantity'      => 'required|integer|min:1',
            'cost_price'    => 'required|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
            'reason'        => 'nullable|string|max:500',
            'paid_from_cash'=> 'required|boolean',
            'product_id'    => 'nullable|exists:products,id',
            'notes'         => 'nullable|string|max:500',
        ]);

        $total = $validated['quantity'] * $validated['cost_price'];

        DB::beginTransaction();
        try {
            // 1. Create purchase record
            $purchase = EmergencyPurchase::create([
                'branch_id'      => auth()->user()->branch_id,
                'user_id'        => auth()->id(),
                'product_name'   => $validated['product_name'],
                'quantity'       => $validated['quantity'],
                'cost_price'     => $validated['cost_price'],
                'total'          => $total,
                'supplier_name'  => $validated['supplier_name'] ?? null,
                'reason'         => $validated['reason'] ?? null,
                'paid_from_cash' => $validated['paid_from_cash'],
                'product_id'     => $validated['product_id'] ?? null,
                'notes'          => $validated['notes'] ?? null,
                'status'         => 'completed',
            ]);

            // 2. Auto-increment stock if linked to existing product
            if ($validated['product_id']) {
                $product = Product::findOrFail($validated['product_id']);
                $product->increaseStock($validated['quantity']);
            }

            // 3. Record as expense if paid from cash
            if ($validated['paid_from_cash']) {
                $expense = DB::table('expenses')->insertGetId([
                    'branch_id'    => auth()->user()->branch_id,
                    'user_id'      => auth()->id(),
                    'amount'       => $total,
                    'category'     => 'emergency_purchase',
                    'description'  => "Pembelian darurat: {$validated['product_name']} x{$validated['quantity']}" . ($validated['supplier_name'] ? " ({$validated['supplier_name']})" : ''),
                    'date'         => now()->toDateString(),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $purchase->update(['expense_id' => $expense]);
            }

            DB::commit();
            return back()->with('success', "✅ Pembelian darurat '{$validated['product_name']}' berhasil — stok +{$validated['quantity']}, tercatat pengeluaran Rp " . number_format($total, 0, ',', '.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }
}
