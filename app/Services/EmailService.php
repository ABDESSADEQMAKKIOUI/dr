<?php

namespace App\Services;

use App\Models\EmailLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Send a raw email and log the result.
     */
    public function send(string $to, string $subject, string $body, string $event = '', ?Model $loggable = null): bool
    {
        try {
            Mail::html($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            EmailLog::create([
                'to'            => $to,
                'subject'       => $subject,
                'event'         => $event,
                'status'        => 'sent',
                'error'         => null,
                'loggable_type' => $loggable ? get_class($loggable) : null,
                'loggable_id'   => $loggable?->id,
                'sent_at'       => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            EmailLog::create([
                'to'            => $to,
                'subject'       => $subject,
                'event'         => $event,
                'status'        => 'failed',
                'error'         => $e->getMessage(),
                'loggable_type' => $loggable ? get_class($loggable) : null,
                'loggable_id'   => $loggable?->id,
                'sent_at'       => null,
            ]);

            return false;
        }
    }

    /**
     * Send an invoice email to the customer.
     */
    public function sendInvoice(\App\Models\Invoice $invoice): bool
    {
        if (!$invoice->customer?->email) {
            return false;
        }

        $company = \App\Models\Setting::get('company_name', config('app.name'));
        $subject = "Invoice #{$invoice->reference} from {$company}";

        $body = view('emails.invoice', compact('invoice', 'company'))->render();

        return $this->send($invoice->customer->email, $subject, $body, 'invoice', $invoice);
    }

    /**
     * Send a sale confirmation email.
     */
    public function sendSaleConfirmation(\App\Models\Sale $sale): bool
    {
        if (!$sale->customer?->email) {
            return false;
        }

        $company = \App\Models\Setting::get('company_name', config('app.name'));
        $subject = "Order Confirmation #{$sale->reference} — {$company}";

        $body = view('emails.sale', compact('sale', 'company'))->render();

        return $this->send($sale->customer->email, $subject, $body, 'sale_created', $sale);
    }
}
