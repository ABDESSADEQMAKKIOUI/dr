<?php

namespace App\Http\Controllers;

use App\Services\PurchaseService;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(protected PurchaseService $purchaseService) {}

    public function index(Request $request): View
    {
        $purchases = $this->purchaseService->list($request->all());
        return view('purchases.index', compact('purchases'));
    }

    public function create(): View
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('purchases.create', compact('suppliers', 'products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'purchase_date' => 'nullable|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Initialize totals
        $subtotal = 0;
        $items = [];

        // Process items and calculate subtotal
        foreach ($validated['products'] as $product) {
            $itemSubtotal = $product['quantity'] * $product['price'];
            $subtotal += $itemSubtotal;
            
            $items[] = [
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'price' => $product['price'],
                'subtotal' => $itemSubtotal,
                'tax_rate' => 0, // Default to 0 for now as it's not in the form
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
            'supplier_id' => $validated['supplier_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'date' => $validated['purchase_date'] ?? now(),
            'tax_amount' => $taxAmount,
            'discount_amount' => $discount,
            'shipping_cost' => $shipping,
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'],
            'items' => $items,
        ];

        $this->purchaseService->create($data);
        return redirect()->route('purchases.index')->with('success', __('app.created_success'));
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load('supplier', 'warehouse', 'items.product', 'payments');
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase): View
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('purchases.edit', compact('purchase', 'suppliers', 'products', 'warehouses'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'purchase_date' => 'nullable|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Initialize totals
        $subtotal = 0;
        $items = [];

        // Process items and calculate subtotal
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
            'supplier_id' => $validated['supplier_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'date' => $validated['purchase_date'] ?? now(),
            'tax_amount' => $taxAmount,
            'discount_amount' => $discount,
            'shipping_cost' => $shipping,
            'total_amount' => $totalAmount,
            'paid_amount' => $validated['paid_amount'] ?? $purchase->paid_amount,
            'notes' => $validated['notes'],
            'items' => $items,
        ];

        $this->purchaseService->update($purchase, $data);
        return redirect()->route('purchases.index')->with('success', __('app.updated_success'));
    }

    public function destroy(Purchase $purchase)
    {
        $this->purchaseService->delete($purchase);
        return redirect()->route('purchases.index')->with('success', __('app.deleted_success'));
    }

    public function returns(): View
    {
        $returns = \App\Models\PurchaseReturn::with(['purchase.supplier'])->withCount('items')->latest()->paginate(15);
        $suppliers = Supplier::where('is_active', true)->get();
        return view('purchases.returns.index', compact('returns', 'suppliers'));
    }

    public function createReturn(): View
    {
        $purchases = Purchase::with(['supplier', 'items.product'])->latest()->get();
        $products = Product::where('is_active', true)->get();
        return view('purchases.returns.create', compact('purchases', 'products'));
    }

    public function storeReturn(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'return_date' => 'required|date',
            'reason' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0',
        ]);

        // Filter out 0 quantity items and get prices
        $itemsToReturn = [];
        $purchase = Purchase::with('items')->findOrFail($validated['purchase_id']);

        foreach ($validated['items'] as $item) {
            if ($item['quantity'] > 0) {
                // Find original price
                $originalItem = $purchase->items->where('product_id', $item['product_id'])->first();
                $price = $originalItem ? $originalItem->price : 0;
                
                $itemsToReturn[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $price,
                ];
            }
        }

        if (empty($itemsToReturn)) {
            return back()->with('error', __('app.at_least_one_item_required'));
        }

        $data = [
            'purchase_id' => $validated['purchase_id'],
            'return_date' => $validated['return_date'],
            'reason' => $validated['reason'],
            'items' => $itemsToReturn,
        ];

        $this->purchaseService->createReturn($data);

        return redirect()->route('purchases.returns.index')->with('success', __('app.created_success'));
    }

    public function showReturn($id): View
    {
        $return = \App\Models\PurchaseReturn::with(['purchase.supplier', 'items.product', 'user'])->findOrFail($id);
        return view('purchases.returns.show', compact('return'));
    }

    public function applyReturn($id)
    {
        $return = \App\Models\PurchaseReturn::with('items.product')->findOrFail($id);
        try {
            $this->purchaseService->applyReturn($return);
            return redirect()->route('purchases.returns.show', $id)
                ->with('success', __('app.return_applied_success') ?? 'Return applied. Stock has been updated.');
        } catch (\Exception $e) {
            return redirect()->route('purchases.returns.show', $id)
                ->with('error', $e->getMessage());
        }
    }

    /** POST /purchases/import */
    public function import(\Illuminate\Http\Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls|max:5120']);

        $import = new \App\Imports\PurchaseImport();
        \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

        $msg = "Imported {$import->getImportedCount()} purchase(s). Skipped {$import->getSkippedCount()}.";

        return redirect()->route('purchases.index')->with('success', $msg);
    }

    /** GET /purchases/sample-csv */
    public function downloadSample()
    {
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="purchases_sample.csv"'];
        $callback = function () {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['reference', 'supplier_name', 'warehouse_name', 'product_sku', 'quantity', 'unit_cost', 'paid_amount', 'date', 'notes']);
            fputcsv($f, ['PO-2024-001', 'Supplier Name', 'Main Warehouse', 'SKU-001', '10', '50.00', '500.00', date('Y-m-d'), 'Sample note']);
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }
}
