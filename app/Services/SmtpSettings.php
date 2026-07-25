<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class SmtpSettings
{
    /**
     * Apply per-tenant SMTP settings from the `settings` table onto the mail
     * config. Extracted verbatim from AppServiceProvider::applySmtpSettings().
     *
     * This MUST be called AFTER the tenant connection swap (ResolveTenant), not
     * at provider-boot time: it reads App\Models\Setting, which resolves against
     * the current default connection. Every failure is swallowed exactly as the
     * original implementation did.
     */
    public static function apply(): void
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
