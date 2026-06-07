<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Process recurring invoices every day at midnight
Schedule::command('invoices:process-recurring')->daily();

// Daily database backup (runs the command, which checks the enable_auto_backup setting)
Schedule::command('backup:database', ['--name' => 'auto_' . date('Y-m-d')])->dailyAt('02:00')->name('auto-backup')->withoutOverlapping()->when(function () {
    return \App\Models\Setting::where('key', 'enable_auto_backup')->value('value') === '1';
});
