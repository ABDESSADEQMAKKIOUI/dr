<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockTransfer;
use App\Models\InventoryCount;
use App\Models\ProductWarehouse;
use App\Imports\OpeningStockImport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class StockController extends Controller
{
    public function warehouses(): View
    {
        $warehouses = Warehouse::withCount('products')->paginate(15);
        return view('stock.warehouses.index', compact('warehouses'));
    }

    public function warehousesCreate(): View
    {
        return view('stock.warehouses.create');
    }

    public function warehousesEdit(Warehouse $warehouse): View
    {
        return view('stock.warehouses.edit', compact('warehouse'));
    }

    public function adjustments(): View
    {
        $adjustments = StockAdjustment::with(['warehouse', 'user'])->paginate(15);
        return view('stock.adjustments.index', compact('adjustments'));
    }

    public function adjustmentsCreate(): View
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        return view('stock.adjustments.create', compact('warehouses', 'products'));
    }

    public function adjustmentsShow(StockAdjustment $adjustment): View
    {
        return view('stock.adjustments.show', compact('adjustment'));
    }

    public function adjustmentsStore(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:addition,subtraction,damage,loss',
            'reason' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.note' => 'nullable|string',
        ]);

        // Create the stock adjustment record
        $adjustment = StockAdjustment::create([
            'warehouse_id' => $validated['warehouse_id'],
            'date' => now()->toDateString(),
            'type' => $validated['type'],
            'reason' => $validated['reason'],
            'notes' => collect($validated['products'])->map(function ($p) {
                return 'Product ID: ' . $p['product_id'] . ', Qty: ' . $p['quantity'] . ($p['note'] ? ' - ' . $p['note'] : '');
            })->implode('; '),
            'user_id' => auth()->id(),
            'reference' => 'ADJ-' . date('Ymd') . '-' . rand(1000, 9999),
        ]);

        return redirect()->route('stock.adjustments.index')->with('success', __('app.created_success'));
    }

    public function adjustmentsEdit(StockAdjustment $adjustment): View
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        return view('stock.adjustments.edit', compact('adjustment', 'warehouses', 'products'));
    }

    public function adjustmentsUpdate(Request $request, StockAdjustment $adjustment)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|in:addition,subtraction,damage,loss',
            'reason' => 'required|string',
        ]);

        $adjustment->update($validated);

        return redirect()->route('stock.adjustments.show', $adjustment)->with('success', __('app.updated_success'));
    }

    public function adjustmentsDestroy(StockAdjustment $adjustment)
    {
        $adjustment->delete();
        return redirect()->route('stock.adjustments.index')->with('success', __('app.deleted_success'));
    }

    public function transfers(): View
    {
        $transfers = StockTransfer::with(['fromWarehouse', 'toWarehouse'])->paginate(15);
        return view('stock.transfers.index', compact('transfers'));
    }

    public function transfersCreate(): View
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        return view('stock.transfers.create', compact('warehouses', 'products'));
    }

    public function transfersShow(StockTransfer $transfer): View
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'items.product', 'user']);
        return view('stock.transfers.show', compact('transfer'));
    }

    public function transfersStore(Request $request)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        $transfer = StockTransfer::create([
            'from_warehouse_id' => $validated['from_warehouse_id'],
            'to_warehouse_id'   => $validated['to_warehouse_id'],
            'date'              => now()->toDateString(),
            'status'            => 'pending',
            'notes'             => $validated['notes'] ?? null,
            'user_id'           => auth()->id(),
            'reference'         => 'TRF-' . date('Ymd') . '-' . rand(1000, 9999),
        ]);

        // Save each product line as a StockTransferItem
        foreach ($validated['products'] as $line) {
            \App\Models\StockTransferItem::create([
                'stock_transfer_id' => $transfer->id,
                'product_id'        => $line['product_id'],
                'quantity'          => $line['quantity'],
            ]);
        }

        return redirect()->route('stock.transfers.index')->with('success', __('app.created_success'));
    }

    public function transfersEdit(StockTransfer $transfer): View
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('stock.transfers.edit', compact('transfer', 'warehouses'));
    }

    public function transfersUpdate(Request $request, StockTransfer $transfer)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id'   => 'required|exists:warehouses,id|different:from_warehouse_id',
            'status'            => 'required|in:pending,sent,received',
            'notes'             => 'nullable|string',
        ]);

        $previousStatus = $transfer->status;
        $transfer->update($validated);

        // Move stock when transfer is marked as received for the first time
        if ($validated['status'] === 'received' && $previousStatus !== 'received') {
            $items = \App\Models\StockTransferItem::where('stock_transfer_id', $transfer->id)->get();

            foreach ($items as $item) {
                // Deduct from source warehouse
                $src = \App\Models\ProductWarehouse::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $transfer->from_warehouse_id],
                    ['quantity' => 0]
                );
                $src->decrement('quantity', $item->quantity);

                // Add to destination warehouse
                $dst = \App\Models\ProductWarehouse::firstOrCreate(
                    ['product_id' => $item->product_id, 'warehouse_id' => $transfer->to_warehouse_id],
                    ['quantity' => 0]
                );
                $dst->increment('quantity', $item->quantity);

                // Update the product's global stock_quantity
                \App\Models\Product::where('id', $item->product_id)->update([
                    'stock_quantity' => \App\Models\ProductWarehouse::where('product_id', $item->product_id)->sum('quantity'),
                ]);
            }
        }

        return redirect()->route('stock.transfers.show', $transfer)->with('success', __('app.updated_success'));
    }

    public function transfersDestroy(StockTransfer $transfer)
    {
        $transfer->delete();
        return redirect()->route('stock.transfers.index')->with('success', __('app.deleted_success'));
    }

    public function inventory(Request $request): View
    {
        // Build query with filters
        $query = Product::with('category')->where('is_active', true);
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Search by name or SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        $products = $query->paginate(15)->withQueryString();
        
        // Also get ProductWarehouse records if they exist (filtered by warehouse)
        $inventoryQuery = \App\Models\ProductWarehouse::with(['product.category', 'warehouse']);
        if ($request->filled('warehouse')) {
            $inventoryQuery->where('warehouse_id', $request->warehouse);
        }
        $inventory = $inventoryQuery->paginate(15);
        
        // Calculate stats (based on filtered results if category filter applied)
        $statsQuery = Product::where('is_active', true);
        if ($request->filled('category')) {
            $statsQuery->where('category_id', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $statsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        $stats = [
            'total_products' => (clone $statsQuery)->count(),
            'total_value' => (clone $statsQuery)->selectRaw('SUM(stock_quantity * sale_price) as total')->value('total') ?? 0,
            'low_stock' => (clone $statsQuery)->whereColumn('stock_quantity', '<=', 'stock_alert')->where('stock_quantity', '>', 0)->count(),
            'out_of_stock' => (clone $statsQuery)->where('stock_quantity', '<=', 0)->count(),
        ];
        
        $warehouses = Warehouse::where('is_active', true)->get();
        $categories = \App\Models\Category::all();
        
        return view('stock.inventory.index', compact('inventory', 'products', 'stats', 'warehouses', 'categories'));
    }

    public function inventoryCount(): View
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('stock.inventory.count', compact('warehouses'));
    }

    /** GET /stock/inventory/products-by-warehouse?warehouse_id=X */
    public function productsByWarehouse(Request $request): \Illuminate\Http\JsonResponse
    {
        $warehouseId = $request->query('warehouse_id');

        if (!$warehouseId) {
            return response()->json([]);
        }

        $rows = \App\Models\ProductWarehouse::with(['product.unit'])
            ->where('warehouse_id', $warehouseId)
            ->get()
            ->map(fn ($pw) => [
                'id'           => $pw->product_id,
                'name'         => $pw->product->name ?? '—',
                'sku'          => $pw->product->sku ?? '—',
                'unit'         => $pw->product->unit->short_name ?? '',
                'system_stock' => (int) $pw->quantity,
            ]);

        return response()->json($rows);
    }

    public function storeInventoryCount(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'count_date'   => 'required|date',
            'counts'       => 'required|array',
            'counts.*'     => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string',
        ]);

        // Snapshot current system stock BEFORE any changes
        $expectedData = \App\Models\ProductWarehouse::where('warehouse_id', $validated['warehouse_id'])
            ->pluck('quantity', 'product_id')
            ->toArray();

        // Only keep entries with a value
        $countedData = array_filter($validated['counts'], fn($v) => $v !== null && $v !== '');

        $count = \App\Models\InventoryCount::create([
            'warehouse_id'  => $validated['warehouse_id'],
            'date'          => $validated['count_date'],
            'status'        => 'completed',
            'notes'         => $validated['notes'],
            'user_id'       => auth()->id(),
            'reference'     => 'CNT-' . date('Ymd') . '-' . rand(1000, 9999),
            'counted_data'  => json_encode($countedData),
            'expected_data' => json_encode($expectedData),
        ]);

        // Stock is NOT updated yet — user must review and approve from the detail page
        return redirect()->route('stock.inventory.count-show', $count)->with('success', __('app.stock_count_saved') ?? 'Stock count saved. Review discrepancies and approve to adjust stock.');
    }

    public function alerts(): View
    {
        // Out of stock: quantity is 0 or negative
        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();

        // Low stock: above 0 but at or below the alert threshold
        $lowStockCount = Product::where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'stock_alert')
            ->count();

        // All alert products combined (out of stock OR low stock)
        $alerts = Product::where(function ($q) {
            $q->where('stock_quantity', '<=', 0)
              ->orWhereColumn('stock_quantity', '<=', 'stock_alert');
        })->orderBy('stock_quantity')->get();

        $expiringSoonCount = 0; // expiry tracking not implemented yet

        return view('stock.alerts.index', compact('alerts', 'outOfStockCount', 'lowStockCount', 'expiringSoonCount'));
    }

    // ─── Phase 4 — Opening Stock Import ────────────────────────

    public function openingImport(): View
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('stock.opening-import', compact('warehouses'));
    }

    public function storeOpeningImport(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls|max:5120']);

        $import = new OpeningStockImport();
        Excel::import($import, $request->file('file'));

        $failures = $import->failures();
        $msg = $import->getImportedCount() . ' products stock imported, ' . $import->getSkippedCount() . ' rows skipped.';

        if ($failures->count()) {
            $msg .= ' ' . $failures->count() . ' rows failed validation.';
        }

        return redirect()->route('stock.opening-import')->with('success', $msg);
    }

    public function openingImportSample()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['product_sku', 'warehouse_id', 'quantity', 'cost_price']);
            fputcsv($handle, ['PRD-001', '1', '100', '25.50']);
            fputcsv($handle, ['PRD-002', '1', '50', '12.00']);
            fclose($handle);
        }, 'opening_stock_template.csv', ['Content-Type' => 'text/csv']);
    }

    // ─── Phase 4 — Complete Stock Count Workflow ───────────────

    public function inventoryCountList(): View
    {
        $counts = InventoryCount::with(['warehouse', 'user'])
            ->latest()
            ->paginate(15);
        return view('stock.inventory.list', compact('counts'));
    }

    public function inventoryCountShow(InventoryCount $count): View
    {
        $count->load('warehouse', 'user');

        // Counted quantities keyed by product_id
        $counted = json_decode($count->counted_data ?? '{}', true) ?: [];

        // Expected (system stock at count creation) keyed by product_id
        $expected = json_decode($count->expected_data ?? '{}', true) ?: [];

        // Load all products in this warehouse (for names/SKUs)
        $warehouseProducts = ProductWarehouse::with('product')
            ->where('warehouse_id', $count->warehouse_id)
            ->get()
            ->keyBy('product_id');

        // Merge: all products that appear in either expected or counted
        $productIds = collect(array_keys($expected) + array_keys($counted))->unique();

        // If no expected_data (old records), fall back to current warehouse quantities
        if (empty($expected)) {
            $expected = $warehouseProducts->mapWithKeys(fn($pw) => [$pw->product_id => $pw->quantity])->toArray();
        }

        $discrepancies = $productIds->map(function ($productId) use ($expected, $counted, $warehouseProducts) {
            $pw         = $warehouseProducts->get($productId);
            $product    = $pw?->product;
            $systemQty  = (int) ($expected[$productId] ?? 0);
            $countedQty = isset($counted[$productId]) ? (int) $counted[$productId] : null;

            return [
                'product'     => $product,
                'expected'    => $systemQty,
                'counted'     => $countedQty,
                'discrepancy' => $countedQty !== null ? ($countedQty - $systemQty) : null,
            ];
        })->filter(fn($d) => $d['product'] !== null)->values();

        return view('stock.inventory.show', compact('count', 'discrepancies'));
    }

    public function inventoryCountStart(): View
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('stock.inventory.start', compact('warehouses'));
    }

    public function inventoryCountCreate(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'notes'        => 'nullable|string',
        ]);

        $expectedData = ProductWarehouse::where('warehouse_id', $validated['warehouse_id'])
            ->pluck('quantity', 'product_id')
            ->toArray();

        $count = InventoryCount::create([
            'warehouse_id'  => $validated['warehouse_id'],
            'date'          => now()->toDateString(),
            'status'        => 'draft',
            'notes'         => $validated['notes'] ?? null,
            'user_id'       => auth()->id(),
            'reference'     => 'CNT-' . date('Ymd') . '-' . rand(1000, 9999),
            'expected_data' => json_encode($expectedData),
        ]);

        return redirect()->route('stock.inventory.count-entry', $count)
            ->with('success', 'Stock count started. Enter physical quantities below.');
    }

    public function inventoryCountEntry(InventoryCount $count): View
    {
        $products = ProductWarehouse::with('product')
            ->where('warehouse_id', $count->warehouse_id)
            ->get();

        $counted = json_decode($count->counted_data ?? '{}', true) ?: [];

        return view('stock.inventory.entry', compact('count', 'products', 'counted'));
    }

    public function inventoryCountSave(Request $request, InventoryCount $count)
    {
        $validated = $request->validate([
            'counts'   => 'required|array',
            'counts.*' => 'nullable|integer|min:0',
        ]);

        $count->update([
            'counted_data' => json_encode($validated['counts']),
            'status'       => 'counting',
        ]);

        return redirect()->route('stock.inventory.count-show', $count)
            ->with('success', 'Quantities saved.');
    }

    public function inventoryCountComplete(InventoryCount $count)
    {
        $count->update(['status' => 'completed']);
        return redirect()->route('stock.inventory.count-show', $count)
            ->with('success', 'Count marked as completed. Review discrepancies and approve to adjust stock.');
    }

    public function inventoryCountApprove(InventoryCount $count)
    {
        if ($count->status !== 'completed') {
            return back()->with('error', 'Count must be completed before approving.');
        }

        $counted  = json_decode($count->counted_data  ?? '{}', true) ?: [];
        $expected = json_decode($count->expected_data ?? '{}', true) ?: [];

        foreach ($counted as $productId => $qty) {
            $qty = (int) $qty;
            $systemQty = (int) ($expected[$productId] ?? 0);
            $diff = $qty - $systemQty;

            $pw = ProductWarehouse::firstOrCreate(
                ['product_id' => $productId, 'warehouse_id' => $count->warehouse_id],
                ['quantity' => 0]
            );

            if ($diff == 0) continue;

            // Create adjustment
            $adjustment = StockAdjustment::create([
                'warehouse_id' => $count->warehouse_id,
                'date'         => now(),
                'reference'    => 'CNT-ADJ-' . $count->reference,
                'type'         => $diff > 0 ? 'addition' : 'subtraction',
                'notes'        => 'Auto-adjustment from Stock Count #' . $count->reference,
                'user_id'      => auth()->id(),
            ]);

            StockAdjustmentItem::create([
                'stock_adjustment_id' => $adjustment->id,
                'product_id'          => $productId,
                'quantity'            => abs($diff),
                'type'                => $diff > 0 ? 'addition' : 'subtraction',
            ]);

            // Update actual stock
            $pw->update(['quantity' => $qty]);

            // Also update product stock_quantity
            $product = Product::find($productId);
            if ($product) {
                $totalStock = ProductWarehouse::where('product_id', $productId)->sum('quantity');
                $product->update(['stock_quantity' => $totalStock]);
            }
        }

        $count->update(['status' => 'adjusted']);

        return redirect()->route('stock.inventory.count-show', $count)
            ->with('success', 'Stock adjusted to match physical count.');
    }

    public function inventoryCountDestroy(InventoryCount $count)
    {
        $count->delete();
        return redirect()->route('stock.inventory.counts')
            ->with('success', __('app.stock_count_deleted') ?? 'Stock count deleted.');
    }
}
