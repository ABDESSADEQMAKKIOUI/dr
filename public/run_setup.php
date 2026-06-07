<?php
// DELETE THIS FILE IMMEDIATELY AFTER USE
chdir(dirname(__DIR__));

echo '<pre style="background:#111;color:#0f0;padding:20px;font-size:13px;font-family:monospace;">';
echo "=== DIAGNOSTICS ===\n";
echo "Working dir: " . getcwd() . "\n";
echo "PHP version: " . phpversion() . "\n";
echo ".env exists: " . (file_exists('.env') ? 'YES' : 'NO') . "\n";
echo "vendor exists: " . (file_exists('vendor/autoload.php') ? 'YES' : 'NO') . "\n";
echo "artisan exists: " . (file_exists('artisan') ? 'YES' : 'NO') . "\n";
echo "storage writable: " . (is_writable('storage') ? 'YES' : 'NO') . "\n";
echo "bootstrap/cache writable: " . (is_writable('bootstrap/cache') ? 'YES' : 'NO') . "\n";

echo "\n=== PHP PATH ===\n";
echo shell_exec('which php 2>&1') ?: "(not found)\n";
echo shell_exec('php -v 2>&1') ?: "(error)\n";

echo "\n=== ARTISAN TEST ===\n";
$output = shell_exec('php artisan --version 2>&1');
echo $output ?: "(no output - artisan may be failing)\n";

echo "\n=== LARAVEL ERROR LOG (last 30 lines) ===\n";
$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $last30 = array_slice($lines, -30);
    echo implode('', $last30);
} else {
    echo "No log file found\n";
}

echo "\n=== STORAGE PERMISSIONS ===\n";
echo shell_exec('ls -la storage/ 2>&1');
echo shell_exec('ls -la bootstrap/ 2>&1');

echo "\n=== FIX PERMISSIONS ===\n";
echo shell_exec('chmod -R 755 storage bootstrap/cache 2>&1') ?: "Done\n";

echo "\n=== CLEAR CONFIG ===\n";
echo shell_exec('php artisan config:clear 2>&1') ?: "(no output)\n";

echo "\n=== CLEAR CACHE ===\n";
echo shell_exec('php artisan cache:clear 2>&1') ?: "(no output)\n";

echo "\n=== CLEAR VIEWS ===\n";
echo shell_exec('php artisan view:clear 2>&1') ?: "(no output)\n";

echo "\n=== RUN MIGRATIONS ===\n";
echo shell_exec('php artisan migrate --force 2>&1') ?: "(no output)\n";

echo '</pre>';
echo '<p style="color:red;font-weight:bold;font-size:16px;">!! DELETE THIS FILE from cPanel File Manager now !!</p>';
