<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    /**
     * Health check endpoint — GET /health.
     * Returns JSON with status of all critical services.
     */
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app'       => $this->checkApp(),
            'database'  => $this->checkDatabase(),
            'redis'     => $this->checkRedis(),
            'queue'     => $this->checkQueue(),
            'storage'   => $this->checkStorage(),
            'disk'      => $this->checkDisk(),
            'cache'     => $this->checkCache(),
        ];

        $healthy = !in_array(false, array_column($checks, 'healthy'), true);
        $status = $healthy ? 200 : 503;

        return response()->json([
            'status'    => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'version'   => config('app.version', '1.0.0'),
            'checks'    => $checks,
        ], $status);
    }

    private function checkApp(): array
    {
        return [
            'healthy' => true,
            'message' => 'ServiceKU v' . config('app.version', '1.0.0'),
            'env'     => app()->environment(),
            'debug'   => config('app.debug'),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['healthy' => true, 'message' => 'Connected', 'driver' => DB::getDriverName()];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkRedis(): array
    {
        try {
            Redis::connection()->ping();
            return ['healthy' => true, 'message' => 'Connected'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        try {
            $size = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            return ['healthy' => true, 'message' => "{$size} pending, {$failed} failed"];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        try {
            Storage::disk('local')->put('health_check.txt', 'ok');
            Storage::disk('local')->delete('health_check.txt');
            return ['healthy' => true, 'message' => 'Read/Write OK'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    private function checkDisk(): array
    {
        $free = disk_free_space(storage_path());
        $total = disk_total_space(storage_path());
        return [
            'healthy' => $free > 100 * 1024 * 1024, // 100MB minimum
            'message' => sprintf('Free: %.1f GB / Total: %.1f GB', $free / 1e9, $total / 1e9),
        ];
    }

    private function checkCache(): array
    {
        try {
            Cache::put('health_check', 'ok', 10);
            $value = Cache::get('health_check');
            return ['healthy' => $value === 'ok', 'message' => $value === 'ok' ? 'Read/Write OK' : 'Cache miss'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }
}
