<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Setting;
use App\Mail\QuotationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function __construct(protected \App\Services\QuotationService $quotationService) {}

    public function index(Request $request): View
    {
        $quotations = $this->quotationService->list($request->all());
        return view('quotations.index', compact('quotations'));
    }

    public function create(): View
    {
        $customers = Customer::where('is_active', true)->get();
        $products = \App\Models\Product::where('is_active', true)->get();
        $warehouses = \App\Models\Warehouse::where('is_active', true)->get();
        return view('quotations.create', compact('customers', 'products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'status' => 'required|string',
            'terms' => 'nullable|string',
        ]);

        // Stock validation
        foreach ($validated['products'] as $product) {
            $p = \App\Models\Product::find($product['product_id']);
            if ($p && $product['quantity'] > $p->stock_quantity) {
                return back()->withInput()->withErrors([
                    'products' => __('app.insufficient_stock') . ': ' . $p->name .
                        ' (' . __('app.available') . ': ' . $p->stock_quantity . ')',
                ]);
            }
        }

        // Initialize totals
        $subtotal = 0;
        $items = [];

        // Process items
        foreach ($validated['products'] as $product) {
            $itemSubtotal = $product['quantity'] * $product['price'];
            $subtotal += $itemSubtotal;
            
            $items[] = [
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'subtotal' => $itemSubtotal,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
            ];
        }

        // Calculate final totals
        $taxRate = $validated['tax'] ?? 0;
        $taxAmount = $subtotal * ($taxRate / 100);
        $shipping = $validated['shipping'] ?? 0;
        $discount = $validated['discount'] ?? 0;
        $totalAmount = $subtotal + $taxAmount + $shipping - $discount;

        // Prepare data for service
        $data = [
            'customer_id' => $validated['customer_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'date' => $validated['quotation_date'],
            'valid_until' => $validated['valid_until'],
            'status' => $validated['status'],
            'tax_amount' => $taxAmount,
            'discount_amount' => $discount,
            'shipping_cost' => $shipping,
            'total_amount' => $totalAmount,
            'notes' => $validated['terms'] ?? null,
            'items' => $items,
        ];

        $quotation = $this->quotationService->create($data);

        $autoSend = Setting::where('key', 'notify_quotation_created')->value('value');
        if ($autoSend === '1' && $quotation) {
            $quotation->load(['customer', 'items.product']);
            $email = $quotation->customer?->email;
            if ($email) {
                try {
                    Mail::to($email)->send(new QuotationMail($quotation));
                } catch (\Exception) {}
            }
        }

        return redirect()->route('quotations.index')->with('success', __('app.created_success'));
    }

    public function show(Quotation $quotation): View
    {
        $quotation->load(['customer', 'items.product']);
        return view('quotations.show', compact('quotation'));
    }

    public function print(Quotation $quotation): View
    {
        $quotation->load(['customer', 'items.product']);
        return view('quotations.print', compact('quotation'));
    }

    public function edit(Quotation $quotation): View
    {
        $customers = Customer::where('is_active', true)->get();
        $products = \App\Models\Product::where('is_active', true)->get();
        $warehouses = \App\Models\Warehouse::where('is_active', true)->get();
        return view('quotations.edit', compact('quotation', 'customers', 'products', 'warehouses'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        // TODO: Implement update logic if needed
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'valid_until' => 'required|date',
        ]);
        
        $quotation->update($validated);
        return redirect()->route('quotations.index')->with('success', __('app.updated_success'));
    }

    public function destroy(Quotation $quotation)
    {
        $this->quotationService->delete($quotation);
        return redirect()->route('quotations.index')->with('success', __('app.deleted_success'));
    }

    public function convert(Quotation $quotation)
    {
        $this->quotationService->convertToSale($quotation);
        return redirect()->route('sales.index')->with('success', __('app.converted_success'));
    }
}
