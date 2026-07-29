<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\SystemLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TenantCleanup extends Command
{
    protected $signature = 'tenants:cleanup
        {--days=30 : Hapus tenant expired lebih dari N hari yang lalu}
        {--dry-run : Jalankan simulasi tanpa menghapus}
        {--force : Hapus tanpa konfirmasi}';

    protected $description = 'Bersihkan tenant expired & hapus database tenant yang sudah lama tidak aktif';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $cutoff = now()->subDays($days);

        $this->info("🔍 Mencari tenant expired > {$days} hari yang lalu...");

        // Cari tenant yang statusnya expired dan sudah lewat batas
        $tenants = Tenant::where('subscription_status', 'expired')
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('subscription_ends_at')
                  ->where('trial_ends_at', '<=', $cutoff)
                  ->orWhere('subscription_ends_at', '<=', $cutoff);
            })
            ->get();

        if ($tenants->isEmpty()) {
            $this->info('✅ Tidak ada tenant expired yang perlu dibersihkan.');
            return Command::SUCCESS;
        }

        $this->warn("📋 Ditemukan {$tenants->count()} tenant expired:");

        foreach ($tenants as $tenant) {
            $expiredAt = $tenant->subscription_ends_at ?? $tenant->trial_ends_at;
            $this->line("   - {$tenant->tenant_name} ({$tenant->id}) [expired: {$expiredAt}]");
        }

        if (!$force && !$this->confirm("Yakin ingin menghapus {$tenants->count()} tenant ini?")) {
            $this->info('Dibatalkan.');
            return Command::SUCCESS;
        }

        $deleted = 0;
        $errors = 0;

        foreach ($tenants as $tenant) {
            $this->info("Memproses: {$tenant->tenant_name}...");

            try {
                if (!$dryRun) {
                    // 1. Hapus database SQLite tenant
                    $dbPath = database_path("tenant_{$tenant->id}/database.sqlite");
                    if (file_exists($dbPath)) {
                        unlink($dbPath);
                        $this->line("   ✅ Database SQLite dihapus: {$dbPath}");

                        // Hapus directory tenant jika kosong
                        $dbDir = dirname($dbPath);
                        if (is_dir($dbDir) && count(scandir($dbDir)) <= 2) {
                            rmdir($dbDir);
                        }
                    }

                    // 2. Hapus storage tenant
                    $tenantStorageDir = storage_path("app/public/tenants/{$tenant->id}");
                    if (is_dir($tenantStorageDir)) {
                        array_map('unlink', glob("{$tenantStorageDir}/*.*"));
                        rmdir($tenantStorageDir);
                        $this->line("   ✅ Storage tenant dihapus: {$tenantStorageDir}");
                    }

                    // 3. Hapus domain tenant
                    $tenant->domains()->delete();

                    // 4. Hapus record tenant (cascade akan hapus relasi terkait)
                    $tenant->delete();

                    SystemLog::info("Tenant cleanup: {$tenant->tenant_name} ({$tenant->id}) berhasil dihapus");
                }

                $deleted++;
            } catch (\Throwable $e) {
                $this->error("   ❌ Gagal menghapus {$tenant->tenant_name}: {$e->getMessage()}");
                SystemLog::error("Tenant cleanup error: {$tenant->tenant_name} ({$tenant->id}) - {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();

        if ($dryRun) {
            $this->info("✅ [DRY-RUN] {$deleted} tenant akan dihapus (0 errors)");
        } else {
            $this->info("✅ {$deleted} tenant berhasil dibersihkan ({$errors} errors)");
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
