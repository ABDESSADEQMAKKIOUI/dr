<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $quotation) {}

    public function envelope(): Envelope
    {
        $appName = \App\Models\Setting::where('key', 'company_name')->value('value') ?? config('app.name');
        $ref = $this->quotation->reference ?? $this->quotation->quotation_number ?? '#' . $this->quotation->id;
        return new Envelope(
            subject: "Quotation {$ref} — {$appName}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quotation');
    }
}
