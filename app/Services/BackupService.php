<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    /**
     * Créer backup complet
     */
    public function createBackup(): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupName = "backup_{$timestamp}";
        $backupPath = storage_path("app/backups/{$backupName}");

        // Créer dossier
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // 1. Backup base de données
        $this->backupDatabase($backupPath);

        // 2. Backup fichiers uploads
        $this->backupUploads($backupPath);

        // 3. Créer archive ZIP
        $zipFile = $this->createZipArchive($backupPath, $backupName);

        // 4. Supprimer dossier temporaire
        $this->deleteDirectory($backupPath);

        return $zipFile;
    }

    protected function backupDatabase(string $path): void
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $sqlFile = $path . '/database.sql';

        // Utiliser mysqldump
        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );

        exec($command);
    }

    protected function backupUploads(string $path): void
    {
        $uploadsPath = public_path('uploads');
        
        if (file_exists($uploadsPath)) {
            $this->copyDirectory($uploadsPath, $path . '/uploads');
        }
    }

    protected function createZipArchive(string $sourcePath, string $zipName): string
    {
        $zipFile = storage_path("app/backups/{$zipName}.zip");
        
        $zip = new ZipArchive();
        $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourcePath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourcePath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        return $zipFile;
    }

    protected function copyDirectory(string $source, string $destination): void
    {
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $files = scandir($source);
        
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $srcFile = $source . '/' . $file;
                $destFile = $destination . '/' . $file;

                if (is_dir($srcFile)) {
                    $this->copyDirectory($srcFile, $destFile);
                } else {
                    copy($srcFile, $destFile);
                }
            }
        }
    }

    protected function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }

    /**
     * Lister les backups
     */
    public function listBackups(): array
    {
        $backupPath = storage_path('app/backups');
        
        if (!file_exists($backupPath)) {
            return [];
        }

        $files = glob($backupPath . '/*.zip');
        
        return array_map(function ($file) {
            return [
                'name' => basename($file),
                'size' => filesize($file),
                'created_at' => date('Y-m-d H:i:s', filetime($file)),
            ];
        }, $files);
    }

    /**
     * Supprimer backup
     */
    public function deleteBackup(string $filename): bool
    {
        $file = storage_path("app/backups/{$filename}");
        
        if (file_exists($file)) {
            return unlink($file);
        }

        return false;
    }
}
