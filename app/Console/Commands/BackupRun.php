<?php

namespace App\Console\Commands;

use App\Models\SystemSetting;
use App\Models\SystemLog;
use Illuminate\Console\Command;

class BackupRun extends Command
{
    protected $signature = 'backup:run {--force : Skip confirmation}';
    protected $description = 'Jalankan backup database & storage ServiceKU';

    public function handle()
    {
        $this->info('=== ServiceKU Backup ===');
        $this->newLine();

        // Baca konfigurasi dari system settings
        $backupPath = SystemSetting::getValue('backup_path', storage_path('backups'));
        $retentionDays = (int) SystemSetting::getValue('backup_retention_days', '30');

        // Pastikan direktori backup ada
        $dbDir = rtrim($backupPath, '/') . '/databases';
        $storageDir = rtrim($backupPath, '/') . '/storage';

        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');

        // ========== 1. BACKUP DATABASE ==========
        $this->info('1. Backup database...');

        // Cek konfigurasi database dari .env
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');
        $dbMaster = env('DB_DATABASE');

        if (!$dbUser || !$dbPass || !$dbMaster) {
            $this->error('❌ DB credentials tidak lengkap di .env');
            $this->line('Pastikan DB_USERNAME, DB_PASSWORD, dan DB_DATABASE terisi.');
            return Command::FAILURE;
        }

        $safeUser = escapeshellarg($dbUser);
        $safePass = escapeshellarg($dbPass);
        $safeMaster = escapeshellarg($dbMaster);

        // Backup master database via Docker
        $masterFile = "{$dbDir}/master_{$timestamp}.sql";

        $cmd = "docker exec serviceku-mysql mysqldump"
            . " -u{$safeUser} -p{$safePass}"
            . " --routines --triggers --events --single-transaction"
            . " {$safeMaster} > {$masterFile} 2>/dev/null";

        $output = null;
        $resultCode = null;
        exec($cmd, $output, $resultCode);

        if ($resultCode === 0 && file_exists($masterFile)) {
            exec("gzip -f {$masterFile}");
            $this->info('  ✅ Master database: master_' . $timestamp . '.sql.gz');
        } else {
            $this->warn('  ⚠️  Gagal backup master database (kode: ' . $resultCode . ')');
        }

        // Backup semua tenant databases
        $tenantCmd = "docker exec serviceku-mysql mysql"
            . " -u{$safeUser} -p{$safePass}"
            . " -N -e \"SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME LIKE 'tenant_%';\" 2>/dev/null";

        exec($tenantCmd, $tenants, $resultCode);

        $tenantCount = 0;
        foreach ($tenants as $tenantDb) {
            $tenantDb = trim($tenantDb);
            if (empty($tenantDb)) continue;

            $safeTenantDb = escapeshellarg($tenantDb);
            $tenantFile = "{$dbDir}/{$tenantDb}_{$timestamp}.sql";
            $cmd = "docker exec serviceku-mysql mysqldump"
                . " -u{$safeUser} -p{$safePass}"
                . " --routines --triggers --events --single-transaction"
                . " {$safeTenantDb} > {$tenantFile} 2>/dev/null";

            exec($cmd, $output, $resultCode);

            if ($resultCode === 0 && file_exists($tenantFile)) {
                exec("gzip -f {$tenantFile}");
                $tenantCount++;
            }
        }

        $this->info("  ✅ {$tenantCount} database tenant");

        // ========== 2. BACKUP STORAGE ==========
        $this->info('2. Backup storage...');

        $projectDir = base_path();
        $storageFile = "{$storageDir}/storage_{$timestamp}.tar.gz";

        if (is_dir("{$projectDir}/storage/app") || is_dir("{$projectDir}/storage/framework")) {
            $cmd = "tar -czf {$storageFile}"
                . " -C {$projectDir}"
                . " storage/app/public storage/framework 2>/dev/null || true";
            exec($cmd, $output, $resultCode);

            if (file_exists($storageFile)) {
                $this->info('  ✅ Storage: storage_' . $timestamp . '.tar.gz');
            }
        }

        // Backup .env
        $envFile = "{$storageDir}/env_{$timestamp}.backup";
        if (file_exists("{$projectDir}/.env")) {
            copy("{$projectDir}/.env", $envFile);
            $this->info('  ✅ File .env: env_' . $timestamp . '.backup');
        }

        // ========== 3. CLEANUP LAMA ==========
        $this->info('3. Membersihkan backup lebih dari ' . $retentionDays . ' hari...');

        $deleted = 0;
        foreach (glob("{$dbDir}/*.sql.gz") as $file) {
            if (filemtime($file) < now()->subDays($retentionDays)->timestamp) {
                unlink($file);
                $deleted++;
            }
        }
        foreach (glob("{$storageDir}/*.tar.gz") as $file) {
            if (filemtime($file) < now()->subDays($retentionDays)->timestamp) {
                unlink($file);
                $deleted++;
            }
        }
        foreach (glob("{$storageDir}/*.backup") as $file) {
            if (filemtime($file) < now()->subDays($retentionDays)->timestamp) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("  🗑️  {$deleted} file backup lama dihapus");
        } else {
            $this->info('  Tidak ada file lama');
        }

        // ========== SELESAI ==========
        SystemSetting::setValue('backup_last_run', now()->format('Y-m-d H:i:s'), 'backup');
        SystemSetting::setValue('backup_last_status', 'success', 'backup');

        SystemLog::info("Backup completed: {$tenantCount} tenants");

        $this->newLine();
        $this->info('✅ Backup selesai!');
        $this->line("📁 {$backupPath}");

        return Command::SUCCESS;
    }
}
