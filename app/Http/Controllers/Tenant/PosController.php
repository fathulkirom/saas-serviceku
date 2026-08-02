<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Sale;
use App\Models\Tenant\CashierShift;
use App\Models\Tenant\SaleReturn;
use App\Models\Tenant\SaleSerial;
use App\Models\Tenant\Product;
use Illuminate\Http\Request;

/**
 * POS & Retail Controller — Sprint 7.5.
 */
class PosController extends Controller
{
    // ======== CASHIER SHIFT ========
    public function openShift(Request $request)
    {
        $data = $request->validate(['opening_balance' => 'required|numeric|min:0']);
        $shift = CashierShift::create(['user_id' => auth()->id(), 'branch_id' => auth()->user()->branch_id, 'opening_balance' => $data['opening_balance']]);
        event(new \App\Events\Entity\ShiftOpened($shift));
        return back()->with('success', 'Shift dibuka.');
    }

    public function closeShift(Request $request, CashierShift $shift)
    {
        $data = $request->validate(['expected_cash' => 'required|numeric|min:0', 'actual_cash' => 'required|numeric|min:0', 'notes' => 'nullable|string']);
        $shift->close($data['expected_cash'], $data['actual_cash'], $data['notes'] ?? null);
        return back()->with('success', 'Shift ditutup. Selisih: Rp ' . number_format($shift->difference, 0, ',', '.'));
    }

    // ======== PAYMENT ========
    public function pay(Request $request, Sale $sale)
    {
        $this->authorize('update', $sale);

        $data = $request->validate([
            'payment_details' => 'required|array',
            'payment_details.*.method' => 'required|in:cash,transfer,qris,debit,credit',
            'payment_details.*.amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric',
        ]);

        $totalPaid = collect($data['payment_details'])->sum('amount');
        $change = max(0, $totalPaid - $sale->total);
        $remainingBalance = max(0, $sale->total - $totalPaid);

        $sale->update([
            'payment_details' => $data['payment_details'],
            'paid_amount' => $totalPaid,
            'change' => $change,
            'remaining_balance' => $remainingBalance,
            'status' => $remainingBalance > 0 ? Sale::STATUS_DRAFT : Sale::STATUS_PAID,
        ]);

        if ($sale->status === Sale::STATUS_PAID) {
            // Reduce stock on paid
            foreach ($sale->items as $item) {
                $product = $item->product;
                $before = $product->stock_quantity;
                $product->reduceStock($item->quantity);
                \App\Models\Tenant\InventoryMutation::create([
                    'product_id' => $product->id, 'type' => 'sale', 'quantity' => -$item->quantity,
                    'before_stock' => $before, 'after_stock' => $product->fresh()->stock_quantity,
                    'reference_type' => 'sale', 'reference_id' => $sale->id,
                    'note' => "POS Sale #{$sale->id}", 'created_by' => auth()->id(),
                ]);
            }
            event(new \App\Events\Entity\SalePaid($sale));
        }

        return back()->with('success', $remainingBalance > 0 ? 'Pembayaran sebagian. Sisa: Rp ' . number_format($remainingBalance) : 'Pembayaran lunas.');
    }

    // ======== RETURN ========
    public function requestReturn(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'type' => 'required|in:return,exchange,refund',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
            'reason' => 'required|string',
        ]);

        $return = SaleReturn::create(['sale_id' => $sale->id, 'type' => $data['type'], 'amount' => collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']), 'reason' => $data['reason'], 'created_by' => auth()->id()]);

        foreach ($data['items'] as $item) {
            $return->items()->create($item);
        }

        return back()->with('success', 'Return requested.');
    }

    public function approveReturn(SaleReturn $return)
    {
        $return->complete(auth()->id());
        return back()->with('success', 'Return disetujui. Stok dikembalikan.');
    }

    // ======== SERIAL ========
    public function recordSerial(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'serials' => 'required|array',
            'serials.*.product_id' => 'required|exists:products,id',
            'serials.*.serial_number' => 'required|string',
            'serials.*.serial_type' => 'required|in:imei,sn,warranty',
        ]);
        foreach ($data['serials'] as $s) {
            SaleSerial::create(['sale_id' => $sale->id, 'product_id' => $s['product_id'], 'serial_number' => $s['serial_number'], 'serial_type' => $s['serial_type']]);
            event(new \App\Events\Entity\SerialSold($s));
        }
        return back()->with('success', 'Serial tercatat.');
    }

    // ======== DASHBOARD ========
    public function cashierDashboard()
    {
        $shift = CashierShift::where('user_id', auth()->id())->where('status', 'open')->first();
        return response()->json([
            'current_shift' => $shift,
            'today_sales' => Sale::where('cashier_shift_id', $shift?->id)->where('status', Sale::STATUS_PAID)->sum('total'),
            'pending' => Sale::where('status', Sale::STATUS_DRAFT)->count(),
            'today_count' => Sale::where('cashier_shift_id', $shift?->id)->count(),
        ]);
    }
}
