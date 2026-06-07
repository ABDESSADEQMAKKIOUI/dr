<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function salesReport(array $filters = []): array
    {
        $query = Sale::with('customer', 'items.product');

        if (isset($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        $sales = $query->get();

        return [
            'total_sales' => $sales->sum('total_amount'),
            'total_paid' => $sales->sum('paid_amount'),
            'count' => $sales->count(),
            'by_customer' => $sales->groupBy('customer_id')->map(fn($group) => [
                'customer' => $group->first()->customer->name,
                'total' => $group->sum('total_amount'),
                'count' => $group->count(),
            ])->values(),
            'by_product' => DB::table('sale_items')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->selectRaw('products.name, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as total')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
        ];
    }

    public function purchaseReport(array $filters = []): array
    {
        $query = Purchase::with('supplier');

        if (isset($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        $purchases = $query->get();

        return [
            'total_purchases' => $purchases->sum('total_amount'),
            'total_paid' => $purchases->sum('paid_amount'),
            'count' => $purchases->count(),
        ];
    }

    public function profitLossReport(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? now()->endOfMonth();

        $revenue = Sale::whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $purchases = Purchase::whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->sum('total_amount');

        $expenses = Expense::whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->sum('amount');

        $profit = $revenue - $purchases - $expenses;

        return [
            'revenue' => $revenue,
            'cost_of_goods' => $purchases,
            'expenses' => $expenses,
            'gross_profit' => $revenue - $purchases,
            'net_profit' => $profit,
            'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
        ];
    }

    public function stockReport(): array
    {
        $products = Product::with('category')->get();

        return [
            'total_products' => $products->count(),
            'total_stock_value' => $products->sum(fn($p) => $p->stock_quantity * $p->cost_price),
            'low_stock_count' => $products->filter(fn($p) => $p->stock_quantity <= $p->stock_alert)->count(),
            'out_of_stock_count' => $products->where('stock_quantity', 0)->count(),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 5.6 — FIFO / Average Cost Profit Calculation
    // ─────────────────────────────────────────────────────────────

    /**
     * Calculate Cost of Goods Sold using the specified method.
     *
     * @param  string  $method  'fifo' | 'average' | 'cogs'
     * @param  string  $dateFrom
     * @param  string  $dateTo
     * @return array   ['cogs' => float, 'revenue' => float, 'gross_profit' => float, 'method' => string]
     */
    public function getCostOfGoodsSold(string $method, string $dateFrom, string $dateTo): array
    {
        $revenue = Sale::whereBetween('date', [$dateFrom, $dateTo])
            ->sum('total_amount');

        $cogs = match ($method) {
            'fifo'    => $this->cogsByFifo($dateFrom, $dateTo),
            'average' => $this->cogsByAverage($dateFrom, $dateTo),
            default   => $this->cogsByStandardCost($dateFrom, $dateTo),
        };

        return [
            'method'       => $method,
            'cogs'         => $cogs,
            'revenue'      => $revenue,
            'gross_profit' => $revenue - $cogs,
            'margin'       => $revenue > 0 ? round(($revenue - $cogs) / $revenue * 100, 2) : 0,
        ];
    }

    /**
     * FIFO: match each sold unit to the oldest available purchase batch per product.
     */
    private function cogsByFifo(string $dateFrom, string $dateTo): float
    {
        // Load sold items in the period grouped by product
        $soldItems = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.date', [$dateFrom, $dateTo])
            ->selectRaw('sale_items.product_id, SUM(sale_items.quantity) as qty_sold')
            ->groupBy('sale_items.product_id')
            ->get()
            ->keyBy('product_id');

        // Load all purchase batches (chronological) for each product, up to sale date
        $purchaseBatches = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('purchases.date', '<=', $dateTo)
            ->selectRaw('purchase_items.product_id, purchase_items.quantity, purchase_items.price, purchases.date')
            ->orderBy('purchases.date')
            ->get()
            ->groupBy('product_id');

        $totalCogs = 0.0;

        foreach ($soldItems as $productId => $soldRow) {
            $remainingToMatch = (float) $soldRow->qty_sold;
            $batches          = $purchaseBatches[$productId] ?? collect();

            foreach ($batches as $batch) {
                if ($remainingToMatch <= 0) {
                    break;
                }
                $use           = min($remainingToMatch, $batch->quantity);
                $totalCogs    += $use * $batch->price;
                $remainingToMatch -= $use;
            }

            // If sold more than purchased (e.g., opening stock), use product cost_price as fallback
            if ($remainingToMatch > 0) {
                $costPrice = DB::table('products')->where('id', $productId)->value('cost_price') ?? 0;
                $totalCogs += $remainingToMatch * $costPrice;
            }
        }

        return $totalCogs;
    }

    /**
     * Weighted Average Cost: total purchase cost / total units purchased, at time of sale.
     */
    private function cogsByAverage(string $dateFrom, string $dateTo): float
    {
        // Per product: compute average cost up to the period start (weighted average)
        $avgCosts = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('purchases.date', '<=', $dateTo)
            ->selectRaw('purchase_items.product_id, SUM(purchase_items.price * purchase_items.quantity) / SUM(purchase_items.quantity) as avg_cost')
            ->groupBy('purchase_items.product_id')
            ->get()
            ->keyBy('product_id');

        $soldItems = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.date', [$dateFrom, $dateTo])
            ->selectRaw('sale_items.product_id, SUM(sale_items.quantity) as qty_sold')
            ->groupBy('sale_items.product_id')
            ->get();

        $totalCogs = 0.0;

        foreach ($soldItems as $row) {
            $avgCost    = $avgCosts[$row->product_id]->avg_cost
                ?? DB::table('products')->where('id', $row->product_id)->value('cost_price')
                ?? 0;
            $totalCogs += $row->qty_sold * $avgCost;
        }

        return $totalCogs;
    }

    /**
     * Standard (product cost_price column) COGS — simplest, no batch tracking.
     */
    private function cogsByStandardCost(string $dateFrom, string $dateTo): float
    {
        return (float) DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.date', [$dateFrom, $dateTo])
            ->selectRaw('SUM(sale_items.quantity * products.cost_price) as cogs')
            ->value('cogs') ?? 0;
    }
}
