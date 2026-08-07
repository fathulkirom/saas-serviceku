<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use App\Models\Tenant\StockAllocation;
use App\Models\Tenant\DamagedStock;
use App\Models\Tenant\InventoryMutation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class InventarisController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->get('tab', 'stok');
        $branchId = auth()->user()->branch_id;

        return Inertia::render('Inventaris/Index', [
            'activeTab' => $tab,

            // BR-FIX-02 (BR-005): product list is READ scoped to own branch +
            // configured visible branches (branch_visibility). Mutation is still
            // limited to the actor's own branch elsewhere (stock transfer/request).
            'products' => fn() => \App\Services\BranchAccessService::stockVisibilityScope(Product::query(), auth()->user())
                ->with('branch')
                ->latest()
                ->paginate(15),

            // Untuk drawer Transfer Stok (cabang tujuan)
            'branches' => fn() => \App\Models\Tenant\Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']),

            'allocations' => fn() => StockAllocation::with(['fromBranch', 'toBranch', 'product', 'allocator', 'confirmer'])
                ->when($branchId, fn($q) => $q->where('from_branch_id', $branchId)->orWhere('to_branch_id', $branchId))
                ->latest()
                ->paginate(15),

            'damagedStocks' => fn() => DamagedStock::with(['product', 'creator'])
                ->where('branch_id', auth()->user()->branch_id)
                ->latest()
                ->paginate(20),

            'mutations' => fn() => $this->getMutations($request),

            'mutationProducts' => fn() => $this->scopeProductBranch(Product::query(), $branchId)->orderBy('name')->get(['id', 'name']),

            'mutationFilters' => fn() => $request->only(['product_id', 'type', 'date_from', 'date_to']),

            'reorderAlerts' => fn() => Product::with('branch')
                ->where('branch_id', auth()->user()->branch_id)
                ->where('stock_quantity', '<=', DB::raw('min_stock'))
                ->orderBy('stock_quantity')
                ->get(),

            'inventoryStats' => fn() => [
                'total_products' => $this->scopeProductBranch(Product::query(), $branchId)->count(),
                'low_stock' => $this->scopeProductBranch(Product::query(), $branchId)->whereColumn('stock_quantity', '<=', 'min_stock')->count(),
                'out_of_stock' => $this->scopeProductBranch(Product::query(), $branchId)->where('stock_quantity', 0)->count(),
                'total_value' => (float) $this->scopeProductBranch(Product::query(), $branchId)->sum(DB::raw('stock_quantity * selling_price')),
                'total_damaged' => (int) DamagedStock::where('branch_id', $branchId)->sum('quantity'),
            ],

            'forecast' => fn() => $this->getForecast(),
        ]);
    }

    private function getMutations(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $query = InventoryMutation::with(['product', 'creator', 'branch'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('mutation_type')) {
            $query->where('type', $request->mutation_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->latest()->paginate(30);
    }

    private function getForecast()
    {
        $branchId = auth()->user()->branch_id;
        return Product::where('branch_id', $branchId)
            ->where('stock_quantity', '>', 0)
            ->get()
            ->map(function ($product) use ($branchId) {
                $sixMonthsAgo = now()->subMonths(6);
                $threeMonthsAgo = now()->subMonths(3);

                $usageRows = InventoryMutation::where('product_id', $product->id)
                    ->where('branch_id', $branchId)
                    ->where('type', 'keluar')
                    ->where('created_at', '>=', $sixMonthsAgo)
                    ->sum('quantity');

                $recentUsage = InventoryMutation::where('product_id', $product->id)
                    ->where('branch_id', $branchId)
                    ->where('type', 'keluar')
                    ->where('created_at', '>=', $threeMonthsAgo)
                    ->sum('quantity');

                $monthlyUsage = $recentUsage > 0 ? $recentUsage / 3 : ($usageRows > 0 ? $usageRows / 6 : 0);
                $daysUntilEmpty = $monthlyUsage > 0 ? round(($product->stock_quantity / $monthlyUsage) * 30) : 999;
                $needsRestock = $product->stock_quantity <= $product->min_stock || $daysUntilEmpty < 30;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock_quantity,
                    'min_stock' => $product->min_stock,
                    'monthly_usage' => round($monthlyUsage, 1),
                    'days_until_empty' => round($daysUntilEmpty),
                    'needs_restock' => $needsRestock,
                ];
            })->sortBy('days_until_empty')->values();
    }

    private function scopeProductBranch($query, $branchId)
    {
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }
}
