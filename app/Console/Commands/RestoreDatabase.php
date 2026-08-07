<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RestoreDatabase extends Command
{
    protected $signature = 'restore:database {path : Path to backup directory}';
    protected $description = 'Restore database from backup';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (!is_dir($path)) {
            $this->error("Backup directory not found: {$path}");
            return self::FAILURE;
        }

        $centralFile = "{$path}/central_db.sql";
        if (file_exists($centralFile)) {
            $this->warn('Restoring central database...');
            $this->call('db:wipe', ['--database' => 'mysql', '--force' => true]);
            exec("mysql -h" . env('DB_HOST') . " -u" . env('DB_USERNAME') . " -p" . env('DB_PASSWORD') . " " . env('DB_DATABASE') . " < {$centralFile}");
            $this->info('Central database restored.');
        }

        $this->info('Restore complete. Run migrations: php artisan migrate --force');
        return self::SUCCESS;
    }
}
