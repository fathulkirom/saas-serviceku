<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceRequiredPart;
use Illuminate\Http\Request;

/**
 * Operational Dashboard Controller — Sprint 7.4A.
 * Provides data for Warehouse, CS, and Owner dashboards.
 */
class OperationalDashboardController extends Controller
{
    /** Warehouse dashboard data */
    public function warehouse()
    {
        return inertia('Inventaris/Operational', [
            'stats' => [
                'waiting_requests' => ServiceRequiredPart::where('status', 'requested')->count(),
                'reserved_stock' => ServiceRequiredPart::where('status', 'approved')->sum('qty'),
                'low_stock' => Product::where('stock_status', 'low')->orWhereColumn('stock_quantity', '<=', 'min_stock')->count(),
                'waiting_purchase' => ServiceRequiredPart::where('supplier_status', 'waiting_purchase')->count(),
                'pending_return' => \App\Models\Tenant\ServicePartReturn::where('status', 'requested')->count(),
                'today_incoming' => \App\Models\Tenant\InventoryMutation::where('type', 'purchase')->whereDate('created_at', today())->sum('quantity'),
                'top_parts' => ServiceRequiredPart::where('status', 'used')
                    ->selectRaw('part_name, COUNT(*) as cnt')->groupBy('part_name')->orderByDesc('cnt')->take(10)->get(),
            ],
            'waitingRequests' => ServiceRequiredPart::with('service.customer')->where('status', 'requested')->byPriority()->take(20)->get(),
        ]);
    }

    /** CS dashboard data */
    public function cs()
    {
        return response()->json([
            'draft_services' => Service::where('invoice_status', 'draft')->count(),
            'draft_invoices' => Service::where('invoice_status', 'draft')->count(),
            'waiting_approval' => Service::where('approval_status', 'pending')->count(),
            'ready_pickup' => Service::where('status', 'siap_diambil')->count(),
            'outstanding_parts' => ServiceRequiredPart::whereIn('status', ['requested', 'approved'])->count(),
            'reserved_stock' => ServiceRequiredPart::where('status', 'approved')->sum('qty'),
        ]);
    }

    /** Owner KPI dashboard data */
    public function owner()
    {
        $totalStockValue = Product::sum(\DB::raw('stock_quantity * cost_price'));
        $usedParts = ServiceRequiredPart::where('status', 'used');

        return response()->json([
            'inventory_value' => $totalStockValue,
            'dead_stock' => Product::where('stock_quantity', '>', 0)->whereDoesntHave('mutations', fn($q) => $q->where('created_at', '>', now()->subDays(90)))->count(),
            'fast_moving' => ServiceRequiredPart::where('status', 'used')->where('used_at', '>', now()->subDays(30))->selectRaw('part_name, COUNT(*) as cnt')->groupBy('part_name')->orderByDesc('cnt')->take(5)->get(),
            'gross_profit' => $usedParts->sum(\DB::raw('subtotal - (unit_price * qty)')),
            'most_used' => $usedParts->selectRaw('part_name, SUM(qty) as total')->groupBy('part_name')->orderByDesc('total')->take(5)->get(),
            'outstanding_purchase' => ServiceRequiredPart::where('supplier_status', 'waiting_purchase')->count(),
            'active_warranties' => \App\Models\Tenant\ServiceWarranty::active()->count(),
        ]);
    }

    /** Edit part request (tech only, before used) */
    public function editRequest(Request $request, ServiceRequiredPart $part)
    {
        $data = $request->validate([
            'qty' => 'nullable|integer|min:1',
            'part_name' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $part->edit($data);
            return back()->with('success', 'Request diperbarui.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** Set priority on request */
    public function setPriority(Request $request, ServiceRequiredPart $part)
    {
        $data = $request->validate(['priority' => 'required|in:normal,urgent,vip,warranty']);
        $part->setPriority($data['priority']);
        return back()->with('success', 'Priority diubah.');
    }
}
