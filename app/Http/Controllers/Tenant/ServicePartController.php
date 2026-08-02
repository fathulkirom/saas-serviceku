<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\ServicePartReturn;
use Illuminate\Http\Request;

/**
 * Service Part Controller — Sprint 7.4 Revision.
 * Tech requests parts → CS uses them on invoice → stock decreases.
 */
class ServicePartController extends Controller
{
    /** Tech requests a part (no stock impact) */
    public function request(Request $request, Service $service)
    {
        $data = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'part_name' => 'required|string',
            'qty' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $part = ServiceRequiredPart::create([
            'service_id' => $service->id,
            'product_id' => $data['product_id'] ?? null,
            'part_name' => $data['part_name'],
            'qty' => $data['qty'],
            'unit_price' => $data['unit_price'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $part->request(auth()->id());
        return back()->with('success', 'Part requested.');
    }

    /** Tech cancels request (no stock impact, requires reason) */
    public function cancelRequest(Request $request, ServiceRequiredPart $part)
    {
        $data = $request->validate(['reason' => 'required|string']);
        $part->cancel($data['reason']);
        return back()->with('success', 'Request dibatalkan.');
    }

    /** Admin approves request (just confirms need, no stock impact) */
    public function approveRequest(ServiceRequiredPart $part)
    {
        $part->approve();
        return back()->with('success', 'Request disetujui.');
    }

    /** CS adds part to invoice → THIS reduces stock */
    public function usePart(Request $request, ServiceRequiredPart $part)
    {
        $data = $request->validate([
            'selling_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        try {
            $part->use(auth()->id(), $data['selling_price'], $data['discount'] ?? 0);
            return back()->with('success', 'Part digunakan. Stok berkurang.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** Tech/CS requests return of used part */
    public function requestReturn(Request $request, Service $service)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
        ]);

        ServicePartReturn::create([
            'service_id' => $service->id,
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'reason' => $data['reason'],
            'requested_by' => auth()->id(),
        ]);

        return back()->with('success', 'Return requested.');
    }

    /** CS processes return → stock restored */
    public function processReturn(ServicePartReturn $return)
    {
        $product = Product::findOrFail($return->product_id);
        $before = $product->stock_quantity;
        $product->increaseStock($return->quantity);

        \App\Models\Tenant\InventoryMutation::create([
            'product_id' => $product->id,
            'type' => 'return',
            'quantity' => $return->quantity,
            'before_stock' => $before,
            'after_stock' => $product->fresh()->stock_quantity,
            'reference_type' => 'service_part_return',
            'reference_id' => $return->id,
            'note' => "Return Service #{$return->service_id}: {$return->reason}",
            'created_by' => auth()->id(),
        ]);

        $return->update(['status' => 'processed', 'processed_by' => auth()->id()]);
        event(new \App\Events\Entity\PartReturned($return));
        return back()->with('success', 'Return diproses. Stok dikembalikan.');
    }

    /** Service profit calculation */
    public function profit(Service $service)
    {
        $parts = ServiceRequiredPart::where('service_id', $service->id)->where('status', 'used')->get();
        $partRevenue = $parts->sum('subtotal');
        $partCost = $parts->sum(function ($p) {
            return ($p->unit_price ?? $p->product?->cost_price ?? 0) * $p->qty;
        });
        $serviceRevenue = (float) ($service->service_charge ?? 0);
        $totalRevenue = $serviceRevenue + $partRevenue;
        $totalCost = $partCost;
        $profit = $totalRevenue - $totalCost;

        event(new \App\Events\Entity\ServiceProfitCalculated($service, $profit));

        return response()->json([
            'service_revenue' => $serviceRevenue,
            'part_revenue' => $partRevenue,
            'total_revenue' => $totalRevenue,
            'part_cost' => $partCost,
            'profit' => $profit,
            'margin_pct' => $totalRevenue > 0 ? round(($profit / $totalRevenue) * 100) : 0,
        ]);
    }
}
