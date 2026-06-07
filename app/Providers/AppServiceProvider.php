<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->applySmtpSettings();
    }

    private function applySmtpSettings(): void
    {
        try {
            if (!\Schema::hasTable('settings')) {
                return;
            }

            $s = \App\Models\Setting::whereIn('key', [
                'smtp_host', 'smtp_port', 'smtp_encryption',
                'smtp_username', 'smtp_password',
                'smtp_from_address', 'smtp_from_name',
            ])->pluck('value', 'key');

            if ($s->isEmpty()) {
                return;
            }

            if ($s->has('smtp_host'))         Config::set('mail.mailers.smtp.host',       $s['smtp_host']);
            if ($s->has('smtp_port'))         Config::set('mail.mailers.smtp.port',       (int) $s['smtp_port']);
            if ($s->has('smtp_encryption'))   Config::set('mail.mailers.smtp.encryption', $s['smtp_encryption'] ?: null);
            if ($s->has('smtp_username'))     Config::set('mail.mailers.smtp.username',   $s['smtp_username']);
            if ($s->has('smtp_from_address')) Config::set('mail.from.address',            $s['smtp_from_address']);
            if ($s->has('smtp_from_name'))    Config::set('mail.from.name',               $s['smtp_from_name']);

            if ($s->has('smtp_password') && $s['smtp_password']) {
                try {
                    Config::set('mail.mailers.smtp.password', decrypt($s['smtp_password']));
                } catch (\Exception) {}
            }
        } catch (\Exception) {}
    }
}
