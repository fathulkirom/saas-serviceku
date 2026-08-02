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
    /** Inventory dashboard */
    public function dashboard()
    {
        $stats = [
            'total_items' => Product::count(),
            'stock_value' => Product::sum(\DB::raw('stock_quantity * cost_price')),
            'low_stock' => Product::where('stock_status', 'low')->orWhereColumn('stock_quantity', '<=', 'min_stock')->count(),
            'out_of_stock' => Product::where('stock_quantity', 0)->count(),
            'recent_movements' => InventoryMutation::with('product', 'creator')->latest()->take(20)->get(),
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

    /** Approve part request from technician */
    public function approvePart(ServiceRequiredPart $part)
    {
        $product = Product::find($part->product_id);
        if (!$product || $product->stock_quantity < $part->qty) {
            return back()->with('error', 'Stok tidak mencukupi.');
        }

        $before = $product->stock_quantity;
        $part->reserve();
        $product->reduceStock($part->qty);

        InventoryMutation::create([
            'product_id' => $product->id,
            'type' => 'service_usage',
            'quantity' => -$part->qty,
            'before_stock' => $before,
            'after_stock' => $product->fresh()->stock_quantity,
            'unit_cost' => $product->cost_price,
            'reference_type' => 'service_required_part',
            'reference_id' => $part->id,
            'note' => "Service #{$part->service_id}",
            'created_by' => auth()->id(),
        ]);

        event(new \App\Events\Entity\StockUsed($product, $part->qty));
        return back()->with('success', 'Part disetujui. Stok berkurang.');
    }

    /** Return unused part to stock */
    public function returnPart(ServiceRequiredPart $part)
    {
        $product = Product::find($part->product_id);
        $before = $product->stock_quantity;
        $part->return();
        $product->increaseStock($part->qty);

        InventoryMutation::create([
            'product_id' => $product->id,
            'type' => 'return',
            'quantity' => $part->qty,
            'before_stock' => $before,
            'after_stock' => $product->fresh()->stock_quantity,
            'reference_type' => 'service_required_part',
            'reference_id' => $part->id,
            'note' => "Return from Service #{$part->service_id}",
            'created_by' => auth()->id(),
        ]);

        event(new \App\Events\Entity\StockReturned($product, $part->qty));
        return back()->with('success', 'Part dikembalikan ke stok.');
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
