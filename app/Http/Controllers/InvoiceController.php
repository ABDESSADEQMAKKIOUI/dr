<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Setting;
use App\Mail\InvoiceMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $invoices = Invoice::with(['customer', 'sale'])->paginate(15);
        return view('invoices.index', compact('invoices'));
    }

    public function create(): View
    {
        $customers = Customer::where('is_active', true)->get();
        return view('invoices.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'due_date' => 'required|date',
            'items' => 'required|array|min:1',
        ]);

        $invoice = Invoice::create($validated);

        $autoSend = Setting::where('key', 'notify_invoice_created')->value('value');
        if ($autoSend === '1') {
            $invoice->load(['customer', 'items.product', 'payments']);
            $email = $invoice->customer?->email;
            if ($email) {
                try {
                    Mail::to($email)->send(new InvoiceMail($invoice));
                } catch (\Exception) {}
            }
        }

        return redirect()->route('invoices.index')->with('success', __('app.created_success'));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['customer', 'items.product', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        $customers = Customer::where('is_active', true)->get();
        return view('invoices.edit', compact('invoice', 'customers'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'due_date' => 'required|date',
        ]);

        $invoice->update($validated);
        return redirect()->route('invoices.index')->with('success', __('app.updated_success'));
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', __('app.deleted_success'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'payments']);
        return view('invoices.print', compact('invoice'));
    }

    public function download(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'payments']);
        
        // For now, redirect to print view (can be enhanced with PDF generation later)
        return view('invoices.print', compact('invoice'));
    }
}
