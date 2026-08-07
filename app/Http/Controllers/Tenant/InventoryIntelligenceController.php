<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use App\Models\Tenant\InventoryMutation;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\PurchaseOrder;
use App\Models\Tenant\Supplier;
use Illuminate\Http\Request;

/**
 * Inventory Intelligence Controller — Sprint 7.4.
 */
class InventoryIntelligenceController extends Controller
{
    /** Inventory dashboard — READ scoped to own + visible branches (BR-005). */
    public function dashboard()
    {
        // BR-FIX-02 (BR-005): no longer global. The dashboard shows stock the
        // user may READ (own branch + configured branch_visibility branches).
        $visible = \App\Services\BranchAccessService::visibleBranchIds(auth()->user());

        $stats = [
            'total_items' => Product::whereIn('branch_id', $visible)->count(),
            'stock_value' => Product::whereIn('branch_id', $visible)->sum(\DB::raw('stock_quantity * cost_price')),
            'low_stock' => Product::whereIn('branch_id', $visible)->where(fn($q) => $q->where('stock_status', 'low')->orWhereColumn('stock_quantity', '<=', 'min_stock'))->count(),
            'out_of_stock' => Product::whereIn('branch_id', $visible)->where('stock_quantity', 0)->count(),
            'recent_movements' => InventoryMutation::with('product', 'creator')
                ->whereIn('branch_id', $visible)
                ->latest()->take(20)->get(),
        ];

        return inertia('Inventaris/Dashboard', ['stats' => $stats]);
    }

    /** Stock movement history per product */
    public function movements(Product $product)
    {
        $movements = InventoryMutation::with('creator')
            ->where('product_id', $product->id)
            ->latest()
            ->paginate(30);

        return inertia('Inventaris/Movements', [
            'product' => $product,
            'movements' => $movements,
        ]);
    }

    /**
     * Approve part request from technician → RESERVES stock.
     * BR-FIX-01 (BR-009): approval must NOT reduce physical stock.
     */
    public function approvePart(ServiceRequiredPart $part)
    {
        try {
            $part->approve();
            return back()->with('success', 'Part disetujui. Stok di-reservasi (stok fisik tidak berubah).');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Return part to stock.
     * BR-FIX-01 (BR-008): reserved-only parts are released (no stock change);
     * consumed parts restore stock + reversal mutation.
     */
    public function returnPart(ServiceRequiredPart $part)
    {
        try {
            if (in_array($part->status, ServiceRequiredPart::RESERVED_STATES, true)) {
                $part->releaseReservation(auth()->id(), 'Dikembalikan dari inventaris');
                return back()->with('success', 'Reservasi part dilepaskan. Stok fisik tidak berubah.');
            }
            $part->returnToStock(auth()->id(), 'Dikembalikan dari inventaris');
            return back()->with('success', 'Part dikembalikan ke stok.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** Service profit calculation */
    public function serviceProfit(Service $service)
    {
        $revenue = ($service->service_charge ?? 0) + ($service->total_cost ?? 0);
        $partCost = $service->requiredParts()->where('status', 'used')->sum('unit_price');
        $cost = $partCost;

        return response()->json([
            'revenue' => $revenue,
            'part_cost' => $partCost,
            'profit' => $revenue - $cost,
            'margin_pct' => $revenue > 0 ? round((($revenue - $cost) / $revenue) * 100) : 0,
        ]);
    }
}
