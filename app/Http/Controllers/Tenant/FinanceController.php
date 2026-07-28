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

        return Inertia::render('Keuangan/Index', [
            'activeTab' => $tab,

            'sales' => fn() => Sale::with(['customer', 'items', 'service.customer'])
                ->when($request->status, fn($q) => $q->where('status', $request->status))
                ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                    $q->where('id', 'like', "%{$request->search}%")
                      ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$request->search}%"));
                }))
                ->latest()
                ->paginate(15),

            'salesStats' => fn() => [
                'completed_services' => Service::whereIn('status', [Service::STATUS_SELESAI, Service::STATUS_SIAP_DIAMBIL])
                    ->where(fn($q) => $q->whereNull('payment_status')->orWhere('payment_status', 'pending'))->count(),
                'drafts' => Sale::where('status', Sale::STATUS_DRAFT)->count(),
                'paid' => Sale::where('status', Sale::STATUS_PAID)->count(),
            ],

            'salesFilters' => fn() => $request->only(['status', 'sale_type', 'search', 'date_from', 'date_to']),

            'expenses' => fn() => Expense::with(['branch', 'creator'])
                ->latest()
                ->paginate(15),

            'expenseCategories' => fn() => Expense::CATEGORIES,

            'purchases' => fn() => Purchase::with(['supplier', 'creator', 'items.product'])
                ->latest()
                ->paginate(15),

            'returns' => fn() => PurchaseReturn::with(['purchase', 'supplier', 'items.product', 'creator'])
                ->whereHas('purchase', fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                ->latest()
                ->paginate(20),
        ]);
    }
}
