<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use App\Models\Invoice;
use App\Models\Sale;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailLogController extends Controller
{
    /**
     * Email delivery log report.
     */
    public function index(Request $request): View
    {
        $logs = EmailLog::latest()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->event,  fn($q) => $q->where('event', $request->event))
            ->when($request->from,   fn($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to,     fn($q) => $q->whereDate('created_at', '<=', $request->to))
            ->paginate(30);

        return view('reports.email-logs', compact('logs'));
    }

    /**
     * Send invoice email on demand.
     */
    public function sendInvoiceEmail(Invoice $invoice)
    {
        $emailService = app(EmailService::class);
        $success = $emailService->sendInvoice($invoice);

        return back()->with(
            $success ? 'success' : 'error',
            $success
                ? 'Invoice email sent to ' . $invoice->customer->email
                : 'Failed to send email. Check SMTP settings or customer email address.'
        );
    }

    /**
     * Send sale confirmation email on demand.
     */
    public function sendSaleEmail(Sale $sale)
    {
        $emailService = app(EmailService::class);
        $success = $emailService->sendSaleConfirmation($sale);

        return back()->with(
            $success ? 'success' : 'error',
            $success
                ? 'Sale confirmation email sent to ' . $sale->customer->email
                : 'Failed to send email. Check SMTP settings or customer email address.'
        );
    }
}
