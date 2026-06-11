<?php

namespace App\Http\Controllers;

use App\Models\ProductSerial;
use App\Services\ProductService;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\ProductWarehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index(Request $request): View
    {
        $products = $this->productService->list($request->all());

        $inStockCount   = Product::where('stock_quantity', '>', 0)
                            ->whereColumn('stock_quantity', '>', 'stock_alert')->count();
        $lowStockCount  = Product::where('stock_quantity', '>', 0)
                            ->whereColumn('stock_quantity', '<=', 'stock_alert')->count();
        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();

        $categories = Category::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();

        return view('products.index', compact(
            'products', 'inStockCount', 'lowStockCount', 'outOfStockCount',
            'categories', 'brands'
        ));
    }

    public function create(): View
    {
        $categories = Category::all();
        $brands = Brand::all();
        $units = Unit::all();
        $warehouses = Warehouse::where('is_active', true)->get();
        $productNames = Product::groupBy('name')->orderBy('name')->pluck('name');

        return view('products.create', compact('categories', 'brands', 'units', 'warehouses', 'productNames'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products',
            'barcode' => 'nullable|string|unique:products',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'nullable|exists:units,id',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock_alert' => 'nullable|integer|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'image' => 'nullable|image|max:2048',
        ]);

        $warehouseId = $validated['warehouse_id'] ?? null;
        unset($validated['warehouse_id']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = $this->productService->create($validated);

        // If warehouse is selected, create ProductWarehouse record
        if ($warehouseId && $product) {
            ProductWarehouse::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'quantity' => $validated['stock_quantity'] ?? 0,
            ]);
        }

        return redirect()->route('products.index')->with('success', __('app.created_success'));
    }

    public function show(Product $product): View
    {
        $product->load([
            'category', 'brand', 'unit', 'saleUnit', 'purchaseUnit',
            'variants', 'images', 'warehouses.warehouse', 'creator',
        ]);

        // Recent sales of this product (last 10)
        $recentSales = \App\Models\SaleItem::with('sale.customer')
            ->where('product_id', $product->id)
            ->latest()
            ->limit(10)
            ->get();

        // Recent purchases (last 10)
        $recentPurchases = \App\Models\PurchaseItem::with('purchase.supplier')
            ->where('product_id', $product->id)
            ->latest()
            ->limit(10)
            ->get();

        // Totals
        $totalSold     = \App\Models\SaleItem::where('product_id', $product->id)->sum('quantity');
        $totalPurchased = \App\Models\PurchaseItem::where('product_id', $product->id)->sum('quantity');
        $totalRevenue  = \App\Models\SaleItem::where('product_id', $product->id)->sum('subtotal');

        return view('products.show', compact(
            'product', 'recentSales', 'recentPurchases',
            'totalSold', 'totalPurchased', 'totalRevenue'
        ));
    }

    public function edit(Product $product): View
    {
        $categories = Category::all();
        $brands = Brand::all();
        $units = Unit::all();
        return view('products.edit', compact('product', 'categories', 'brands', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'unit_id' => 'nullable|exists:units,id',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock_alert' => 'nullable|integer|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($product->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $this->productService->update($product, $validated);
        return redirect()->route('products.index')->with('success', __('app.updated_success'));
    }

    public function destroy(Product $product)
    {
        $this->productService->delete($product);
        return redirect()->route('products.index')->with('success', __('app.deleted_success'));
    }

    public function categories(): View
    {
        $categories = Category::withCount('products')->paginate(15);
        return view('products.categories.index', compact('categories'));
    }

    public function brands(): View
    {
        $brands = Brand::withCount('products')->paginate(15);
        return view('products.brands.index', compact('brands'));
    }

    public function units(): View
    {
        $units = Unit::paginate(15);
        return view('products.units.index', compact('units'));
    }

    /** GET /products/{product}/serials — IMEI / Serial number list */
    public function serials(Request $request, Product $product): View
    {
        $query = ProductSerial::with('warehouse', 'saleItem', 'purchaseItem')
            ->where('product_id', $product->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $query->where('serial_number', 'like', '%' . $request->q . '%');
        }

        $serials   = $query->latest()->paginate(50);
        $available = ProductSerial::where('product_id', $product->id)->where('status', 'available')->count();
        $sold      = ProductSerial::where('product_id', $product->id)->where('status', 'sold')->count();
        $returned  = ProductSerial::where('product_id', $product->id)->where('status', 'returned')->count();

        return view('products.serials', compact('product', 'serials', 'available', 'sold', 'returned'));
    }

    /**
     * GET /api/check-duplicate?type=sku&value=XXX[&exclude_id=5]
     * Returns {exists: bool}
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $type      = $request->query('type', 'sku');
        $value     = $request->query('value', '');
        $excludeId = $request->query('exclude_id');

        if (empty($value)) {
            return response()->json(['exists' => false]);
        }

        $map = [
            'sku'      => ['products', 'sku'],
            'barcode'  => ['products', 'barcode'],
            'email_c'  => ['customers', 'email'],
            'phone_c'  => ['customers', 'phone'],
            'email_s'  => ['suppliers', 'email'],
            'phone_s'  => ['suppliers', 'phone'],
        ];

        if (!isset($map[$type])) {
            return response()->json(['exists' => false]);
        }

        [$table, $column] = $map[$type];

        $query = \Illuminate\Support\Facades\DB::table($table)->where($column, $value);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return response()->json(['exists' => $query->exists()]);
    }
}
