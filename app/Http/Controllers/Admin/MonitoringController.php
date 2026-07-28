<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantStat;
use App\Models\SystemLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with(["plan", "stats"])->latest()->get();
        $aggregate = TenantStat::select(
            DB::raw("COALESCE(SUM(users_count), 0) as total_users"),
            DB::raw("COALESCE(SUM(services_count), 0) as total_services"),
            DB::raw("COALESCE(SUM(sales_count), 0) as total_sales"),
            DB::raw("COALESCE(SUM(total_revenue), 0) as total_revenue"),
            DB::raw("COALESCE(SUM(products_count), 0) as total_products"),
            DB::raw("COALESCE(SUM(storage_used_mb), 0) as total_storage")
        )->first();

        $recentLogs = SystemLog::latest()->take(30)->get();
        $errorsToday = SystemLog::whereDate("created_at", today())
            ->whereIn("level", ["error", "critical"])->count();
        $registrationsToday = Tenant::whereDate("created_at", today())->count();

        $health = [
            "php_version" => phpversion(),
            "laravel_version" => app()->version(),
            "environment" => app()->environment(),
            "debug_mode" => config("app.debug"),
            "cache_driver" => config("cache.default"),
            "queue_driver" => config("queue.default"),
            "session_driver" => config("session.driver"),
            "db_connection" => config("database.default"),
            "server_time" => now()->format("Y-m-d H:i:s"),
            "server_timezone" => config("app.timezone"),
            "db_status" => "Unknown",
        ];
        try { DB::connection()->getPdo(); $health["db_status"] = "Connected"; } catch (\Exception $e) { $health["db_status"] = "Error"; }

        return inertia("Admin/Monitoring", [
            "tenants" => $tenants,
            "aggregate" => $aggregate,
            "recentLogs" => $recentLogs,
            "errorsToday" => $errorsToday,
            "registrationsToday" => $registrationsToday,
            "health" => $health,
            "storageHealth" => $this->checkStorageHealth(),
            "backupHealth" => $this->checkBackupHealth(),
        ]);
    }

    private function checkStorageHealth(): array
    {
        $ssdPath = '/';
        $ssdTotal = disk_total_space($ssdPath);
        $ssdFree = disk_free_space($ssdPath);
        $ssdUsed = $ssdTotal - $ssdFree;
        $ssdPercent = $ssdTotal > 0 ? round(($ssdUsed / $ssdTotal) * 100, 1) : 0;

        $ssdStatus = 'healthy';
        $ssdMessage = '';
        $alerts = [];

        if ($ssdPercent > 90) {
            $ssdStatus = 'critical';
            $ssdMessage = "⚠️ SSD hampir penuh! ({$ssdPercent}%)";
            $alerts[] = ['type' => 'danger', 'message' => $ssdMessage];
        } elseif ($ssdPercent > 80) {
            $ssdStatus = 'warning';
            $ssdMessage = "⚡ SSD tersisa " . $this->formatBytes($ssdFree);
        }

        $backupPath = SystemSetting::getValue('backup_path', storage_path('app/backups'));
        $hddInfo = null;
        $hddStatus = 'unavailable';
        $hddMessage = 'HDD tidak terdeteksi';

        foreach ([$backupPath, '/mnt/hdd', '/mnt/backup'] as $path) {
            $checkPath = is_dir($path) ? $path : dirname($path);
            if (is_dir($checkPath) && disk_total_space($checkPath) > 0) {
                $hddTotal = disk_total_space($checkPath);
                $hddFree = disk_free_space($checkPath);
                $hddPercent = $hddTotal > 0 ? round((($hddTotal - $hddFree) / $hddTotal) * 100, 1) : 0;
                $hddInfo = ['path' => $checkPath, 'total' => $this->formatBytes($hddTotal), 'free' => $this->formatBytes($hddFree), 'percent' => $hddPercent];
                $hddStatus = $hddPercent > 90 ? 'critical' : 'healthy';
                $hddMessage = $hddPercent > 90 ? "⚠️ HDD hampir penuh!" : "HDD terdeteksi di {$checkPath}";
                if ($hddPercent > 90) $alerts[] = ['type' => 'danger', 'message' => $hddMessage];
                break;
            }
        }

        return [
            'ssd' => ['total' => $this->formatBytes($ssdTotal), 'free' => $this->formatBytes($ssdFree), 'percent' => $ssdPercent, 'status' => $ssdStatus, 'message' => $ssdMessage],
            'hdd' => ['info' => $hddInfo, 'status' => $hddStatus, 'message' => $hddMessage],
            'alerts' => $alerts,
        ];
    }

    private function checkBackupHealth(): array
    {
        $lastRun = SystemSetting::getValue('backup_last_run');
        $lastStatus = SystemSetting::getValue('backup_last_status', '-');
        $status = 'unknown';
        $message = 'Belum pernah backup';
        $alerts = [];

        if ($lastRun) {
            $hoursSince = \Carbon\Carbon::parse($lastRun)->diffInHours(now());
            if ($lastStatus === 'success') {
                $status = $hoursSince > 72 ? 'critical' : ($hoursSince > 48 ? 'warning' : 'healthy');
                $message = $status === 'healthy' ? "✅ Backup {$hoursSince} jam yang lalu" : "⚠️ Backup terakhir {$hoursSince} jam yang lalu";
                if ($status !== 'healthy') $alerts[] = ['type' => 'danger', 'message' => $message];
            } else {
                $status = 'critical';
                $message = "❌ Backup terakhir gagal!";
                $alerts[] = ['type' => 'danger', 'message' => $message];
            }
        }

        return ['status' => $status, 'message' => $message, 'last_run' => $lastRun ?? '-', 'last_status' => $lastStatus, 'alerts' => $alerts];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
