<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CashRegister;
use App\Models\Tenant\DailyDeposit;
use App\Models\Tenant\Commission;
use App\Models\Tenant\PaymentReconciliation;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class CashController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->get('tab', 'shift');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        return Inertia::render('Kas/Index', [
            'activeTab' => $tab,
            'filters' => ['date_from' => $dateFrom, 'date_to' => $dateTo],

            'registers' => fn() => CashRegister::with(['branch', 'user'])
                ->when($dateFrom, fn($q) => $q->whereDate('opened_at', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->whereDate('opened_at', '<=', $dateTo))
                ->latest()
                ->paginate(15),

            'deposits' => fn() => DailyDeposit::with(['branch', 'creator'])
                ->when($dateFrom, fn($q) => $q->whereDate('deposit_date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->whereDate('deposit_date', '<=', $dateTo))
                ->latest()
                ->paginate(15),

            'commissions' => fn() => Commission::with(['service.customer', 'technician', 'payer'])
                ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
                ->latest()
                ->paginate(20),

            'reconciliations' => fn() => PaymentReconciliation::with(['sale.customer', 'creator'])
                ->where('branch_id', auth()->user()->branch_id)
                ->when($dateFrom, fn($q) => $q->whereDate('reconciliation_date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->whereDate('reconciliation_date', '<=', $dateTo))
                ->latest()
                ->paginate(20),
        ]);
    }
}
