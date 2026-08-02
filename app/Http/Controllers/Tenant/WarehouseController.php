<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use App\Models\Tenant\InventoryMutation;
use App\Models\Tenant\StockOpname;
use App\Models\Tenant\StockAdjustment;
use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\TechnicianInventory;
use App\Models\Tenant\StockTransfer;
use Illuminate\Http\Request;

/**
 * Warehouse Operations Controller — Sprint 7.4B.
 */
class WarehouseController extends Controller
{
    /** Create stock opname with all products in location */
    public function createOpname(Request $request)
    {
        $data = $request->validate(['location_id' => 'nullable|exists:stock_locations,id']);
        $opname = StockOpname::create(['location_id' => $data['location_id'] ?? null, 'created_by' => auth()->id()]);

        // Auto-populate items with current system quantities
        Product::chunk(100, function ($products) use ($opname) {
            foreach ($products as $p) {
                $opname->items()->create(['product_id' => $p->id, 'system_qty' => $p->stock_quantity]);
            }
        });

        $opname->update(['status' => 'counting']);
        return back()->with('success', 'Stock opname #' . $opname->opname_number . ' dibuat. Silakan input fisik.');
    }

    /** Record physical count result */
    public function recordCount(Request $request, StockOpname $opname)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_qty' => 'required|integer|min:0',
            'items.*.note' => 'nullable|string',
        ]);

        foreach ($data['items'] as $item) {
            $opnameItem = $opname->items()->where('product_id', $item['product_id'])->first();
            if ($opnameItem) {
                $opnameItem->update([
                    'physical_qty' => $item['physical_qty'],
                    'difference' => $item['physical_qty'] - $opnameItem->system_qty,
                    'note' => $item['note'] ?? null,
                ]);
            }
        }

        $opname->update(['status' => 'review']);
        return back()->with('success', 'Hasil perhitungan disimpan.');
    }

    /** Approve opname → create adjustments for differences */
    public function approveOpname(StockOpname $opname)
    {
        foreach ($opname->items as $item) {
            if ($item->difference !== 0 && $item->difference !== null) {
                $type = $item->difference < 0 ? 'missing' : 'found';
                StockAdjustment::record(
                    $item->product,
                    $type,
                    $item->difference,
                    "Opname #{$opname->opname_number}: " . ($item->note ?? 'Penyesuaian stok'),
                    auth()->id()
                );
            }
        }

        $opname->complete(auth()->id());
        return back()->with('success', 'Opname disetujui. Selisih stok disesuaikan.');
    }

    /** Assign serial to service */
    public function assignSerial(Request $request, ProductSerial $serial)
    {
        $data = $request->validate(['service_id' => 'required|exists:services,id']);
        $serial->assignToService($data['service_id']);
        return back()->with('success', 'Serial #' . $serial->serial_number . ' ditetapkan ke service.');
    }

    /** Transfer stock to technician */
    public function transferToTechnician(Request $request)
    {
        $data = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($data['product_id']);
        if ($product->stock_quantity < $data['quantity']) {
            return back()->with('error', 'Stok tidak mencukupi.');
        }

        \DB::transaction(function () use ($data, $product) {
            $before = $product->stock_quantity;
            $product->reduceStock($data['quantity']);

            InventoryMutation::create([
                'product_id' => $product->id, 'type' => 'transfer_out',
                'quantity' => -$data['quantity'], 'before_stock' => $before,
                'after_stock' => $product->fresh()->stock_quantity,
                'note' => "Transfer ke teknisi #{$data['technician_id']}",
                'created_by' => auth()->id(),
            ]);

            TechnicianInventory::updateOrCreate(
                ['technician_id' => $data['technician_id'], 'product_id' => $product->id],
                ['quantity' => \DB::raw("quantity + {$data['quantity']}")]
            );
        });

        event(new \App\Events\Entity\TechnicianStockTransferred($product, $data['technician_id'], $data['quantity']));
        return back()->with('success', 'Stok ditransfer ke teknisi.');
    }

    /** Receive stock transfer */
    public function receiveTransfer(StockTransfer $transfer)
    {
        $transfer->receive(auth()->id());
        return back()->with('success', 'Transfer diterima.');
    }
}
