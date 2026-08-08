<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Service;
use App\Models\Tenant\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $month = $request->get('month', now()->format('Y-m'));

        // Per-branch revenue
        $branchRevenue = Sale::selectRaw('branch_id, SUM(total) as revenue, COUNT(*) as transactions')
            ->where('status', Sale::STATUS_PAID)
            ->whereBetween('created_at', [$month.'-01', $month.'-31'])
            ->groupBy('branch_id')->with('branch')->get();

        // Service stats
        $serviceStats = Service::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'cancel' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN status IN ('menunggu_alokasi','dikerjakan') THEN 1 ELSE 0 END) as active
        ")->where('branch_id', $branchId)
          ->whereBetween('created_at', [$month.'-01', $month.'-31'])
          ->first();

        // Top services by revenue
        $topServices = Service::where('branch_id', $branchId)
            ->where('status', 'selesai')
            ->whereBetween('created_at', [$month.'-01', $month.'-31'])
            ->orderByDesc('total_cost')->take(10)->get();

        // Monthly trend (last 6 months)
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i)->format('Y-m');
            $trends[] = [
                'month' => now()->subMonths($i)->format('M Y'),
                'revenue' => Sale::where('branch_id', $branchId)
                    ->where('status', Sale::STATUS_PAID)
                    ->whereBetween('created_at', [$m.'-01', $m.'-31'])->sum('total'),
                'expenses' => Expense::where('branch_id', $branchId)
                    ->whereBetween('date', [$m.'-01', $m.'-31'])->sum('amount'),
                'services' => Service::where('branch_id', $branchId)
                    ->whereBetween('created_at', [$m.'-01', $m.'-31'])->count(),
            ];
        }

        return inertia('Laporan/Analytics', [
            'month'          => $month,
            'branchRevenue'  => $branchRevenue,
            'serviceStats'   => $serviceStats,
            'topServices'    => $topServices,
            'trends'         => $trends,
        ]);
    }
}
