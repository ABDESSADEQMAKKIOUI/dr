<?php

namespace App\Http\Controllers;

use App\Services\SaleService;
use App\Models\Sale;
use App\Models\SaleDraft;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(protected SaleService $saleService) {}

    public function index(Request $request): View
    {
        $sales = $this->saleService->list($request->all());
        return view('sales.index', compact('sales'));
    }

    public function create(): View
    {
        $customers = Customer::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('sales.create', compact('customers', 'products', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sale_date' => 'nullable|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'status' => 'required|string|in:draft,confirmed,delivered',
            'notes' => 'nullable|string',
        ]);

        // Initialize totals
        $subtotal = 0;
        $items = [];

        // Process items and calculate subtotal
        foreach ($validated['products'] as $productData) {
            $productModel = Product::find($productData['product_id']);
            $unitCost = $productModel ? $productModel->cost_price : 0;
            
            $itemSubtotal = $productData['quantity'] * $productData['price'];
            $subtotal += $itemSubtotal;
            
            $items[] = [
                'product_id' => $productData['product_id'],
                'quantity' => $productData['quantity'],
                'price' => $productData['price'],
                'unit_cost' => $unitCost,
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
            'date' => $validated['sale_date'] ?? now(),
            'status' => $validated['status'],
            'tax_amount' => $taxAmount,
            'discount_amount' => $discount,
            'shipping_cost' => $shipping,
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'] ?? null,
            'items' => $items,
        ];

        $this->saleService->create($data);
        return redirect()->route('sales.index')->with('success', __('app.created_success'));
    }

    public function show(Sale $sale): View
    {
        $sale->load('customer', 'warehouse', 'items.product', 'payments', 'invoice');
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale): View
    {
        $customers = Customer::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('sales.edit', compact('sale', 'customers', 'products', 'warehouses'));
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'customer_id'  => 'nullable|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sale_date'    => 'nullable|date',
            'products'     => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity'   => 'required|numeric|min:1',
            'products.*.price'      => 'required|numeric|min:0',
            'tax'      => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'status'   => 'required|string|in:draft,confirmed,delivered',
            'notes'    => 'nullable|string',
        ]);

        $subtotal = 0;
        $items = [];

        foreach ($validated['products'] as $productData) {
            $productModel = Product::find($productData['product_id']);
            $itemSubtotal = $productData['quantity'] * $productData['price'];
            $subtotal += $itemSubtotal;
            $items[] = [
                'product_id'      => $productData['product_id'],
                'quantity'        => $productData['quantity'],
                'price'           => $productData['price'],
                'unit_cost'       => $productModel ? $productModel->cost_price : 0,
                'subtotal'        => $itemSubtotal,
                'tax_rate'        => 0,
                'tax_amount'      => 0,
                'discount_amount' => 0,
            ];
        }

        $taxRate     = $validated['tax'] ?? 0;
        $taxAmount   = $subtotal * ($taxRate / 100);
        $shipping    = $validated['shipping'] ?? 0;
        $discount    = $validated['discount'] ?? 0;
        $totalAmount = $subtotal + $taxAmount + $shipping - $discount;

        $sale->update([
            'customer_id'     => $validated['customer_id'],
            'warehouse_id'    => $validated['warehouse_id'],
            'date'            => $validated['sale_date'] ?? $sale->date,
            'status'          => $validated['status'],
            'tax_amount'      => $taxAmount,
            'discount_amount' => $discount,
            'shipping_cost'   => $shipping,
            'total_amount'    => $totalAmount,
            'notes'           => $validated['notes'] ?? null,
        ]);

        $oldStatus = $sale->status;
        $newStatus = $validated['status'];

        $sale->items()->delete();
        foreach ($items as $item) {
            $sale->items()->create($item);
        }

        // Deduct stock if status is being set to confirmed/delivered for the first time
        if (!in_array($oldStatus, ['confirmed', 'delivered']) && in_array($newStatus, ['confirmed', 'delivered'])) {
            $sale->load('items.product');
            foreach ($sale->items as $saleItem) {
                $pw = \App\Models\ProductWarehouse::where('product_id', $saleItem->product_id)
                    ->where('warehouse_id', $sale->warehouse_id)
                    ->where('product_variant_id', $saleItem->product_variant_id)
                    ->first();
                if ($pw) {
                    $pw->decrement('quantity', $saleItem->quantity);
                }
                if ($saleItem->product) {
                    $totalStock = \App\Models\ProductWarehouse::where('product_id', $saleItem->product_id)->sum('quantity');
                    $saleItem->product->update(['stock_quantity' => $totalStock]);
                }
            }
        }

        return redirect()->route('sales.show', $sale->id)->with('success', __('app.updated_success'));
    }

    public function print(Sale $sale): View
    {
        $sale->load('customer', 'warehouse', 'items.product', 'invoice');
        return view('sales.print', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        $this->saleService->delete($sale);
        return redirect()->route('sales.index')->with('success', __('app.deleted_success'));
    }

    public function pos(): View
    {
        $products   = Product::where('is_active', true)->with('category')->get();
        $customers  = Customer::where('is_active', true)->get();
        $categories = Category::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $defaultWarehouseId = Setting::get('pos_default_warehouse', null)
            ?? optional($warehouses->first())->id;
        $draftsCount = SaleDraft::where('user_id', auth()->id())->count();
        return view('sales.pos.index', compact(
            'products', 'customers', 'categories', 'warehouses',
            'defaultWarehouseId', 'draftsCount'
        ));
    }

    /**
     * POS-specific store — accepts cart format from the POS interface.
     */
    public function posStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id'  => 'required|exists:warehouses,id',
            'customer_id'   => 'nullable|exists:customers,id',
            'items'         => 'required|array|min:1',
            'items.*.id'    => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price'    => 'required|numeric|min:0',
            'discount'      => 'nullable|numeric|min:0',
            'payment_method'=> 'nullable|string',
        ]);

        $subtotal = 0;
        $items = [];
        $taxRate = Setting::get('default_tax_rate', 0);

        foreach ($validated['items'] as $item) {
            $productModel = Product::find($item['id']);
            $unitCost     = $productModel ? $productModel->cost_price : 0;
            $itemSubtotal = $item['quantity'] * $item['price'];
            $subtotal    += $itemSubtotal;
            $itemTaxAmount = $itemSubtotal * ($taxRate / 100);

            $items[] = [
                'product_id'      => $item['id'],
                'quantity'        => $item['quantity'],
                'price'           => $item['price'],
                'unit_cost'       => $unitCost,
                'subtotal'        => $itemSubtotal,
                'tax_rate'        => $taxRate,
                'tax_amount'      => $itemTaxAmount,
                'discount_amount' => 0,
            ];
        }

        $taxAmount   = $subtotal * ($taxRate / 100);
        $discount    = $validated['discount'] ?? 0;
        $totalAmount = $subtotal + $taxAmount - $discount;

        $data = [
            'customer_id'     => $validated['customer_id'] ?? null,
            'warehouse_id'    => $validated['warehouse_id'],
            'date'            => now(),
            'status'          => 'confirmed',
            'tax_amount'      => $taxAmount,
            'discount_amount' => $discount,
            'shipping_cost'   => 0,
            'total_amount'    => $totalAmount,
            'notes'           => null,
            'items'           => $items,
        ];

        $sale = $this->saleService->create($data);
        $autoPrint = Setting::get('pos_auto_print', 'false') === 'true';

        return response()->json([
            'success'    => true,
            'sale_id'    => $sale->id,
            'auto_print' => $autoPrint,
            'receipt_url'=> route('sales.receipt', $sale->id),
        ]);
    }

    /**
     * POS receipt view.
     */
    public function receipt(Sale $sale): View
    {
        $sale->load('customer', 'warehouse', 'items.product', 'payments');
        $settings = Setting::all()->pluck('value', 'key');
        $receiptSize = $settings['pos_receipt_size'] ?? 'a4';
        $view = $receiptSize === 'a4' ? 'receipts.a4' : 'receipts.thermal';
        return view($view, compact('sale', 'settings'));
    }

    public function returns(): View
    {
        $returns = \App\Models\SaleReturn::with(['sale.customer', 'items'])->latest()->paginate(15);
        return view('sales.returns.index', compact('returns'));
    }

    public function createReturn(): View
    {
        $sales = Sale::with(['customer', 'items.product'])->latest()->get();
        $products = Product::where('is_active', true)->get();
        return view('sales.returns.create', compact('sales', 'products'));
    }

    public function storeReturn(Request $request)
    {
        $validated = $request->validate([
            'sale_id'    => 'required|exists:sales,id',
            'return_date'=> 'required|date',
            'reason'     => 'required|string',
            'items'      => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0',
        ]);

        $sale = Sale::with('items')->findOrFail($validated['sale_id']);
        $items = [];

        foreach ($validated['items'] as $itemData) {
            if ($itemData['quantity'] <= 0) continue;

            // Verify product belongs to this sale
            $saleItem = $sale->items->where('product_id', $itemData['product_id'])->first();
            if (!$saleItem) {
                return back()->withInput()->withErrors([
                    'items' => 'Product #' . $itemData['product_id'] . ' does not belong to the selected sale.',
                ]);
            }

            // Verify return quantity does not exceed sold quantity
            if ($itemData['quantity'] > $saleItem->quantity) {
                $product = Product::find($itemData['product_id']);
                return back()->withInput()->withErrors([
                    'items' => ($product->name ?? 'Product') . ': return quantity (' . $itemData['quantity'] . ') exceeds sold quantity (' . $saleItem->quantity . ').',
                ]);
            }

            $items[] = [
                'product_id' => $itemData['product_id'],
                'quantity'   => $itemData['quantity'],
                'price'      => $saleItem->price,
            ];
        }

        if (empty($items)) {
            return back()->withInput()->withErrors(['items' => __('app.no_items_selected')]);
        }

        $data = [
            'sale_id'     => $validated['sale_id'],
            'return_date' => $validated['return_date'],
            'reason'      => $validated['reason'],
            'items'       => $items,
        ];

        $this->saleService->createReturn($data);
        return redirect()->route('sales.returns.index')->with('success', __('app.created_success'));
    }

    public function updateReturnStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,approved,rejected']);

        $return = \App\Models\SaleReturn::with('items.product')->findOrFail($id);
        $oldStatus = $return->status;
        $newStatus = $request->status;

        $return->update(['status' => $newStatus]);

        // Restore stock when approved for the first time
        if ($oldStatus !== 'approved' && $newStatus === 'approved') {
            foreach ($return->items as $item) {
                $pw = \App\Models\ProductWarehouse::where('product_id', $item->product_id)
                    ->where('warehouse_id', $return->warehouse_id)
                    ->where('product_variant_id', null)
                    ->first();

                if ($pw) {
                    $pw->increment('quantity', $item->quantity);
                } else {
                    \App\Models\ProductWarehouse::create([
                        'product_id'         => $item->product_id,
                        'warehouse_id'        => $return->warehouse_id,
                        'product_variant_id'  => null,
                        'quantity'            => $item->quantity,
                    ]);
                }

                if ($item->product) {
                    $total = \App\Models\ProductWarehouse::where('product_id', $item->product_id)->sum('quantity');
                    $item->product->update(['stock_quantity' => $total]);
                }
            }

            // Check if all sold quantities have been returned across all approved returns
            $sale = $return->sale()->with('items')->first();
            if ($sale) {
                $approvedReturns = \App\Models\SaleReturn::where('sale_id', $sale->id)
                    ->where('status', 'approved')
                    ->with('items')
                    ->get();

                $returnedQty = []; // product_id => total returned qty
                foreach ($approvedReturns as $r) {
                    foreach ($r->items as $ri) {
                        $returnedQty[$ri->product_id] = ($returnedQty[$ri->product_id] ?? 0) + $ri->quantity;
                    }
                }

                $fullyReturned = true;
                foreach ($sale->items as $saleItem) {
                    $returned = $returnedQty[$saleItem->product_id] ?? 0;
                    if ($returned < $saleItem->quantity) {
                        $fullyReturned = false;
                        break;
                    }
                }

                if ($fullyReturned) {
                    $sale->update(['status' => 'returned']);
                }
            }
        }

        return redirect()->route('sales.returns.show', $id)->with('success', __('app.updated_success'));
    }

    public function showReturn($id): View
    {
        $return = \App\Models\SaleReturn::with(['sale.customer', 'items.product', 'user'])->findOrFail($id);
        return view('sales.returns.show', compact('return'));
    }
}
