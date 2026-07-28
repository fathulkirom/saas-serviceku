<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Log;

/**
 * Google Drive Backup Service
 * 
 * Menggunakan rclone untuk upload backup ke Google Drive.
 * 
 * Setup:
 *   1. Install rclone: https://rclone.org/install/
 *   2. Konfigurasi: rclone config (pilih Google Drive)
 *   3. Beri nama remote: "serviceku-backup"
 *   4. Atur folder ID di pengaturan backup
 * 
 * @see https://rclone.org/drive/
 */
class GoogleDriveService
{
    /**
     * Nama remote rclone untuk Google Drive.
     */
    const RCLONE_REMOTE = 'serviceku-backup';

    /**
     * Cek apakah rclone terinstall.
     */
    public static function isRcloneInstalled(): bool
    {
        $output = null;
        $resultCode = null;
        exec('which rclone 2>/dev/null', $output, $resultCode);
        return $resultCode === 0;
    }

    /**
     * Dapatkan versi rclone.
     */
    public static function getRcloneVersion(): ?string
    {
        if (!self::isRcloneInstalled()) return null;
        $output = [];
        exec('rclone version 2>/dev/null', $output);
        return $output[0] ?? null;
    }

    /**
     * Cek konfigurasi Google Drive.
     */
    public static function checkConfig(): array
    {
        $result = [
            'rclone_installed' => self::isRcloneInstalled(),
            'rclone_version' => self::getRcloneVersion(),
            'remote_configured' => false,
            'folder_exists' => false,
            'storage_used' => null,
            'error' => null,
        ];

        if (!$result['rclone_installed']) {
            $result['error'] = 'rclone tidak terinstall. Install: brew install rclone / sudo apt install rclone';
            return $result;
        }

        // Cek remote
        $output = [];
        exec('rclone listremotes 2>/dev/null', $output);
        $result['remote_configured'] = in_array(self::RCLONE_REMOTE . ':', $output);

        if (!$result['remote_configured']) {
            $result['error'] = "Remote '" . self::RCLONE_REMOTE . "' belum dikonfigurasi. Jalankan: rclone config";
            return $result;
        }

        // Cek folder backup
        $folderId = SystemSetting::getValue('gdrive_folder_id', '');
        if ($folderId) {
            $output = [];
            exec("rclone lsd " . self::RCLONE_REMOTE . ":/ 2>/dev/null", $output);
            $result['folder_exists'] = true;
        }

        // Cek storage used
        $output = [];
        exec("rclone about " . self::RCLONE_REMOTE . ": 2>/dev/null", $output);
        if (!empty($output)) {
            $result['storage_used'] = implode("\n", $output);
        }

        return $result;
    }

    /**
     * Upload file backup ke Google Drive.
     *
     * @param string $localPath Path file lokal
     * @param string $remotePath Path di Google Drive (relatif)
     * @return array ['success' => bool, 'message' => string]
     */
    public static function upload(string $localPath, string $remotePath = ''): array
    {
        if (!self::isRcloneInstalled()) {
            return ['success' => false, 'message' => 'rclone tidak terinstall'];
        }

        if (!file_exists($localPath)) {
            return ['success' => false, 'message' => "File tidak ditemukan: {$localPath}"];
        }

        $folderId = SystemSetting::getValue('gdrive_folder_id', '');
        $remote = self::RCLONE_REMOTE . ":/";
        if ($folderId) {
            $remote = self::RCLONE_REMOTE . ":{$folderId}/";
        }
        if ($remotePath) {
            $remote .= $remotePath;
        }

        $fileSize = filesize($localPath);
        $fileName = basename($localPath);

        Log::info("Uploading {$fileName} ({$fileSize} bytes) to Google Drive...");

        $output = [];
        $resultCode = null;
        $cmd = "rclone copy \"{$localPath}\" \"{$remote}\" --progress 2>&1";
        exec($cmd, $output, $resultCode);

        if ($resultCode === 0) {
            $message = "✅ {$fileName} berhasil diupload ke Google Drive";
            Log::info($message);
            SystemLog::info($message);

            // Hapus file lokal setelah upload? (opsional)
            if (SystemSetting::getValue('gdrive_delete_local', 'false') === 'true') {
                unlink($localPath);
                Log::info("File lokal {$fileName} dihapus setelah upload");
            }

            return ['success' => true, 'message' => $message];
        }

        $errorMsg = "❌ Gagal upload {$fileName}: " . implode("\n", $output);
        Log::error($errorMsg);
        SystemLog::error($errorMsg);

        return ['success' => false, 'message' => $errorMsg];
    }

