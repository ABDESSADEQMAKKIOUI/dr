<?php
// DELETE THIS FILE IMMEDIATELY AFTER USE
$projectRoot = '/home/cfpssma1/public_html/dr.cfpss.ma';
chdir($projectRoot);
set_time_limit(300);

echo '<pre style="background:#111;color:#0f0;padding:20px;font-size:13px;font-family:monospace;">';

$step = $_GET['step'] ?? '1';

if ($step === '1') {
    echo "=== STEP 1: DOWNLOAD COMPOSER ===\n";
    if (file_exists('composer.phar')) {
        echo "composer.phar already exists ✓\n";
    } else {
        echo "Downloading composer.phar...\n";
        $result = copy('https://getcomposer.org/composer-stable.phar', 'composer.phar');
        echo $result ? "Downloaded ✓\n" : "Download failed ✗\n";
    }
    echo "composer.phar exists: " . (file_exists('composer.phar') ? 'YES ✓' : 'NO ✗') . "\n";
    echo shell_exec('php composer.phar --version 2>&1') . "\n";
    echo "\n<a href='?step=2' style='color:yellow;font-size:16px;'>>>> Click here to run composer install (Step 2) <<<</a>\n";
}

if ($step === '2') {
    echo "=== STEP 2: COMPOSER INSTALL ===\n";
    echo "This may take 1-2 minutes...\n\n";
    echo shell_exec('php composer.phar install --no-dev --optimize-autoloader 2>&1');
    echo "\nvendor exists: " . (file_exists('vendor/autoload.php') ? 'YES ✓' : 'NO ✗') . "\n";
    echo "\n<a href='?step=3' style='color:yellow;font-size:16px;'>>>> Click here to run migrations (Step 3) <<<</a>\n";
}

if ($step === '3') {
    echo "=== STEP 3: SETUP LARAVEL ===\n";
    echo shell_exec('chmod -R 755 storage bootstrap/cache 2>&1') ?: "Permissions set\n";
    echo shell_exec('php artisan config:clear 2>&1') . "\n";
    echo shell_exec('php artisan cache:clear 2>&1') . "\n";
    echo shell_exec('php artisan view:clear 2>&1') . "\n";
    echo shell_exec('php artisan route:clear 2>&1') . "\n";
    echo shell_exec('php artisan storage:link 2>&1') . "\n";
    echo "\n=== MIGRATIONS ===\n";
    echo shell_exec('php artisan migrate --force 2>&1') . "\n";
    echo "\n=== LOG (last 20 lines) ===\n";
    $log = 'storage/logs/laravel.log';
    if (file_exists($log)) {
        echo implode('', array_slice(file($log), -20));
    } else {
        echo "No log file\n";
    }
    echo "\n✓ DONE! Delete this file and composer.phar from File Manager\n";
}

echo '</pre>';
