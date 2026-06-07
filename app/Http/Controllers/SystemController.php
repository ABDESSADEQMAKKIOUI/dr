<?php

namespace App\Http\Controllers;

use App\Services\CacheService;
use App\Services\BackupService;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function __construct(
        protected CacheService $cacheService,
        protected BackupService $backupService
    ) {}

    /**
     * Stats cache
     */
    public function cacheStats()
    {
        return response()->json($this->cacheService->getStats());
    }

    /**
     * Vider cache
     */
    public function clearCache()
    {
        $this->cacheService->flush();
        return response()->json(['message' => 'Cache vidé avec succès']);
    }

    /**
     * Créer backup
     */
    public function createBackup()
    {
        $backupFile = $this->backupService->createBackup();
        
        return response()->json([
            'message' => 'Backup créé avec succès',
            'file' => basename($backupFile),
        ]);
    }

    /**
     * Liste backups
     */
    public function listBackups()
    {
        return response()->json($this->backupService->listBackups());
    }

    /**
     * Télécharger backup
     */
    public function downloadBackup(string $filename)
    {
        $file = storage_path("app/backups/{$filename}");
        
        if (!file_exists($file)) {
            return response()->json(['error' => 'Backup non trouvé'], 404);
        }

        return response()->download($file);
    }

    /**
     * Supprimer backup
     */
    public function deleteBackup(string $filename)
    {
        $this->backupService->deleteBackup($filename);
        return response()->json(['message' => 'Backup supprimé']);
    }

    /**
     * Infos système
     */
    public function systemInfo()
    {
        return response()->json([
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment(),
        ]);
    }
}