    /**
     * Upload seluruh folder backup ke Google Drive.
     *
     * @param string $localDir Path direktori backup lokal
     * @return array
     */
    public static function uploadBackupFolder(string $localDir): array
    {
        if (!is_dir($localDir)) {
            return ['success' => false, 'message' => "Direktori tidak ditemukan: {$localDir}"];
        }

        $folderId = SystemSetting::getValue('gdrive_folder_id', '');
        $remote = self::RCLONE_REMOTE . ":/";
        if ($folderId) {
            $remote = self::RCLONE_REMOTE . ":{$folderId}/";
        }

        $date = now()->format('Y-m-d');
        $remote .= "backup-{$date}/";

        Log::info("Uploading backup folder to Google Drive: {$remote}");

        $output = [];
        $resultCode = null;
        $cmd = "rclone copy \"{$localDir}\" \"{$remote}\" --progress 2>&1";
        exec($cmd, $output, $resultCode);

        if ($resultCode === 0) {
            $message = "✅ Backup folder berhasil diupload ke Google Drive";
            Log::info($message);
            SystemLog::info($message);
            return ['success' => true, 'message' => $message];
        }

        $errorMsg = "❌ Gagal upload folder: " . implode("\n", $output);
        Log::error($errorMsg);
        SystemLog::error($errorMsg);

        return ['success' => false, 'message' => $errorMsg];
    }

    /**
     * Dapatkan daftar file backup di Google Drive.
     */
    public static function listBackups(): array
    {
        $files = [];

        if (!self::isRcloneInstalled()) {
            return $files;
        }

        $folderId = SystemSetting::getValue('gdrive_folder_id', '');
        $remote = self::RCLONE_REMOTE . ":/";
        if ($folderId) {
            $remote = self::RCLONE_REMOTE . ":{$folderId}/";
        }

        $output = [];
        exec("rclone ls \"{$remote}\" --max-depth 3 2>/dev/null", $output);

        foreach ($output as $line) {
            if (preg_match('/^\s*(\d+)\s+(.+)$/', $line, $m)) {
                $files[] = [
                    'size' => (int) $m[1],
                    'path' => trim($m[2]),
                    'size_formatted' => self::formatBytes((int) $m[1]),
                ];
            }
        }

        return $files;
    }

    /**
     * Hapus file backup lama dari Google Drive (lebih dari X hari).
     */
    public static function cleanupOldBackups(int $retentionDays = 30): int
    {
        $deleted = 0;
        $folderId = SystemSetting::getValue('gdrive_folder_id', '');
        $remote = self::RCLONE_REMOTE . ":/";
        if ($folderId) {
            $remote = self::RCLONE_REMOTE . ":{$folderId}/";
        }

        // Dapatkan daftar folder backup (backup-YYYY-MM-DD)
        $output = [];
        exec("rclone lsd \"{$remote}\" 2>/dev/null", $output);

        foreach ($output as $line) {
            if (preg_match('/(backup-\d{4}-\d{2}-\d{2})/', $line, $m)) {
                $dateStr = str_replace('backup-', '', $m[1]);
                $backupDate = \Carbon\Carbon::parse($dateStr);
                if ($backupDate->diffInDays(now()) > $retentionDays) {
                    $folderPath = $remote . $m[1];
                    exec("rclone purge \"{$folderPath}\" 2>/dev/null");
                    $deleted++;
                    Log::info("Deleted old backup from Google Drive: {$m[1]}");
                }
            }
        }

        return $deleted;
    }

    private static function formatBytes(int $bytes): string
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
