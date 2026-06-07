<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * General settings page
     */
    public function general(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.general', compact('settings'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'timezone' => 'nullable|string',
            'date_format' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            Setting::updateOrCreate(
                ['key' => 'company_logo'],
                ['value' => $logoPath, 'group' => 'general', 'type' => 'file']
            );
            
            // Delete old logo if exists
            $oldLogo = Setting::where('key', 'company_logo')->first();
            if ($oldLogo && $oldLogo->value && $oldLogo->value !== $logoPath) {
                \Storage::disk('public')->delete($oldLogo->value);
            }
        }

        // Save other settings
        foreach ($validated as $key => $value) {
            if ($key !== 'logo') {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'general', 'type' => 'text']
                );
            }
        }

        return redirect()->route('settings.general')->with('success', __('app.saved_success'));
    }

    /**
     * Invoice settings page
     */
    public function invoice(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.invoice', compact('settings'));
    }

    /**
     * Update invoice settings
     */
    public function updateInvoice(Request $request)
    {
        $request->validate([
            'invoice_prefix'          => 'nullable|string|max:20',
            'next_invoice_number'     => 'nullable|integer|min:1',
            'invoice_number_format'   => 'nullable|in:sequential,padded,year_prefix',
            'reset_invoice_counter'   => 'nullable|in:never,yearly,monthly',
            'invoice_color'           => 'nullable|string|max:7',
            'invoice_template'        => 'nullable|in:modern,classic,minimal',
            'invoice_footer'          => 'nullable|string',
            'invoice_terms'           => 'nullable|string',
            'default_payment_terms'   => 'nullable|integer|min:0',
            'late_fee_percentage'     => 'nullable|numeric|min:0',
            'payment_instructions'    => 'nullable|string',
        ]);

        $textKeys = [
            'invoice_prefix', 'next_invoice_number', 'invoice_number_format',
            'reset_invoice_counter', 'invoice_color', 'invoice_template',
            'invoice_footer', 'invoice_terms', 'default_payment_terms',
            'late_fee_percentage', 'payment_instructions',
        ];

        foreach ($textKeys as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->input($key), 'group' => 'invoice', 'type' => 'text']
            );
        }

        $boolKeys = ['show_company_logo', 'show_tax_number', 'show_bank_details', 'show_signature_block', 'auto_send_invoice'];
        foreach ($boolKeys as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->boolean($key) ? '1' : '0', 'group' => 'invoice', 'type' => 'boolean']
            );
        }

        return redirect()->route('settings.invoice')->with('success', __('app.saved_success'));
    }

    /**
     * Tax settings page
     */
    public function tax(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.tax', compact('settings'));
    }

    /**
     * Update tax settings
     */
    public function updateTax(Request $request)
    {
        $validated = $request->validate([
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_number' => 'nullable|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'tax', 'type' => 'text']
            );
        }

        return redirect()->route('settings.tax')->with('success', __('app.saved_success'));
    }

    /**
     * Notifications settings page
     */
    public function notifications(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.notifications', compact('settings'));
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $boolKeys = [
            'notify_invoice_created', 'notify_quotation_created',
            'notify_new_order', 'notify_low_stock', 'notify_new_customer',
            'notify_payment', 'notify_overdue',
            'notify_backup', 'notify_updates', 'notify_errors',
        ];

        foreach ($boolKeys as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->boolean($key) ? '1' : '0', 'group' => 'notifications', 'type' => 'boolean']
            );
        }

        Setting::updateOrCreate(
            ['key' => 'admin_emails'],
            ['value' => $request->input('admin_emails', ''), 'group' => 'notifications', 'type' => 'text']
        );

        return redirect()->route('settings.notifications')->with('success', __('app.saved_success'));
    }

    /**
     * SMTP Email settings page
     */
    public function emailSmtp(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.email-smtp', compact('settings'));
    }

    /**
     * Update SMTP settings
     */
    public function updateEmailSmtp(Request $request)
    {
        $request->validate([
            'smtp_host'         => 'nullable|string|max:255',
            'smtp_port'         => 'nullable|integer',
            'smtp_encryption'   => 'nullable|in:tls,ssl,',
            'smtp_username'     => 'nullable|string|max:255',
            'smtp_from_name'    => 'nullable|string|max:255',
            'smtp_from_address' => 'nullable|email',
        ]);

        $keys = ['smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_from_name', 'smtp_from_address'];
        foreach ($keys as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->input($key), 'group' => 'smtp', 'type' => 'text']
            );
        }

        if ($request->filled('smtp_password')) {
            Setting::updateOrCreate(
                ['key' => 'smtp_password'],
                ['value' => encrypt($request->input('smtp_password')), 'group' => 'smtp', 'type' => 'secret']
            );
        }

        return redirect()->route('settings.email-smtp')->with('success', __('app.saved_success'));
    }

    /**
     * Send a test email to verify SMTP settings
     */
    public function testEmailSmtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            \Mail::raw('This is a test email from your application. SMTP is configured correctly!', function ($msg) use ($request) {
                $msg->to($request->email)->subject('SMTP Test Email');
            });
            return redirect()->route('settings.email-smtp')->with('test_success', true);
        } catch (\Exception $e) {
            return redirect()->route('settings.email-smtp')->with('test_error', $e->getMessage());
        }
    }

    /**
     * Languages settings page
     */
    public function languages(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        $languages = config('languages', []);
        return view('settings.languages', compact('settings', 'languages'));
    }

    /**
     * Update language settings
     */
    public function updateLanguages(Request $request)
    {
        $validated = $request->validate([
            'default_language' => 'nullable|string|max:10',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'language', 'type' => 'text']
            );
        }

        return redirect()->route('settings.languages')->with('success', __('app.saved_success'));
    }

    /**
     * Currencies settings page
     */
    public function currencies(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.currencies', compact('settings'));
    }

    /**
     * Update currency settings
     */
    public function updateCurrencies(Request $request)
    {
        $validated = $request->validate([
            'default_currency' => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:10',
            'currency_position' => 'nullable|in:before,after',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'currency', 'type' => 'text']
            );
        }

        return redirect()->route('settings.currencies')->with('success', __('app.saved_success'));
    }

    /**
     * Email templates settings page
     */
    public function emailTemplates(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.email-templates', compact('settings'));
    }

    /**
     * Update email templates
     */
    public function updateEmailTemplates(Request $request)
    {
        $validated = $request->validate([
            'email_template_*' => 'nullable|string',
        ]);

        foreach ($request->except('_token') as $key => $value) {
            if (str_starts_with($key, 'email_template_')) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'email', 'type' => 'text']
                );
            }
        }

        return redirect()->route('settings.email-templates')->with('success', __('app.saved_success'));
    }

    /**
     * Backup settings page
     */
    public function backup(): View
    {
        $settings  = Setting::all()->pluck('value', 'key');
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $files = collect(glob($backupDir . '/*.sql.gz') ?: [])
            ->sortByDesc(fn($f) => filemtime($f))
            ->values()
            ->map(fn($f) => [
                'filename'   => basename($f),
                'name'       => basename($f, '.sql.gz'),
                'size'       => $this->formatBytes(filesize($f)),
                'size_bytes' => filesize($f),
                'created_at' => date('d/m/Y H:i', filemtime($f)),
            ]);

        $totalSize = $files->sum('size_bytes');
        $stats = [
            'total'      => $files->count(),
            'last_backup'=> $files->first()['created_at'] ?? __('app.never'),
            'total_size' => $this->formatBytes($totalSize),
        ];

        return view('settings.backup', compact('settings', 'files', 'stats'));
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    /**
     * Create backup
     */
    public function createBackup(Request $request)
    {
        $name = $request->input('backup_name', date('Y-m-d_H-i-s'));
        $name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);

        try {
            \Artisan::call('backup:database', ['--name' => $name]);
            return redirect()->route('settings.backup')->with('success', 'Backup "' . $name . '.sql.gz" created successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('settings.backup')->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Download a backup file
     */
    public function downloadBackup(Request $request)
    {
        $filename  = $request->input('filename');
        $backupDir = storage_path('app/backups');
        $path      = realpath($backupDir . '/' . $filename);

        // Prevent path traversal
        if (!$path || !str_starts_with($path, realpath($backupDir))) {
            abort(403);
        }

        return response()->download($path);
    }

    /**
     * Delete a backup file
     */
    public function deleteBackup(Request $request)
    {
        $filename  = $request->input('filename');
        $backupDir = storage_path('app/backups');
        $path      = realpath($backupDir . '/' . $filename);

        if ($path && str_starts_with($path, realpath($backupDir)) && file_exists($path)) {
            unlink($path);
        }

        return redirect()->route('settings.backup')->with('success', 'Backup deleted.');
    }

    /**
     * Save backup schedule settings
     */
    public function saveBackupSchedule(Request $request)
    {
        $keys = ['backup_frequency', 'backup_time', 'backup_retention', 'enable_auto_backup'];
        foreach ($keys as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $request->input($key, $key === 'enable_auto_backup' ? '0' : null)]);
        }
        return redirect()->route('settings.backup')->with('success', 'Backup schedule saved successfully.');
    }

    /**
     * Restore backup
     */
    public function restoreBackup(Request $request)
    {
        return redirect()->route('settings.backup')->with('success', 'Restore is not supported from the UI for safety. Use the SQL file directly.');
    }

    /**
     * POS settings page
     */
    public function pos(): View
    {
        $settings  = Setting::all()->pluck('value', 'key');
        $warehouses = \App\Models\Warehouse::where('is_active', true)->get();
        $customers  = \App\Models\Customer::where('is_active', true)->get();
        return view('settings.pos', compact('settings', 'warehouses', 'customers'));
    }

    /**
     * Update POS settings
     */
    public function updatePos(Request $request)
    {
        $validated = $request->validate([
            'pos_receipt_size'      => 'nullable|in:a4,80mm,58mm',
            'pos_auto_print'        => 'nullable|boolean',
            'pos_show_logo'         => 'nullable|boolean',
            'pos_show_warehouse'    => 'nullable|boolean',
            'pos_default_warehouse' => 'nullable|exists:warehouses,id',
            'pos_default_customer'  => 'nullable|exists:customers,id',
        ]);

        $boolKeys = ['pos_auto_print', 'pos_show_logo', 'pos_show_warehouse'];

        foreach ($boolKeys as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->boolean($key) ? 'true' : 'false', 'group' => 'pos', 'type' => 'boolean']
            );
        }

        $textKeys = ['pos_receipt_size', 'pos_default_warehouse', 'pos_default_customer'];
        foreach ($textKeys as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $validated[$key] ?? null, 'group' => 'pos', 'type' => 'text']
            );
        }

        return redirect()->route('settings.pos')->with('success', __('app.saved_success'));
    }
}
