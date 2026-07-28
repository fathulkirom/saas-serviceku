<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\SystemLog;
use App\Models\TenantStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            "total_tenants" => Tenant::count(),
            "active_tenants" => Tenant::where("is_active", true)->count(),
            "trial_tenants" => Tenant::where("subscription_status", "trial")->count(),
            "suspended_tenants" => Tenant::where("subscription_status", "suspended")->count(),
            "recent_registrations" => Tenant::where("created_at", ">=", now()->subDays(7))->count(),
            "expiring_trials" => Tenant::where("subscription_status", "trial")
                ->where("trial_ends_at", "<=", now()->addDays(3))
                ->where("trial_ends_at", ">=", now())
                ->count(),
        ];

        $tenants = Tenant::with("plan", "domains")->latest()->paginate(20);

        $aggregate = TenantStat::select(
            DB::raw("COALESCE(SUM(users_count), 0) as total_users"),
            DB::raw("COALESCE(SUM(services_count), 0) as total_services"),
            DB::raw("COALESCE(SUM(sales_count), 0) as total_sales"),
            DB::raw("COALESCE(SUM(total_revenue), 0) as total_revenue"),
            DB::raw("COALESCE(SUM(products_count), 0) as total_products"),
            DB::raw("COALESCE(SUM(storage_used_mb), 0) as total_storage")
        )->first();

        $recentLogs = SystemLog::latest()->take(10)->get();
        $dbSize = $this->getDatabaseSize();

        return inertia("Admin/Dashboard", [
            "stats" => $stats,
            "tenants" => $tenants,
            "totalStats" => $aggregate,
            "recentLogs" => $recentLogs,
            "systemHealth" => [
                "php_version" => phpversion(),
                "database_size" => $dbSize,
                "laravel_version" => app()->version(),
            ],
        ]);
    }

    private function getDatabaseSize(): string
    {
        try {
            $db = config("database.default");
            if ($db === "sqlite") {
                $path = database_path("database.sqlite");
                $size = file_exists($path) ? round(filesize($path) / 1024 / 1024, 2) . " MB" : "0 MB";
                return $size;
            }
            return "N/A";
        } catch (\Exception $e) {
            return "Error";
        }
    }
}
