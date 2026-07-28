<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateInvoicePdf;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Product;
use App\Models\Tenant\MasterData;
use App\Models\Tenant\InventoryMutation;
use App\Models\Tenant\Service;
use App\Models\Tenant\Commission;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalePaymentController extends Controller
{
    public function payDraft(Request $request, Sale $sale)
    {
        if ($sale->status !== Sale::STATUS_DRAFT) return back()->with('error', 'Penjualan ini bukan draft.');

        $validated = $request->validate([
            'payment_method_id' => 'nullable|exists:master_data,id',
            'payment_method' => 'nullable|string',
            'paid_amount' => 'required|numeric|min:0',
            'warranty_days' => 'nullable|integer|min:0',
        ]);

        $user = Auth::user();
        $paidAmount = $validated['paid_amount'];
        $paymentMethod = $validated['payment_method'] ?? 'cash';
        if (!empty($validated['payment_method_id'])) {
            $method = MasterData::find($validated['payment_method_id']);
            $paymentMethod = $method ? $method->name : $paymentMethod;
        }

        $sale->update([
            'status' => Sale::STATUS_PAID, 'payment_method' => $paymentMethod,
            'payment_method_id' => $validated['payment_method_id'] ?? null,
            'paid_amount' => $paidAmount, 'change' => max(0, $paidAmount - $sale->total),
        ]);

        foreach ($sale->items as $item) {
            if ($item->product_id && in_array($item->item_type, ['sparepart', 'aksesoris'])) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->reduceStock($item->quantity);
                    InventoryMutation::create([
                        'branch_id' => $user->branch_id, 'product_id' => $product->id,
                        'type' => 'keluar', 'quantity' => $item->quantity,
                        'reference_type' => 'sale', 'reference_id' => (string)$sale->id,
                        'note' => 'Pembayaran draft #' . $sale->id, 'created_by' => $user->id,
                    ]);
                }
            }
        }

        if ($sale->service_id) {
            $paidService = Service::with('spareparts')->find($sale->service_id);
            if ($paidService) {
                $paidService->update([
                    'status' => Service::STATUS_SELESAI,
                    'payment_status' => 'paid',
                    'warranty_days' => $validated['warranty_days'] ?? 0,
                    'warranty_expired_at' => ($validated['warranty_days'] ?? 0) > 0 ? now()->addDays($validated['warranty_days']) : null,
                ]);
                Commission::autoCreateForService($paidService);
            }
        }

        ActivityLog::log('sale_paid', 'Pembayaran draft #' . $sale->id . ' - Rp ' . number_format($sale->total, 0, ',', '.'), $sale);
        GenerateInvoicePdf::dispatch($sale);

        return redirect()->route('sales.show', $sale->id)->with('success', 'Pembayaran berhasil!');
    }

    public function void(Sale $sale)
    {
        if (!$sale->isPaid()) return back()->with('error', 'Hanya penjualan lunas yang bisa di-void.');

        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                if ($item->product_id) {
                    $item->product()->increment('stock_quantity', $item->quantity);
                    InventoryMutation::create([
                        'branch_id' => $sale->branch_id, 'product_id' => $item->product_id,
                        'type' => 'masuk', 'quantity' => $item->quantity,
                        'reference' => 'void_sale', 'reference_id' => $sale->id,
                        'description' => 'Void penjualan #' . $sale->id, 'created_by' => auth()->id(),
                    ]);
                }
            }
            $sale->update(['status' => Sale::STATUS_CANCEL]);
        });

        ActivityLog::log('sale', 'Void penjualan #' . $sale->id);
        return back()->with('success', 'Penjualan berhasil di-void. Stok dikembalikan.');
    }
}
