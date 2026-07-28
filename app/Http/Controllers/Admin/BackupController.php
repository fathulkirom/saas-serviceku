<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use App\Models\SystemSetting;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class BackupController extends Controller
{
    /**
     * Tampilkan halaman pengaturan backup.
     */
    public function index()
    {
        $config = [
            'backup_path' => SystemSetting::getValue('backup_path', '/mnt/hdd/Backup/ServiceKU'),
            'backup_retention_days' => (int) SystemSetting::getValue('backup_retention_days', '30'),
            'backup_auto_enabled' => SystemSetting::getValue('backup_auto_enabled', 'false'),
            'backup_auto_time' => SystemSetting::getValue('backup_auto_time', '03:00'),
            'backup_last_run' => SystemSetting::getValue('backup_last_run'),
            'backup_last_status' => SystemSetting::getValue('backup_last_status', '-'),
            // Google Drive settings
            'gdrive_enabled' => SystemSetting::getValue('gdrive_enabled', 'false'),
            'gdrive_folder_id' => SystemSetting::getValue('gdrive_folder_id', ''),
            'gdrive_delete_local' => SystemSetting::getValue('gdrive_delete_local', 'false'),
        ];

        // Dapatkan daftar file backup yang ada
        $backupFiles = $this->getBackupFiles($config['backup_path']);

        // Info disk
        $diskInfo = $this->getDiskInfo($config['backup_path']);

        // Info Google Drive
        $gdriveInfo = GoogleDriveService::checkConfig();
        $gdriveFiles = $gdriveInfo['remote_configured'] ? GoogleDriveService::listBackups() : [];
        $gdriveStorage = $gdriveInfo['storage_used'] ?: null;

        return inertia('Admin/Backup', [
            'config' => $config,
            'backupFiles' => $backupFiles,
            'diskInfo' => $diskInfo,
            'gdriveInfo' => $gdriveInfo,
            'gdriveFiles' => $gdriveFiles,
            'gdriveStorage' => $gdriveStorage,
        ]);
    }

    /**
     * Simpan pengaturan backup.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'backup_path' => 'required|string|max:500',
            'backup_retention_days' => 'required|integer|min:1|max:365',
            'backup_auto_enabled' => 'required|in:true,false',
            'backup_auto_time' => 'required|string|size:5',
            // Google Drive
            'gdrive_enabled' => 'nullable|in:true,false',
            'gdrive_folder_id' => 'nullable|string|max:500',
            'gdrive_delete_local' => 'nullable|in:true,false',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::setValue($key, $value, 'backup');
        }

        SystemLog::info('Backup settings updated');
        return back()->with('success', 'Pengaturan backup berhasil disimpan.');
    }

    /**
     * Upload backup ke Google Drive (manual).
     */
    public function uploadToDrive()
    {
        $backupPath = SystemSetting::getValue('backup_path', '/mnt/hdd/Backup/ServiceKU');

        // Upload folder databases & storage
        $result = GoogleDriveService::uploadBackupFolder($backupPath);

        if ($result['success']) {
            SystemLog::info('Manual upload to Google Drive berhasil');
            return back()->with('success', $result['message']);
        }

        SystemLog::error('Manual upload to Google Drive gagal: ' . $result['message']);
        return back()->with('error', $result['message']);
    }

    /**
     * Jalankan backup secara manual.
     */
    public function runBackup()
    {
        try {
            $exitCode = Artisan::call('backup:run', ['--force' => true]);
            $output = Artisan::output();

            if ($exitCode === 0) {
                SystemSetting::setValue('backup_last_run', now()->format('Y-m-d H:i:s'), 'backup');
                SystemSetting::setValue('backup_last_status', 'success', 'backup');
                SystemLog::info('Backup manual berhasil dijalankan');
                return back()->with('success', '✅ Backup berhasil!');
            } else {
                SystemSetting::setValue('backup_last_status', 'failed', 'backup');
                SystemLog::error('Backup manual gagal: ' . substr($output, 0, 200));
                return back()->with('error', '❌ Backup gagal. Lihat log untuk detail.');
            }
        } catch (\Exception $e) {
            SystemSetting::setValue('backup_last_status', 'failed', 'backup');
            SystemLog::error('Backup error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Hapus file backup tertentu.
     */
    public function deleteBackup(Request $request)
    {
        $validated = $request->validate([
            'path' => 'required|string',
        ]);

        $filePath = $validated['path'];

        // Security: pastikan file di dalam direktori backup
        $basePath = SystemSetting::getValue('backup_path', '/mnt/hdd/Backup/ServiceKU');
        if (!str_starts_with(realpath($filePath) ?: $filePath, realpath($basePath) ?: $basePath)) {
            return back()->with('error', 'Akses ditolak: file di luar direktori backup.');
        }

        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
            SystemLog::info('Backup file deleted: ' . basename($filePath));
            return back()->with('success', 'File backup berhasil dihapus.');
        }

        return back()->with('error', 'File tidak ditemukan.');
    }

    /**
     * Dapatkan daftar file backup dari direktori.
     */
    private function getBackupFiles(string $path): array
    {
        $files = [];

        // Database backups
        $dbPath = rtrim($path, '/') . '/databases';
        if (is_dir($dbPath)) {
            foreach (glob($dbPath . '/*.sql.gz') as $file) {
                $files[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => $this->formatSize(filesize($file)),
                    'date' => date('Y-m-d H:i:s', filemtime($file)),
                    'type' => 'database',
                ];
            }
        }

        // Storage backups
        $storagePath = rtrim($path, '/') . '/storage';
        if (is_dir($storagePath)) {
            foreach (glob($storagePath . '/storage_*.tar.gz') as $file) {
                $files[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => $this->formatSize(filesize($file)),
                    'date' => date('Y-m-d H:i:s', filemtime($file)),
                    'type' => 'storage',
                ];
            }
            foreach (glob($storagePath . '/env_*.backup') as $file) {
                $files[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => $this->formatSize(filesize($file)),
                    'date' => date('Y-m-d H:i:s', filemtime($file)),
                    'type' => 'env',
                ];
            }
        }

        // Urutkan berdasarkan tanggal terbaru
        usort($files, fn($a, $b) => strcmp($b['date'], $a['date']));

        return $files;
    }

    /**
     * Info disk usage.
     */
    private function getDiskInfo(string $path): array
    {
        $path = rtrim($path, '/');
        if (!is_dir($path)) {
            // Buat direktori untuk test
            @mkdir($path, 0755, true);
            @mkdir($path . '/databases', 0755, true);
            @mkdir($path . '/storage', 0755, true);
        }

        // Jika path tidak bisa diakses, cari parent path
        $targetPath = $path;
        if (!is_dir($targetPath) || !disk_total_space($targetPath)) {
            // Fallback ke parent atau root
            $targetPath = function_exists('disk_total_space') && is_dir('/mnt/hdd') ? '/mnt/hdd' : '/';
        }

        $free = @disk_free_space($targetPath);
        $total = @disk_total_space($targetPath);
        $used = $total - $free;

        return [
            'total' => $this->formatSize($total),
            'used' => $this->formatSize($used),
            'free' => $this->formatSize($free),
            'percent' => $total > 0 ? round(($used / $total) * 100, 1) : 0,
        ];
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
