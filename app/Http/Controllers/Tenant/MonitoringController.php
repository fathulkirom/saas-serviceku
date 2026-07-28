<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\LoginHistory;
use App\Models\Tenant\SystemAlert;
use App\Models\Tenant\Service;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Product;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function index()
    {
        $recentActivities = ActivityLog::with('user')->latest()->take(50)->get()->map(fn($log) => [
            'id' => $log->id,
            'user' => $log->user?->name ?? 'System',
            'action' => $log->action,
            'description' => $log->description,
            'time' => $log->created_at->diffForHumans(),
            'created_at' => $log->created_at->format('d/m/Y H:i'),
        ]);
        $activeAlerts = SystemAlert::unresolved()->latest()->get();
        $stats = [
            'services_today' => Service::whereDate('created_at', today())->count(),
            'sales_today' => Sale::whereDate('created_at', today())->count(),
            'revenue_today' => Sale::whereDate('created_at', today())->sum('total'),
            'active_users' => User::where('active', true)->count(),
            'low_stock_count' => Product::whereColumn('stock_quantity', '<=', 'min_stock')->count(),
            'out_of_stock' => Product::where('stock_quantity', '<=', 0)->count(),
            'active_services' => Service::active()->count(),
        ];
        $loginsToday = LoginHistory::whereDate('created_at', today())->count();
        $failedLogins = LoginHistory::whereDate('created_at', today())->where('status', 'failed')->count();

        return inertia('Monitoring/Index', compact('recentActivities', 'activeAlerts', 'stats', 'loginsToday', 'failedLogins'));
    }

    public function activities(Request $request)
    {
        $query = ActivityLog::with('user')->latest();
        if ($request->filled('action')) $query->where('action', $request->action);
        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);

        return inertia('Monitoring/Activities', [
            'activities' => $query->paginate(30),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['action', 'user_id', 'date_from', 'date_to']),
        ]);
    }

    public function logins(Request $request)
    {
        $query = LoginHistory::with('user')->latest();
        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('status')) $query->where('status', $request->status);

        return inertia('Monitoring/Logins', [
            'logins' => $query->paginate(30),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['user_id', 'status']),
        ]);
    }

    public function alerts()
    {
        return redirect()->route('monitoring.index')->with('info', 'System alerts sudah dipindah ke Monitoring.');
    }

    public function dismissAlert(SystemAlert $systemAlert)
    {
        $systemAlert->update(['is_read' => true, 'resolved_at' => now()]);
        return back()->with('success', 'Alert ditutup.');
    }

    public function checkLowStock()
    {
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'min_stock')->where('stock_quantity', '>', 0)->get();
        foreach ($lowStockProducts as $product) {
            $existing = SystemAlert::where('type', 'low_stock')->where('context->product_id', $product->id)->whereNull('resolved_at')->first();
            if (!$existing) {
                SystemAlert::createAlert('low_stock', "Stok {$product->name} menipis", "Sisa stok: {$product->stock_quantity}", 'warning', ['product_id' => $product->id]);
            }
        }
        $outOfStock = Product::where('stock_quantity', '<=', 0)->get();
        foreach ($outOfStock as $product) {
            $existing = SystemAlert::where('type', 'low_stock')->where('context->product_id', $product->id)->whereNull('resolved_at')->first();
            if (!$existing) {
                SystemAlert::createAlert('low_stock', "Stok {$product->name} habis!", "Segera lakukan pembelian.", 'danger', ['product_id' => $product->id, 'status' => 'out']);
            }
        }
        return back()->with('success', 'Pengecekan stok selesai.');
    }

    public function dismissAllAlerts()
    {
        SystemAlert::whereNull('resolved_at')->update(['is_read' => true, 'resolved_at' => now()]);
        return back()->with('success', 'Semua alert berhasil ditutup.');
    }
}
