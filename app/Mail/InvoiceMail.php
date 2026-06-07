<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        $appName = \App\Models\Setting::where('key', 'company_name')->value('value') ?? config('app.name');
        return new Envelope(
            subject: "Invoice {$this->invoice->reference} — {$appName}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invoice');
    }
}
