<?php

namespace App\Http\Controllers;

use App\Models\RecurringInvoice;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;

class RecurringInvoiceController extends Controller
{
    public function index()
    {
        $invoices = RecurringInvoice::with('customer')
            ->latest()
            ->paginate(20);
        return view('recurring-invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products  = Product::where('is_active', true)->orderBy('name')->get();
        return view('recurring-invoices.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'frequency'   => 'required|in:daily,weekly,monthly,yearly',
            'next_run_at' => 'required|date|after_or_equal:today',
            'send_email'  => 'boolean',
            'notes'       => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        $total = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['price']);
        $data['total']      = $total;
        $data['send_email'] = $request->boolean('send_email');
        $data['status']     = 'active';

        RecurringInvoice::create($data);

        return redirect()->route('recurring-invoices.index')->with('success', 'Recurring invoice created.');
    }

    public function show(RecurringInvoice $recurringInvoice)
    {
        $recurringInvoice->load('customer');
        return view('recurring-invoices.show', compact('recurringInvoice'));
    }

    public function edit(RecurringInvoice $recurringInvoice)
    {
        $customers = Customer::orderBy('name')->get();
        $products  = Product::where('is_active', true)->orderBy('name')->get();
        return view('recurring-invoices.edit', compact('recurringInvoice', 'customers', 'products'));
    }

    public function update(Request $request, RecurringInvoice $recurringInvoice)
    {
        $data = $request->validate([
            'status'     => 'required|in:active,paused,cancelled',
            'frequency'  => 'required|in:daily,weekly,monthly,yearly',
            'next_run_at'=> 'required|date',
            'send_email' => 'boolean',
            'notes'      => 'nullable|string',
        ]);

        $data['send_email'] = $request->boolean('send_email');
        $recurringInvoice->update($data);

        return redirect()->route('recurring-invoices.index')->with('success', 'Recurring invoice updated.');
    }

    public function destroy(RecurringInvoice $recurringInvoice)
    {
        $recurringInvoice->delete();
        return redirect()->route('recurring-invoices.index')->with('success', 'Recurring invoice deleted.');
    }
}
