<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Service;
use App\Models\Tenant\Expense;
use App\Models\Tenant\DailyDeposit;
use App\Models\Tenant\Product;
use App\Models\Tenant\InventoryMutation;
use App\Models\Tenant\Commission;
use App\Models\Tenant\User;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $last30Days = collect(range(29, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();
            return [
                'date' => $date,
                'label' => now()->subDays($daysAgo)->format('d M'),
                'sales' => (float) Sale::whereDate('created_at', $date)->sum('total'),
                'services' => Service::whereDate('created_at', $date)->count(),
                'expenses' => (float) Expense::whereDate('expense_date', $date)->sum('amount'),
            ];
        });

        return inertia('Reports/Index', [
            'chartData' => $last30Days,
            'summary' => [
                'total_revenue' => (float) Sale::whereMonth('created_at', now()->month)->sum('total'),
                'total_services' => Service::whereMonth('created_at', now()->month)->count(),
                'total_expenses' => (float) Expense::whereMonth('expense_date', now()->month)->sum('amount'),
            ],
        ]);
    }

    public function sales(Request $request)
    {
        $period = $request->get('period', 'today');
        $dates = $this->getDateRange($period, $request);

        $sales = Sale::with(['customer', 'items'])
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->latest()
            ->get();

        $summary = [
            'total_sales' => $sales->count(),
            'total_revenue' => $sales->sum('total'),
            'total_discount' => $sales->sum('discount'),
            'average_sale' => $sales->avg('total') ?? 0,
            'by_type' => $sales->groupBy('sale_type')->map(function ($items, $type) {
                return [
                    'count' => $items->count(),
                    'total' => $items->sum('total'),
                ];
            }),
        ];

        return inertia('Reports/Sales', [
            'sales' => $sales,
            'summary' => $summary,
            'period' => $period,
            'dateFrom' => $dates['start']->format('Y-m-d'),
            'dateTo' => $dates['end']->format('Y-m-d'),
        ]);
    }

    public function services(Request $request)
    {
        $period = $request->get('period', 'today');
        $dates = $this->getDateRange($period, $request);

        $services = Service::with(['customer', 'technician'])
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->latest()
            ->get();

        $summary = [
            'total' => $services->count(),
            'by_status' => $services->groupBy('status')->map->count(),
            'total_charge' => $services->sum('service_charge'),
            'total_cost' => $services->sum('total_cost'),
        ];

        return inertia('Reports/Services', [
            'services' => $services,
            'summary' => $summary,
            'period' => $period,
            'dateFrom' => $dates['start']->format('Y-m-d'),
            'dateTo' => $dates['end']->format('Y-m-d'),
        ]);
    }

    public function inventory(Request $request)
    {
        $products = Product::with(['branch'])
            ->orderByRaw('stock_quantity <= min_stock DESC')
            ->orderBy('stock_quantity', 'asc')
            ->get();

        $mutations = InventoryMutation::with(['product', 'creator', 'branch'])
            ->latest()
            ->take(100)
            ->get();

        $summary = [
            'total_products' => $products->count(),
            'low_stock' => $products->filter(fn($p) => $p->isLowStock())->count(),
            'out_of_stock' => $products->filter(fn($p) => $p->stock_quantity <= 0)->count(),
            'total_value' => $products->sum(fn($p) => $p->stock_quantity * $p->cost_price),
        ];

        return inertia('Reports/Inventory', [
            'products' => $products,
            'mutations' => $mutations,
            'summary' => $summary,
        ]);
    }

    public function finance(Request $request)
    {
        $period = $request->get('period', 'month');
        $dates = $this->getDateRange($period, $request);

        $sales = Sale::with(['customer', 'items'])->whereBetween('created_at', [$dates['start'], $dates['end']])->get();
        $expenses = Expense::with('creator')->whereBetween('expense_date', [$dates['start'], $dates['end']])->get();
        $deposits = DailyDeposit::with('creator')->whereBetween('deposit_date', [$dates['start'], $dates['end']])->get();

        $summary = [
            'revenue' => $sales->sum('total'),
            'expenses' => $expenses->sum('amount'),
            'profit' => $sales->sum('total') - $expenses->sum('amount'),
            'total_deposits' => $deposits->sum('amount'),
            'sales_count' => $sales->count(),
            'expenses_count' => $expenses->count(),
        ];

        return inertia('Reports/Finance', [
            'summary' => $summary,
            'sales' => $sales,
            'expenses' => $expenses,
            'deposits' => $deposits,
            'period' => $period,
            'dateFrom' => $dates['start']->format('Y-m-d'),
            'dateTo' => $dates['end']->format('Y-m-d'),
        ]);
    }

    /**
     * Laporan komisi teknisi.
     */
    public function commissions(Request $request)
    {
        $period = $request->get('period', 'month');
        $dates = $this->getDateRange($period, $request);

        // Ambil data servis yang selesai dalam periode
        $services = Service::with(['technician', 'customer', 'spareparts'])
            ->where('status', Service::STATUS_SELESAI)
            ->whereBetween('updated_at', [$dates['start'], $dates['end']])
            ->get();

        // Ambil data sale items jasa
        $laborServices = \App\Models\Tenant\MasterLaborService::where('branch_id', auth()->user()->branch_id)
            ->orderBy('name')
            ->get();

        // Hitung komisi per teknisi
        $commissions = $services->groupBy('technician_id')->map(function ($techServices, $techId) {
            $technician = $techServices->first()->technician;
            $totalServiceCharge = $techServices->sum('service_charge');
            $totalSparepartCost = $techServices->sum(function ($s) {
                return $s->spareparts->sum('subtotal');
            });

            return [
                'technician' => $technician ? [
                    'id' => $technician->id,
                    'name' => $technician->name,
                ] : ['id' => null, 'name' => 'Tanpa Teknisi'],
                'total_services' => $techServices->count(),
                'total_service_charge' => $totalServiceCharge,
                'total_sparepart_cost' => $totalSparepartCost,
                'commission_rate' => 0.5, // 50% dari jasa (contoh)
                'estimated_commission' => $totalServiceCharge * 0.5,
                'services' => $techServices->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'customer_name' => $s->customer?->name ?? '-',
                        'completed_at' => $s->updated_at->format('d/m/Y'),
                        'service_charge' => $s->service_charge,
                        'sparepart_cost' => $s->spareparts->sum('subtotal'),
                        'commission' => $s->service_charge * 0.5,
                    ];
                }),
            ];
        });

        $summary = [
            'total_services' => $services->count(),
            'total_commissions' => $commissions->sum('estimated_commission'),
            'total_service_charge' => $services->sum('service_charge'),
            'technician_count' => $commissions->count(),
        ];

        return inertia('Reports/Commissions', [
            'commissions' => $commissions,
            'summary' => $summary,
            'laborServices' => $laborServices,
            'period' => $period,
            'dateFrom' => $dates['start']->format('Y-m-d'),
            'dateTo' => $dates['end']->format('Y-m-d'),
        ]);
    }

    public function productivity(Request $request)
    {
        $period = $request->get('period', 'month');
        $dates = $this->getDateRange($period, $request);

        $technicians = User::where('branch_id', auth()->user()->branch_id)
            ->whereIn('role', ['technician', 'owner', 'admin'])
            ->get()
            ->map(function ($tech) use ($dates) {
                $completed = Service::where('technician_id', $tech->id)
                    ->where('status', Service::STATUS_SELESAI)
                    ->whereBetween('updated_at', [$dates['start'], $dates['end']])
                    ->count();

                $totalServices = Service::where('technician_id', $tech->id)
                    ->whereBetween('created_at', [$dates['start'], $dates['end']])
                    ->count();

                $commissionTotal = Commission::where('technician_id', $tech->id)
                    ->where('status', 'paid')
                    ->whereBetween('paid_at', [$dates['start'], $dates['end']])
                    ->sum('amount');

                $avgDays = Service::where('technician_id', $tech->id)
                    ->where('status', Service::STATUS_SELESAI)
                    ->whereBetween('updated_at', [$dates['start'], $dates['end']])
                    ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(DAY, created_at, updated_at)), 1) as avg_days')
                    ->value('avg_days');

                $level = $completed > 20 ? 'senior' : ($completed > 10 ? 'junior' : 'pemula');

                return [
                    'name' => $tech->name,
                    'role' => $tech->getRoleDisplayName(),
                    'completed' => $completed,
                    'total' => $totalServices,
                    'completion_rate' => $totalServices > 0 ? round(($completed / $totalServices) * 100) : 0,
                    'commission' => $commissionTotal,
                    'avg_days' => round($avgDays ?? 0, 1),
                    'level' => $level,
                    'score' => ($completed * 10) + ($commissionTotal > 0 ? 5 : 0),
                ];
            })->sortByDesc('score')->values();

        return inertia('Reports/Productivity', [
            'technicians' => $technicians,
            'period' => $period,
        ]);
    }

    public function customerAnalytics(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $totalCustomers = Customer::where('branch_id', $branchId)->count();
        $activeCustomers = Customer::whereHas('services', function ($q) {
            $q->whereDate('created_at', '>=', now()->subMonths(3));
        })->where('branch_id', $branchId)->count();

        $peakHours = Service::selectRaw('HOUR(created_at) as hour, COUNT(*) as total')
            ->where('branch_id', $branchId)
            ->whereDate('created_at', '>=', now()->subMonth())
            ->groupBy('hour')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $retentionRate = $totalCustomers > 0 ? round(($activeCustomers / $totalCustomers) * 100) : 0;

        return inertia('Reports/CustomerAnalytics', [
            'totalCustomers' => $totalCustomers,
            'activeCustomers' => $activeCustomers,
            'retentionRate' => $retentionRate,
            'peakHours' => $peakHours,
        ]);
    }

    public function revenueComparison(Request $request)
    {
        $year = $request->get('year', now()->year);

        $monthlyRevenue = Sale::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total) as revenue')
        )
            ->where('status', Sale::STATUS_PAID)
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $previousYear = $year - 1;
        $prevYearRevenue = Sale::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total) as revenue')
        )
            ->where('status', Sale::STATUS_PAID)
            ->whereYear('created_at', $previousYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $current = (float)($monthlyRevenue[$m]->revenue ?? 0);
            $previous = (float)($prevYearRevenue[$m]->revenue ?? 0);
            $growth = $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : 0;
            $months[] = [
                'month' => date('F', mktime(0, 0, 0, $m, 1)),
                'current' => $current,
                'previous' => $previous,
                'growth' => $growth,
            ];
        }

        return inertia('Reports/RevenueComparison', [
            'months' => $months,
            'year' => $year,
            'previousYear' => $previousYear,
        ]);
    }

    public function export(Request $request, string $type)
    {
        $format = $request->get('format', 'csv');
        $period = $request->get('period', 'today');
        $dates = $this->getDateRange($period, $request);

        if ($type === 'sales') {
            $data = Sale::with('customer')->whereBetween('created_at', [$dates['start'], $dates['end']])->latest()->get();
            $filename = "laporan-penjualan-{$period}-" . now()->format('Y-m-d');
            
            if ($format === 'pdf') {
                $pdf = Pdf::loadView('pdfs.sales-report', compact('data', 'period', 'dates'));
                return $pdf->stream("{$filename}.pdf");
            }

            $headers = [
                "Content-type" => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename={$filename}.csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['ID Nota', 'Tanggal', 'Pelanggan', 'No HP', 'Tipe', 'Total (Rp)', 'Status']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        '#' . $row->id,
                        $row->created_at->format('d/m/Y H:i'),
                        $row->customer?->name ?? 'Pelanggan Umum',
                        $row->customer?->phone ?? '-',
                        $row->sale_type,
                        $row->total,
                        $row->payment_status ?? 'paid',
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($type === 'services') {
            $data = Service::with(['customer', 'technician'])->whereBetween('created_at', [$dates['start'], $dates['end']])->latest()->get();
            $filename = "laporan-servis-{$period}-" . now()->format('Y-m-d');

            if ($format === 'pdf') {
                $pdf = Pdf::loadView('pdfs.services-report', compact('data', 'period', 'dates'));
                return $pdf->stream("{$filename}.pdf");
            }

            $headers = [
                "Content-type" => "text/csv; charset=UTF-8",
                "Content-Disposition" => "attachment; filename={$filename}.csv",
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['ID Servis', 'Tanggal Masuk', 'Pelanggan', 'No HP', 'Unit', 'Status', 'Teknisi', 'Biaya (Rp)']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        '#' . $row->id,
                        $row->created_at->format('d/m/Y H:i'),
                        $row->customer?->name ?? '-',
                        $row->customer?->phone ?? '-',
                        $row->tipe_unit ?? '-',
                        $row->getStatusLabel(),
                        $row->technician?->name ?? '-',
                        $row->service_charge ?? 0,
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Tipe laporan tidak didukung.');
    }

    private function getDateRange(string $period, Request $request): array
    {
        $now = now();

        return match ($period) {
            'today' => ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()],
            'yesterday' => ['start' => $now->copy()->subDay()->startOfDay(), 'end' => $now->copy()->subDay()->endOfDay()],
            'week' => ['start' => $now->copy()->startOfWeek(), 'end' => $now->copy()->endOfWeek()],
            'month' => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
            'year' => ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfYear()],
            'custom' => [
                'start' => $request->filled('date_from') ? $request->date_from : $now->copy()->startOfMonth(),
                'end' => $request->filled('date_to') ? $request->date_to : $now->copy()->endOfMonth(),
            ],
            default => ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()],
        };
    }
}
