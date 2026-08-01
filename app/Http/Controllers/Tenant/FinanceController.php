<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Expense;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseReturn;
use App\Models\Tenant\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinanceController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->get('tab', 'penjualan');
        $user = auth()->user();
        $restrictToTodayCompletedTransactions = $this->shouldRestrictToTodayCompletedTransactions($user);
        $today = now()->toDateString();

        $userBranchId = $user->branch_id;

        $salesQuery = Sale::with(['customer', 'items', 'service.customer'])
            ->when($userBranchId, fn($q) => $q->where('branch_id', $userBranchId))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('id', 'like', "%{$request->search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$request->search}%"));
            }));

        if ($restrictToTodayCompletedTransactions) {
            $salesQuery
                ->where('status', Sale::STATUS_PAID)
                ->whereDate('created_at', today());
        } else {
            $salesQuery->when($request->status, fn($q) => $q->where('status', $request->status));
        }

        $salesFilters = $request->only(['status', 'sale_type', 'search', 'date_from', 'date_to']);
        if ($restrictToTodayCompletedTransactions) {
            $salesFilters['status'] = Sale::STATUS_PAID;
            $salesFilters['date_from'] = $today;
            $salesFilters['date_to'] = $today;
        }

        return Inertia::render('Keuangan/Index', [
            'activeTab' => $tab,

            'sales' => fn() => $salesQuery
                ->latest()
                ->paginate(15),

            'salesStats' => fn() => $restrictToTodayCompletedTransactions
                ? [
                    'completed_services' => $this->scopeServiceBranch(Service::query(), $userBranchId)
                        ->whereIn('status', [Service::STATUS_SELESAI, Service::STATUS_SIAP_DIAMBIL])
                        ->whereDate('updated_at', today())
                        ->where(fn($q) => $q->whereNull('payment_status')->orWhere('payment_status', 'pending'))->count(),
                    'drafts' => 0,
                    'paid' => $this->scopeSaleBranch(Sale::query(), $userBranchId)
                        ->where('status', Sale::STATUS_PAID)
                        ->whereDate('created_at', today())
                        ->count(),
                ]
                : [
                    'completed_services' => $this->scopeServiceBranch(Service::query(), $userBranchId)
                        ->whereIn('status', [Service::STATUS_SELESAI, Service::STATUS_SIAP_DIAMBIL])
                        ->where(fn($q) => $q->whereNull('payment_status')->orWhere('payment_status', 'pending'))->count(),
                    'drafts' => $this->scopeSaleBranch(Sale::query(), $userBranchId)->where('status', Sale::STATUS_DRAFT)->count(),
                    'paid' => $this->scopeSaleBranch(Sale::query(), $userBranchId)->where('status', Sale::STATUS_PAID)->count(),
                ],

            'salesFilters' => fn() => $salesFilters,

            'expenses' => fn() => Expense::with(['branch', 'creator'])
                ->when($userBranchId, fn($q) => $q->where('branch_id', $userBranchId))
                ->latest()
                ->paginate(15),

            'expenseCategories' => fn() => Expense::CATEGORIES,

            'purchases' => fn() => Purchase::with(['supplier', 'creator', 'items.product'])
                ->when($userBranchId, fn($q) => $q->where('branch_id', $userBranchId))
                ->latest()
                ->paginate(15),

            'returns' => fn() => PurchaseReturn::with(['purchase', 'supplier', 'items.product', 'creator'])
                ->whereHas('purchase', fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                ->latest()
                ->paginate(20),

            // Untuk drawer Pembelian & Retur Pembelian
            'suppliers' => fn() => \App\Models\Tenant\Supplier::orderBy('name')->get(['id', 'name']),
            'products' => fn() => \App\Models\Tenant\Product::orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function shouldRestrictToTodayCompletedTransactions($user): bool
    {
        if (!$user) {
            return false;
        }

        if (in_array($user->role, ['cs', 'cashier'], true)) {
            return true;
        }

        if ($user->role !== 'custom') {
            return false;
        }

        $customRole = strtolower(trim((string) ($user->custom_role ?? '')));
        $customRole = preg_replace('/[_\-\s]+/', ' ', $customRole);

        return in_array($customRole, ['admin harian'], true);
    }

    private function scopeServiceBranch($query, $userBranchId)
    {
        if ($userBranchId) {
            $query->where('branch_id', $userBranchId);
        }

        return $query;
    }

    private function scopeSaleBranch($query, $userBranchId)
    {
        if ($userBranchId) {
            $query->where('branch_id', $userBranchId);
        }

        return $query;
    }
}
