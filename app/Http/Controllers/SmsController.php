<?php

namespace App\Http\Controllers;

use App\Models\SmsLog;
use App\Models\SmsSettings;
use App\Models\SmsTemplate;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsController extends Controller
{
    /**
     * SMS & WhatsApp settings page.
     */
    public function settings(): View
    {
        $settings = SmsSettings::first() ?? new SmsSettings();
        return view('settings.sms', compact('settings'));
    }

    /**
     * Save SMS settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'gateway'             => 'nullable|in:twilio,nexmo,infobip,termii,whatsapp',
            'twilio_sid'          => 'nullable|string|max:100',
            'twilio_token'        => 'nullable|string|max:200',
            'twilio_from'         => 'nullable|string|max:30',
            'nexmo_key'           => 'nullable|string|max:100',
            'nexmo_secret'        => 'nullable|string|max:200',
            'nexmo_from'          => 'nullable|string|max:30',
            'infobip_api_key'     => 'nullable|string|max:200',
            'infobip_base_url'    => 'nullable|url',
            'infobip_from'        => 'nullable|string|max:30',
            'termii_api_key'      => 'nullable|string|max:200',
            'termii_sender_id'    => 'nullable|string|max:30',
            'whatsapp_token'      => 'nullable|string|max:500',
            'whatsapp_phone_id'   => 'nullable|string|max:100',
        ]);

        $boolFields = [
            'sms_enabled', 'whatsapp_enabled',
            'notify_sale', 'notify_purchase', 'notify_quotation',
            'notify_payment', 'notify_sale_return', 'notify_purchase_return',
            'notify_whatsapp_sale', 'notify_whatsapp_purchase',
        ];

        foreach ($boolFields as $field) {
            $validated[$field] = $request->boolean($field);
        }

        SmsSettings::updateOrCreate(['id' => 1], $validated);

        return redirect()->route('settings.sms.index')->with('success', 'SMS settings saved.');
    }

    /**
     * Send a test SMS.
     */
    public function test(Request $request)
    {
        $request->validate(['test_number' => 'required|string']);

        $sms     = app(SmsService::class);
        $success = $sms->send($request->test_number, 'Test SMS from ' . config('app.name') . '. Gateway is working!', 'test');

        return back()->with(
            $success ? 'success' : 'error',
            $success ? 'Test SMS sent successfully!' : 'Failed to send: ' . ($sms->isEnabled() ? 'Check gateway credentials.' : 'SMS not enabled.')
        );
    }

    /**
     * SMS templates management page.
     */
    public function templates(): View
    {
        $templates = SmsTemplate::orderBy('event')->get();
        return view('settings.sms-templates', compact('templates'));
    }

    /**
     * Update a single SMS template.
     */
    public function updateTemplate(Request $request, SmsTemplate $template)
    {
        $validated = $request->validate([
            'body'      => 'required|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $template->update([
            'body'      => $validated['body'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Template updated.');
    }

    /**
     * SMS delivery log report.
     */
    public function logs(Request $request): View
    {
        $logs = SmsLog::latest()
            ->when($request->gateway, fn($q) => $q->where('gateway', $request->gateway))
            ->when($request->status,  fn($q) => $q->where('status', $request->status))
            ->when($request->from,    fn($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to,      fn($q) => $q->whereDate('created_at', '<=', $request->to))
            ->paginate(30);

        return view('reports.sms-logs', compact('logs'));
    }
}
