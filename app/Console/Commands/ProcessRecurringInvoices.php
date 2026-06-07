<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\RecurringInvoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringInvoices extends Command
{
    protected $signature   = 'invoices:process-recurring';
    protected $description = 'Generate invoices from active recurring invoice templates that are due today.';

    public function handle(): int
    {
        $today = Carbon::today();

        $due = RecurringInvoice::where('status', 'active')
            ->where('next_run_at', '<=', $today)
            ->get();

        if ($due->isEmpty()) {
            $this->info('No recurring invoices due today.');
            return self::SUCCESS;
        }

        $generated = 0;

        foreach ($due as $template) {
            try {
                $this->generateInvoice($template, $today);
                $this->advanceNextRun($template, $today);
                $generated++;
            } catch (\Throwable $e) {
                $this->error("Failed for recurring #{$template->id}: {$e->getMessage()}");
            }
        }

        $this->info("Generated {$generated} invoice(s) from recurring templates.");
        return self::SUCCESS;
    }

    private function generateInvoice(RecurringInvoice $template, Carbon $today): void
    {
        $items = is_string($template->items) ? json_decode($template->items, true) : (array) $template->items;

        $invoice = Invoice::create([
            'customer_id'          => $template->customer_id,
            'date'                 => $today->format('Y-m-d'),
            'due_date'             => $today->addDays(30)->format('Y-m-d'),
            'reference'            => 'REC-' . strtoupper(uniqid()),
            'total_amount'         => $template->total,
            'paid_amount'          => 0,
            'payment_status'       => 'unpaid',
            'recurring_invoice_id' => $template->id,
            'notes'                => "Auto-generated from recurring template #{$template->id}",
        ]);

        foreach ($items as $item) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'product_id'  => $item['product_id'] ?? null,
                'description' => $item['description'] ?? '',
                'quantity'    => $item['quantity'] ?? 1,
                'price'       => $item['price'] ?? 0,
                'subtotal'    => ($item['quantity'] ?? 1) * ($item['price'] ?? 0),
            ]);
        }
    }

    private function advanceNextRun(RecurringInvoice $template, Carbon $today): void
    {
        $next = match ($template->frequency) {
            'daily'   => $today->copy()->addDay(),
            'weekly'  => $today->copy()->addWeek(),
            'monthly' => $today->copy()->addMonth(),
            'yearly'  => $today->copy()->addYear(),
            default   => $today->copy()->addMonth(),
        };

        $template->update([
            'last_run_at' => $today,
            'next_run_at' => $next,
        ]);
    }
}
