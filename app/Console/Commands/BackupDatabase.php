<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupDatabase extends Command
{
    protected $signature   = 'backup:database {--name= : Custom backup file name}';
    protected $description = 'Create a SQL backup of the database';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $name     = $this->option('name') ?: date('Y-m-d_H-i-s');
        $filename = $backupDir . '/' . $name . '.sql';

        try {
            $this->exportDatabase($filename);

            // Compress
            $gz = gzopen($filename . '.gz', 'wb9');
            $fh = fopen($filename, 'rb');
            while (!feof($fh)) {
                gzwrite($gz, fread($fh, 65536));
            }
            fclose($fh);
            gzclose($gz);
            unlink($filename);

            $this->info('Backup created: ' . $name . '.sql.gz');

            // Enforce retention
            $this->applyRetention($backupDir);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function exportDatabase(string $path): void
    {
        $config   = config('database.connections.' . config('database.default'));
        $host     = $config['host'];
        $port     = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        $pdo = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);

        $fh = fopen($path, 'wb');
        fwrite($fh, "-- Database backup: {$database}\n");
        fwrite($fh, "-- Created: " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Drop + create
            $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_NUM);
            fwrite($fh, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($fh, $create[1] . ";\n\n");

            // Data
            $rows = $pdo->query("SELECT * FROM `{$table}`");
            $rowCount = 0;
            $insertBuffer = '';

            while ($row = $rows->fetch(\PDO::FETCH_NUM)) {
                $values = array_map(function ($v) use ($pdo) {
                    return $v === null ? 'NULL' : $pdo->quote($v);
                }, $row);

                $insertBuffer .= ($rowCount === 0
                    ? "INSERT INTO `{$table}` VALUES\n("
                    : ",\n(") . implode(', ', $values) . ')';

                $rowCount++;

                if ($rowCount % 500 === 0) {
                    fwrite($fh, $insertBuffer . ";\n");
                    $insertBuffer = '';
                    $rowCount = 0;
                }
            }

            if ($insertBuffer !== '') {
                fwrite($fh, $insertBuffer . ";\n");
            }

            fwrite($fh, "\n");
        }

        fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($fh);
    }

    private function applyRetention(string $dir): void
    {
        $retentionDays = (int) (\App\Models\Setting::where('key', 'backup_retention')->value('value') ?? 30);
        $cutoff = time() - ($retentionDays * 86400);

        foreach (glob($dir . '/*.sql.gz') as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}
