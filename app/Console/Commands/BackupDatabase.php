<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--path= : Custom backup path}';
    protected $description = 'Backup tenant and central databases';

    public function handle(): int
    {
        $path = $this->option('path') ?? storage_path('backups/' . date('Y-m-d_His'));
        if (!is_dir($path)) mkdir($path, 0755, true);

        $this->info("Backing up to: {$path}");

        // Central database backup
        $centralFile = "{$path}/central_db.sql";
        $this->call('db:dump', ['--database' => 'mysql', '--path' => $centralFile]);
        $this->info("Central DB: {$centralFile}");

        // Storage backup (optional)
        $storageFile = "{$path}/storage.tar.gz";
        exec("tar -czf {$storageFile} -C " . storage_path() . " . 2>/dev/null");
        if (file_exists($storageFile)) {
            $this->info("Storage: {$storageFile}");
        }

        // Cleanup old backups (keep last 7 days)
        $this->cleanupOldBackups();

        $this->info('Backup complete.');
        return self::SUCCESS;
    }

    private function cleanupOldBackups(): void
    {
        $backupDir = storage_path('backups');
        if (!is_dir($backupDir)) return;

        $dirs = glob("{$backupDir}/*", GLOB_ONLYDIR);
        usort($dirs, fn($a, $b) => filemtime($b) - filemtime($a));

        foreach (array_slice($dirs, 7) as $old) {
            exec("rm -rf " . escapeshellarg($old));
            $this->line("Removed old backup: " . basename($old));
        }
    }
}
