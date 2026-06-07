<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));

        // Get total sales
        $totalSales = \App\Models\Sale::whereBetween('date', [$fromDate, $toDate])
            ->sum('total_amount');

        // Get total profit (sales - cost)
        $totalCost = \App\Models\Sale::whereBetween('date', [$fromDate, $toDate])
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(sale_items.quantity * products.cost_price) as total_cost')
            ->value('total_cost');
        
        $totalProfit = $totalSales - ($totalCost ?? 0);

        // Get total orders
        $totalOrders = \App\Models\Sale::whereBetween('date', [$fromDate, $toDate])->count();

        // Average order value
        $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Top products
        $topProducts = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.date', [$fromDate, $toDate])
            ->selectRaw('products.name, SUM(sale_items.quantity) as quantity_sold, SUM(sale_items.quantity * sale_items.price) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Chart data
        $chartData = $this->getSalesChartData($fromDate, $toDate);

        return view('reports.sales', [
            'totalSales' => $totalSales,
            'totalProfit' => $totalProfit,
            'totalOrders' => $totalOrders,
            'avgOrderValue' => $avgOrderValue,
            'topProducts' => $topProducts,
            'labels' => $chartData['labels'],
            'salesData' => $chartData['data'],
        ]);
    }

    public function purchases(Request $request): View
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));
        $supplierId = $request->input('supplier_id');

        // Build query
        $query = \App\Models\Purchase::whereBetween('date', [$fromDate, $toDate]);
        
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        // Get statistics
        $stats = [
            'count' => $query->count(),
            'total' => $query->sum('total_amount'),
            'paid' => $query->sum('paid_amount'),
            'due' => $query->sum('total_amount') - $query->sum('paid_amount'),
        ];

        // Get detailed purchases
        $purchases = \App\Models\Purchase::with('supplier')
            ->whereBetween('date', [$fromDate, $toDate])
            ->when($supplierId, function($q) use ($supplierId) {
                return $q->where('supplier_id', $supplierId);
            })
            ->withCount('items')
            ->orderByDesc('date')
            ->limit(50)
            ->get()
            ->map(function($purchase) {
                return (object)[
                    'created_at' => $purchase->created_at,
                    'reference' => $purchase->reference,
                    'supplier' => $purchase->supplier,
                    'items_count' => $purchase->items_count,
                    'total' => $purchase->total_amount,
                    'paid' => $purchase->paid_amount,
                    'status' => $purchase->status,
                ];
            });

        // Get suppliers for filter
        $suppliers = \App\Models\Supplier::active()->orderBy('name')->get();

        // Chart data - by supplier
        $supplierData = \App\Models\Purchase::whereBetween('date', [$fromDate, $toDate])
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->selectRaw('suppliers.name, SUM(purchases.total_amount) as total')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // Chart data - monthly trend
        $trendData = $this->getPurchasesTrendData($fromDate, $toDate);

        $chartData = [
            'suppliers' => [
                'labels' => $supplierData->pluck('name')->toArray(),
                'data' => $supplierData->pluck('total')->toArray(),
            ],
            'trend' => $trendData,
        ];

        return view('reports.purchases', compact('stats', 'purchases', 'suppliers', 'chartData'));
    }

    public function stock(Request $request): View
    {
        $warehouseId = $request->input('warehouse_id');
        $categoryId = $request->input('category_id');
        $status = $request->input('status');

        // Build product query
        $query = \App\Models\Product::with(['category', 'warehouses']);
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Get statistics
        $totalProducts = (clone $query)->count();
        
        $stockValue = (clone $query)->get()->sum(function($product) {
            return $product->stock_quantity * $product->sale_price;
        });

        $lowStock = (clone $query)->lowStock()->count();
        $outOfStock = (clone $query)->outOfStock()->count();

        $stats = [
            'total_products' => $totalProducts,
            'stock_value' => $stockValue,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
        ];

        // Get inventory details
        $inventoryQuery = \App\Models\Product::with(['category']);
        
        if ($categoryId) {
            $inventoryQuery->where('category_id', $categoryId);
        }

        if ($status == 'low') {
            $inventoryQuery->lowStock();
        } elseif ($status == 'out') {
            $inventoryQuery->outOfStock();
        } elseif ($status == 'normal') {
            $inventoryQuery->whereColumn('stock_quantity', '>', 'stock_alert');
        }

        $inventory = $inventoryQuery->limit(100)->get()->map(function($product) {
            return (object)[
                'product' => $product,
                'warehouse' => (object)['name' => 'Main Warehouse'], // Default warehouse
                'quantity' => $product->stock_quantity,
            ];
        });

        // Get filters data
        $warehouses = \App\Models\Warehouse::all() ?? [];
        $categories = \App\Models\Category::all();

        // Chart data - by category
        $categoryData = \App\Models\Product::join('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, COUNT(products.id) as count')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Chart data - status distribution
        $normalStock = \App\Models\Product::whereColumn('stock_quantity', '>', 'stock_alert')->count();

        $chartData = [
            'categories' => [
                'labels' => $categoryData->pluck('name')->toArray(),
                'data' => $categoryData->pluck('count')->toArray(),
            ],
            'status' => [
                'data' => [$normalStock, $lowStock, $outOfStock],
            ],
        ];

        return view('reports.stock', compact('stats', 'inventory', 'warehouses', 'categories', 'chartData'));
    }

    public function financial(Request $request): View
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));

        // Get sales revenue
        $salesRevenue = \App\Models\Sale::whereBetween('date', [$fromDate, $toDate])
            ->sum('total_amount');

        // Get purchase costs
        $purchaseCosts = \App\Models\Purchase::whereBetween('date', [$fromDate, $toDate])
            ->sum('total_amount');

        // Get operating expenses
        $operatingExpenses = \App\Models\Expense::whereBetween('date', [$fromDate, $toDate])
            ->sum('amount');

        // Calculate totals
        $stats = [
            'revenue' => $salesRevenue,
            'expenses' => $purchaseCosts + $operatingExpenses,
            'sales_revenue' => $salesRevenue,
            'purchase_costs' => $purchaseCosts,
            'operating_expenses' => $operatingExpenses,
        ];

        // Income breakdown
        $incomeBreakdown = [
            ['name' => 'Sales Revenue', 'amount' => $salesRevenue],
        ];

        // Expense breakdown by category
        $expenseBreakdown = \App\Models\Expense::whereBetween('date', [$fromDate, $toDate])
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name, SUM(expenses.amount) as amount')
            ->groupBy('expense_categories.name')
            ->get()
            ->toArray();

        // Add purchases to expense breakdown
        $expenseBreakdown[] = ['name' => 'Purchases', 'amount' => $purchaseCosts];

        // Chart data - monthly breakdown
        $chartData = $this->getMonthlyChartData($fromDate, $toDate);

        return view('reports.financial', compact('stats', 'incomeBreakdown', 'expenseBreakdown', 'chartData'));
    }

    public function customers(Request $request): View
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));

        // Get total customers
        $totalCustomers = \App\Models\Customer::count();

        // Get sales statistics for the period
        $totalSales = \App\Models\Sale::whereBetween('date', [$fromDate, $toDate])
            ->sum('total_amount');

        $totalOrders = \App\Models\Sale::whereBetween('date', [$fromDate, $toDate])
            ->count();

        $avgOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        // Get outstanding amount
        $outstanding = \App\Models\Sale::whereBetween('date', [$fromDate, $toDate])
            ->sum('total_amount') - \App\Models\Sale::whereBetween('date', [$fromDate, $toDate])
            ->sum('paid_amount');

        $stats = [
            'total_customers' => $totalCustomers,
            'total_sales' => $totalSales,
            'avg_order' => $avgOrder,
            'outstanding' => $outstanding,
        ];

        // Get customer details with sales data
        $customers = \App\Models\Customer::withCount(['sales as orders_count' => function($q) use ($fromDate, $toDate) {
                $q->whereBetween('date', [$fromDate, $toDate]);
            }])
            ->with(['sales' => function($q) use ($fromDate, $toDate) {
                $q->whereBetween('date', [$fromDate, $toDate]);
            }])
            ->get()
            ->map(function($customer) {
                $totalSales = $customer->sales->sum('total_amount');
                $paid = $customer->sales->sum('paid_amount');
                $lastOrder = $customer->sales->sortByDesc('date')->first();
                
                return (object)[
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'orders_count' => $customer->orders_count,
                    'total_sales' => $totalSales,
                    'paid' => $paid,
                    'last_order_date' => $lastOrder ? $lastOrder->date : null,
                ];
            })
            ->sortByDesc('total_sales')
            ->take(50);

        // Top customers chart data
        $topCustomersData = $customers->take(10);

        // Customer acquisition trend
        $acquisitionData = $this->getCustomerAcquisitionData($fromDate, $toDate);

        $chartData = [
            'top_customers' => [
                'labels' => $topCustomersData->pluck('name')->toArray(),
                'data' => $topCustomersData->pluck('total_sales')->toArray(),
            ],
            'acquisition' => $acquisitionData,
        ];

        return view('reports.customers', compact('stats', 'customers', 'chartData'));
    }

    public function export(Request $request)
    {
        // Export logic here
        return redirect()->back()->with('success', 'Report exported');
    }

    private function getMonthlyChartData($fromDate, $toDate)
    {
        $start = \Carbon\Carbon::parse($fromDate);
        $end = \Carbon\Carbon::parse($toDate);
        
        $months = [];
        $revenue = [];
        $expenses = [];
        $profit = [];

        $current = $start->copy();
        while ($current <= $end) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();
            
            // Don't go beyond the end date
            if ($monthEnd > $end) {
                $monthEnd = $end->copy();
            }

            $months[] = $current->format('M Y');

            // Revenue for this month
            $monthRevenue = \App\Models\Sale::whereBetween('date', [$monthStart, $monthEnd])
                ->sum('total_amount');
            $revenue[] = $monthRevenue;

            // Expenses for this month
            $monthPurchases = \App\Models\Purchase::whereBetween('date', [$monthStart, $monthEnd])
                ->sum('total_amount');
            $monthExpenses = \App\Models\Expense::whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');
            $totalExpenses = $monthPurchases + $monthExpenses;
            $expenses[] = $totalExpenses;

            // Profit
            $profit[] = $monthRevenue - $totalExpenses;

            $current->addMonth();
        }

        return [
            'months' => $months,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit' => $profit,
        ];
    }

    private function getSalesChartData($fromDate, $toDate)
    {
        $start = \Carbon\Carbon::parse($fromDate);
        $end = \Carbon\Carbon::parse($toDate);
        
        $labels = [];
        $data = [];

        $current = $start->copy();
        while ($current <= $end) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();
            
            if ($monthEnd > $end) {
                $monthEnd = $end->copy();
            }

            $labels[] = $current->format('M Y');

            $monthSales = \App\Models\Sale::whereBetween('date', [$monthStart, $monthEnd])
                ->sum('total_amount');
            $data[] = $monthSales;

            $current->addMonth();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getPurchasesTrendData($fromDate, $toDate)
    {
        $start = \Carbon\Carbon::parse($fromDate);
        $end = \Carbon\Carbon::parse($toDate);
        
        $labels = [];
        $data = [];

        $current = $start->copy();
        while ($current <= $end) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();
            
            if ($monthEnd > $end) {
                $monthEnd = $end->copy();
            }

            $labels[] = $current->format('M Y');

            $monthPurchases = \App\Models\Purchase::whereBetween('date', [$monthStart, $monthEnd])
                ->sum('total_amount');
            $data[] = $monthPurchases;

            $current->addMonth();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function getCustomerAcquisitionData($fromDate, $toDate)
    {
        $start = \Carbon\Carbon::parse($fromDate);
        $end = \Carbon\Carbon::parse($toDate);
        
        $labels = [];
        $data = [];

        $current = $start->copy();
        while ($current <= $end) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();
            
            if ($monthEnd > $end) {
                $monthEnd = $end->copy();
            }

            $labels[] = $current->format('M Y');

            $newCustomers = \App\Models\Customer::whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
            $data[] = $newCustomers;

            $current->addMonth();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3.3 — Extended Sales Reports
    // ─────────────────────────────────────────────────────────────

    public function salesByCategory(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $rows = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.date', [$from, $to])
            ->selectRaw('categories.name as category, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        return view('reports.sales-by-category', compact('rows', 'from', 'to'));
    }

    public function salesByBrand(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $rows = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->whereBetween('sales.date', [$from, $to])
            ->selectRaw('COALESCE(brands.name, "No Brand") as brand, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as revenue')
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc('revenue')
            ->get();

        return view('reports.sales-by-brand', compact('rows', 'from', 'to'));
    }

    public function salesByWarehouse(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $rows = \App\Models\Sale::join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
            ->whereBetween('sales.date', [$from, $to])
            ->selectRaw('warehouses.name as warehouse, COUNT(sales.id) as orders, SUM(sales.total_amount) as revenue, SUM(sales.paid_amount) as paid')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->orderByDesc('revenue')
            ->get();

        return view('reports.sales-by-warehouse', compact('rows', 'from', 'to'));
    }

    public function salesByPaymentMethod(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $rows = \App\Models\Sale::whereBetween('date', [$from, $to])
            ->whereNotNull('payment_method')
            ->join('payment_methods', 'sales.payment_method_id', '=', 'payment_methods.id')
            ->selectRaw('payment_methods.name as method, COUNT(sales.id) as orders, SUM(sales.total_amount) as total')
            ->groupBy('payment_methods.id', 'payment_methods.name')
            ->orderByDesc('total')
            ->get();

        return view('reports.sales-by-payment-method', compact('rows', 'from', 'to'));
    }

    public function salesByUser(Request $request): View
    {
        $from   = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to     = $request->input('to', now()->format('Y-m-d'));
        $userId = $request->input('user_id');

        $rows = \App\Models\Sale::join('users', 'sales.user_id', '=', 'users.id')
            ->whereBetween('sales.date', [$from, $to])
            ->when($userId, fn($q) => $q->where('sales.user_id', $userId))
            ->selectRaw('CONCAT(users.first_name, " ", users.last_name) as user, COUNT(sales.id) as orders, SUM(sales.total_amount) as revenue')
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->orderByDesc('revenue')
            ->get();

        $users = \App\Models\User::orderBy('first_name')->get();

        return view('reports.sales-by-user', compact('rows', 'from', 'to', 'users', 'userId'));
    }

    public function topProducts(Request $request): View
    {
        $from  = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to    = $request->input('to', now()->format('Y-m-d'));
        $limit = (int) $request->input('limit', 20);

        $rows = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.date', [$from, $to])
            ->selectRaw('products.name, products.sku, SUM(sale_items.quantity) as qty_sold, SUM(sale_items.subtotal) as revenue')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return view('reports.top-products', compact('rows', 'from', 'to', 'limit'));
    }

    public function topCustomers(Request $request): View
    {
        $from  = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to    = $request->input('to', now()->format('Y-m-d'));
        $limit = (int) $request->input('limit', 20);

        $rows = \App\Models\Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereBetween('sales.date', [$from, $to])
            ->selectRaw('customers.name, customers.phone, customers.email, COUNT(sales.id) as orders, SUM(sales.total_amount) as spent, SUM(sales.paid_amount) as paid')
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.email')
            ->orderByDesc('spent')
            ->limit($limit)
            ->get();

        return view('reports.top-customers', compact('rows', 'from', 'to', 'limit'));
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3.4 — Extended Purchase Reports
    // ─────────────────────────────────────────────────────────────

    public function purchasesByWarehouse(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $rows = \App\Models\Purchase::join('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
            ->whereBetween('purchases.date', [$from, $to])
            ->selectRaw('warehouses.name as warehouse, COUNT(purchases.id) as orders, SUM(purchases.total_amount) as total, SUM(purchases.paid_amount) as paid')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->orderByDesc('total')
            ->get();

        return view('reports.purchases-by-warehouse', compact('rows', 'from', 'to'));
    }

    public function purchasesByUser(Request $request): View
    {
        $from   = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to     = $request->input('to', now()->format('Y-m-d'));

        $rows = \App\Models\Purchase::join('users', 'purchases.user_id', '=', 'users.id')
            ->whereBetween('purchases.date', [$from, $to])
            ->selectRaw('CONCAT(users.first_name, " ", users.last_name) as user, COUNT(purchases.id) as orders, SUM(purchases.total_amount) as total')
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->orderByDesc('total')
            ->get();

        return view('reports.purchases-by-user', compact('rows', 'from', 'to'));
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3.5 — Customer Detail Report
    // ─────────────────────────────────────────────────────────────

    public function customerDetail(Request $request, \App\Models\Customer $customer): View
    {
        $customer->load(['sales.items.product', 'quotations']);

        $totalSales    = $customer->sales->sum('total_amount');
        $totalPaid     = $customer->sales->sum('paid_amount');
        $totalDue      = $totalSales - $totalPaid;
        $totalOrders   = $customer->sales->count();

        $recentSales = $customer->sales()->with('items')->latest('date')->limit(20)->get();

        return view('reports.customer-detail', compact('customer', 'totalSales', 'totalPaid', 'totalDue', 'totalOrders', 'recentSales'));
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3.6 — Supplier Detail Report
    // ─────────────────────────────────────────────────────────────

    public function supplierDetail(Request $request, \App\Models\Supplier $supplier): View
    {
        $supplier->load(['purchases.items.product']);

        $totalPurchases = $supplier->purchases->sum('total_amount');
        $totalPaid      = $supplier->purchases->sum('paid_amount');
        $totalDue       = $totalPurchases - $totalPaid;
        $totalOrders    = $supplier->purchases->count();

        $recentPurchases = $supplier->purchases()->with('items')->latest('date')->limit(20)->get();

        return view('reports.supplier-detail', compact('supplier', 'totalPurchases', 'totalPaid', 'totalDue', 'totalOrders', 'recentPurchases'));
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3.8 — Warehouse Reports
    // ─────────────────────────────────────────────────────────────

    public function stockByWarehouse(Request $request): View
    {
        $warehouseId = $request->input('warehouse_id');
        $warehouses  = \App\Models\Warehouse::orderBy('name')->get();

        $rows = \App\Models\ProductWarehouse::with('product', 'warehouse')
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->get()
            ->groupBy('warehouse.name');

        return view('reports.stock-by-warehouse', compact('rows', 'warehouses', 'warehouseId'));
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3.9 — Financial Reports
    // ─────────────────────────────────────────────────────────────

    public function paymentTransactions(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $sales = \App\Models\Sale::with('customer')
            ->whereBetween('date', [$from, $to])
            ->where('paid_amount', '>', 0)
            ->orderByDesc('date')
            ->get()
            ->map(fn($s) => (object)[
                'date'   => $s->date,
                'type'   => 'Sale',
                'ref'    => $s->reference,
                'party'  => $s->customer->name ?? 'Walk-in',
                'amount' => $s->paid_amount,
                'method' => $s->payment_method ?? '—',
            ]);

        $purchases = \App\Models\Purchase::with('supplier')
            ->whereBetween('date', [$from, $to])
            ->where('paid_amount', '>', 0)
            ->orderByDesc('date')
            ->get()
            ->map(fn($p) => (object)[
                'date'   => $p->date,
                'type'   => 'Purchase',
                'ref'    => $p->reference,
                'party'  => $p->supplier->name ?? '—',
                'amount' => $p->paid_amount,
                'method' => $p->payment_method ?? '—',
            ]);

        $transactions = $sales->merge($purchases)->sortByDesc('date')->values();

        return view('reports.payment-transactions', compact('transactions', 'from', 'to'));
    }

    public function inventoryValuation(Request $request): View
    {
        $warehouseId = $request->input('warehouse_id');
        $warehouses  = \App\Models\Warehouse::orderBy('name')->get();

        $products = \App\Models\Product::with('warehouses')
            ->when($warehouseId, fn($q) => $q->whereHas('warehouses', fn($wq) => $wq->where('warehouse_id', $warehouseId)))
            ->get()
            ->map(function ($p) use ($warehouseId) {
                $qty = $warehouseId
                    ? ($p->warehouses->where('id', $warehouseId)->first()?->pivot?->quantity ?? 0)
                    : $p->stock_quantity;
                $p->stock_qty   = $qty;
                $p->cost_value  = $qty * $p->cost_price;
                $p->sell_value  = $qty * $p->selling_price;
                return $p;
            });

        $totalCostValue = $products->sum('cost_value');
        $totalSellValue = $products->sum('sell_value');

        return view('reports.inventory-valuation', compact('products', 'warehouses', 'warehouseId', 'totalCostValue', 'totalSellValue'));
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3.6 — Supplier Summary Report
    // ─────────────────────────────────────────────────────────────

    public function suppliers(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $suppliers = \App\Models\Supplier::withCount(['purchases as orders_count' => function ($q) use ($from, $to) {
                $q->whereBetween('date', [$from, $to]);
            }])
            ->with(['purchases' => function ($q) use ($from, $to) {
                $q->whereBetween('date', [$from, $to]);
            }])
            ->get()
            ->map(function ($supplier) {
                $total = $supplier->purchases->sum('total_amount');
                $paid  = $supplier->purchases->sum('paid_amount');
                return (object)[
                    'id'              => $supplier->id,
                    'name'            => $supplier->name,
                    'phone'           => $supplier->phone,
                    'email'           => $supplier->email,
                    'orders_count'    => $supplier->orders_count,
                    'total_purchased' => $total,
                    'total_paid'      => $paid,
                    'due'             => $total - $paid,
                ];
            })
            ->sortByDesc('total_purchased');

        return view('reports.suppliers', compact('suppliers', 'from', 'to'));
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3.4 — Product Purchases Detail
    // ─────────────────────────────────────────────────────────────

    public function productPurchases(Request $request): View
    {
        $from       = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to         = $request->input('to', now()->format('Y-m-d'));
        $supplierId = $request->input('supplier_id');

        $rows = \App\Models\PurchaseItem::join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('purchases.date', [$from, $to])
            ->when($supplierId, fn($q) => $q->where('purchases.supplier_id', $supplierId))
            ->selectRaw('
                products.name as product_name,
                products.sku,
                categories.name as category,
                SUM(purchase_items.quantity) as qty_received,
                AVG(purchase_items.price) as avg_cost,
                SUM(purchase_items.quantity * purchase_items.price) as total_cost
            ')
            ->groupBy('products.id', 'products.name', 'products.sku', 'categories.name')
            ->orderByDesc('total_cost')
            ->get();

        $suppliers = \App\Models\Supplier::active()->orderBy('name')->get();

        return view('reports.product-purchases', compact('rows', 'from', 'to', 'suppliers'));
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3.7 — User-Level Reports
    // ─────────────────────────────────────────────────────────────

    public function quotationsByUser(Request $request): View
    {
        $from   = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to     = $request->input('to', now()->format('Y-m-d'));
        $userId = $request->input('user_id');

        $rows = \App\Models\Quotation::join('users', 'quotations.user_id', '=', 'users.id')
            ->whereBetween('quotations.date', [$from, $to])
            ->when($userId, fn($q) => $q->where('quotations.user_id', $userId))
            ->selectRaw('
                CONCAT(users.first_name, " ", users.last_name) as user,
                COUNT(quotations.id) as count,
                SUM(quotations.total_amount) as total,
                SUM(CASE WHEN quotations.status = "converted" THEN 1 ELSE 0 END) as converted
            ')
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->orderByDesc('total')
            ->get();

        $users = \App\Models\User::orderBy('first_name')->get();

        return view('reports.quotations-by-user', compact('rows', 'from', 'to', 'users', 'userId'));
    }

    public function returnsByUser(Request $request): View
    {
        $from   = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to     = $request->input('to', now()->format('Y-m-d'));
        $userId = $request->input('user_id');

        $saleReturns = \DB::table('sale_returns')
            ->join('users', 'sale_returns.user_id', '=', 'users.id')
            ->whereBetween('sale_returns.date', [$from, $to])
            ->when($userId, fn($q) => $q->where('sale_returns.user_id', $userId))
            ->selectRaw('users.id as uid, CONCAT(users.first_name, " ", users.last_name) as user, COUNT(*) as cnt, SUM(sale_returns.total_amount) as val')
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->get()->keyBy('uid');

        $purchaseReturns = \DB::table('purchase_returns')
            ->join('users', 'purchase_returns.user_id', '=', 'users.id')
            ->whereBetween('purchase_returns.date', [$from, $to])
            ->when($userId, fn($q) => $q->where('purchase_returns.user_id', $userId))
            ->selectRaw('users.id as uid, CONCAT(users.first_name, " ", users.last_name) as user, COUNT(*) as cnt, SUM(purchase_returns.total_amount) as val')
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->get()->keyBy('uid');

        $allUserIds = collect($saleReturns->keys())->merge($purchaseReturns->keys())->unique();
        $rows = $allUserIds->map(function ($uid) use ($saleReturns, $purchaseReturns) {
            $sr = $saleReturns[$uid] ?? null;
            $pr = $purchaseReturns[$uid] ?? null;
            return (object)[
                'user'             => $sr->user ?? $pr->user ?? 'Unknown',
                'sale_returns'     => $sr->cnt ?? 0,
                'purchase_returns' => $pr->cnt ?? 0,
                'total_value'      => ($sr->val ?? 0) + ($pr->val ?? 0),
            ];
        })->sortByDesc('total_value');

        $users = \App\Models\User::orderBy('first_name')->get();

        return view('reports.returns-by-user', compact('rows', 'from', 'to', 'users', 'userId'));
    }

    public function transfersByUser(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $rows = \App\Models\StockTransfer::join('users', 'stock_transfers.user_id', '=', 'users.id')
            ->whereBetween('stock_transfers.date', [$from, $to])
            ->selectRaw('CONCAT(users.first_name, " ", users.last_name) as user, COUNT(stock_transfers.id) as count, SUM((SELECT SUM(quantity) FROM stock_transfer_items WHERE stock_transfer_id = stock_transfers.id)) as total_qty')
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->orderByDesc('count')
            ->get();

        return view('reports.transfers-by-user', compact('rows', 'from', 'to'));
    }

    public function adjustmentsByUser(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $rows = \App\Models\StockAdjustment::join('users', 'stock_adjustments.user_id', '=', 'users.id')
            ->whereBetween('stock_adjustments.date', [$from, $to])
            ->selectRaw('
                CONCAT(users.first_name, " ", users.last_name) as user,
                COUNT(stock_adjustments.id) as count,
                SUM(CASE WHEN stock_adjustments.type = "add" THEN (SELECT SUM(quantity) FROM stock_adjustment_items WHERE stock_adjustment_id = stock_adjustments.id) ELSE 0 END) as added,
                SUM(CASE WHEN stock_adjustments.type = "sub" THEN (SELECT SUM(quantity) FROM stock_adjustment_items WHERE stock_adjustment_id = stock_adjustments.id) ELSE 0 END) as removed
            ')
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->orderByDesc('count')
            ->get();

        return view('reports.adjustments-by-user', compact('rows', 'from', 'to'));
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3.8 — Warehouse Reports (additional)
    // ─────────────────────────────────────────────────────────────

    public function expensesByWarehouse(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $rows = \App\Models\Expense::join('warehouses', 'expenses.warehouse_id', '=', 'warehouses.id')
            ->whereBetween('expenses.date', [$from, $to])
            ->selectRaw('warehouses.name as warehouse, COUNT(expenses.id) as count, SUM(expenses.amount) as total')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->orderByDesc('total')
            ->get();

        return view('reports.expenses-by-warehouse', compact('rows', 'from', 'to'));
    }

    public function quotationsByWarehouse(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $rows = \App\Models\Quotation::join('warehouses', 'quotations.warehouse_id', '=', 'warehouses.id')
            ->whereBetween('quotations.date', [$from, $to])
            ->selectRaw('
                warehouses.name as warehouse,
                COUNT(quotations.id) as count,
                SUM(quotations.total_amount) as total,
                SUM(CASE WHEN quotations.status = "converted" THEN 1 ELSE 0 END) as converted
            ')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->orderByDesc('total')
            ->get();

        return view('reports.quotations-by-warehouse', compact('rows', 'from', 'to'));
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3.9 — Financial Reports (deposits & money transfers)
    // ─────────────────────────────────────────────────────────────

    public function depositsReport(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $deposits = \App\Models\Deposit::with('category', 'account')
            ->whereBetween('date', [$from, $to])
            ->orderByDesc('date')
            ->get();

        $total = $deposits->sum('amount');
        $count = $deposits->count();

        return view('reports.deposits', compact('deposits', 'from', 'to', 'total', 'count'));
    }

    public function moneyTransfersReport(Request $request): View
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $transfers = \App\Models\MoneyTransfer::with('fromAccount', 'toAccount')
            ->whereBetween('date', [$from, $to])
            ->orderByDesc('date')
            ->get();

        $totalAmount = $transfers->sum('amount');
        $totalFees   = $transfers->sum('fee');
        $count       = $transfers->count();

        return view('reports.money-transfers-report', compact('transfers', 'from', 'to', 'totalAmount', 'totalFees', 'count'));
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 5.6 — Profit & Loss with FIFO / Average Cost
    // ─────────────────────────────────────────────────────────────

    public function profitLoss(Request $request): View
    {
        $from   = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to     = $request->input('to', now()->format('Y-m-d'));
        $method = $request->input('method', 'cogs'); // fifo | average | cogs

        $service = new \App\Services\ReportService();
        $cogsData = $service->getCostOfGoodsSold($method, $from, $to);

        $operatingExpenses = \App\Models\Expense::whereBetween('date', [$from, $to])->sum('amount');
        $netProfit = $cogsData['gross_profit'] - $operatingExpenses;

        return view('reports.profit-loss', array_merge($cogsData, [
            'from'               => $from,
            'to'                 => $to,
            'operating_expenses' => $operatingExpenses,
            'net_profit'         => $netProfit,
        ]));
    }

    // ─────────────────────────────────────────────────────────────
    // STEP 3.10 — CSV Export
    // ─────────────────────────────────────────────────────────────

    public function exportCsv(Request $request)
    {
        $type = $request->input('type', 'sales');
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $type . '-' . $from . '-' . $to . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($type, $from, $to) {
            $file = fopen('php://output', 'w');

            match ($type) {
                'sales' => $this->csvSales($file, $from, $to),
                'purchases' => $this->csvPurchases($file, $from, $to),
                'top-products' => $this->csvTopProducts($file, $from, $to),
                'top-customers' => $this->csvTopCustomers($file, $from, $to),
                'inventory-valuation' => $this->csvInventoryValuation($file),
                'payment-transactions' => $this->csvPaymentTransactions($file, $from, $to),
                default => fputcsv($file, ['No data']),
            };

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function csvSales($file, $from, $to): void
    {
        fputcsv($file, ['Reference', 'Date', 'Customer', 'Warehouse', 'Total', 'Paid', 'Due', 'Status']);
        \App\Models\Sale::with('customer', 'warehouse')
            ->whereBetween('date', [$from, $to])
            ->orderByDesc('date')
            ->chunk(500, function ($sales) use ($file) {
                foreach ($sales as $s) {
                    fputcsv($file, [
                        $s->reference,
                        $s->date,
                        $s->customer->name ?? 'Walk-in',
                        $s->warehouse->name ?? '',
                        $s->total_amount,
                        $s->paid_amount,
                        $s->total_amount - $s->paid_amount,
                        $s->payment_status,
                    ]);
                }
            });
    }

    private function csvPurchases($file, $from, $to): void
    {
        fputcsv($file, ['Reference', 'Date', 'Supplier', 'Warehouse', 'Total', 'Paid', 'Due', 'Status']);
        \App\Models\Purchase::with('supplier', 'warehouse')
            ->whereBetween('date', [$from, $to])
            ->orderByDesc('date')
            ->chunk(500, function ($purchases) use ($file) {
                foreach ($purchases as $p) {
                    fputcsv($file, [
                        $p->reference,
                        $p->date,
                        $p->supplier->name ?? '',
                        $p->warehouse->name ?? '',
                        $p->total_amount,
                        $p->paid_amount,
                        $p->total_amount - $p->paid_amount,
                        $p->payment_status,
                    ]);
                }
            });
    }

    private function csvTopProducts($file, $from, $to): void
    {
        fputcsv($file, ['Product', 'SKU', 'Qty Sold', 'Revenue (DH)']);
        \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.date', [$from, $to])
            ->selectRaw('products.name, products.sku, SUM(sale_items.quantity) as qty_sold, SUM(sale_items.subtotal) as revenue')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('revenue')
            ->chunk(500, function ($rows) use ($file) {
                foreach ($rows as $r) {
                    fputcsv($file, [$r->name, $r->sku, $r->qty_sold, $r->revenue]);
                }
            });
    }

    private function csvTopCustomers($file, $from, $to): void
    {
        fputcsv($file, ['Customer', 'Phone', 'Orders', 'Total Spent (DH)', 'Total Paid (DH)']);
        \App\Models\Sale::join('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereBetween('sales.date', [$from, $to])
            ->selectRaw('customers.name, customers.phone, COUNT(sales.id) as orders, SUM(sales.total_amount) as spent, SUM(sales.paid_amount) as paid')
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->orderByDesc('spent')
            ->chunk(500, function ($rows) use ($file) {
                foreach ($rows as $r) {
                    fputcsv($file, [$r->name, $r->phone, $r->orders, $r->spent, $r->paid]);
                }
            });
    }

    private function csvInventoryValuation($file): void
    {
        fputcsv($file, ['Product', 'SKU', 'Category', 'Stock Qty', 'Cost Price', 'Sell Price', 'Cost Value', 'Sell Value']);
        \App\Models\Product::with('category')->chunk(500, function ($products) use ($file) {
            foreach ($products as $p) {
                fputcsv($file, [
                    $p->name,
                    $p->sku,
                    $p->category->name ?? '',
                    $p->stock_quantity,
                    $p->cost_price,
                    $p->selling_price,
                    $p->stock_quantity * $p->cost_price,
                    $p->stock_quantity * $p->selling_price,
                ]);
            }
        });
    }

    private function csvPaymentTransactions($file, $from, $to): void
    {
        fputcsv($file, ['Date', 'Type', 'Reference', 'Party', 'Amount (DH)', 'Method']);
        \App\Models\Sale::with('customer')
            ->whereBetween('date', [$from, $to])
            ->where('paid_amount', '>', 0)
            ->orderByDesc('date')
            ->chunk(500, function ($sales) use ($file) {
                foreach ($sales as $s) {
                    fputcsv($file, [$s->date, 'Sale', $s->reference, $s->customer->name ?? 'Walk-in', $s->paid_amount, $s->payment_method ?? '']);
                }
            });
        \App\Models\Purchase::with('supplier')
            ->whereBetween('date', [$from, $to])
            ->where('paid_amount', '>', 0)
            ->orderByDesc('date')
            ->chunk(500, function ($purchases) use ($file) {
                foreach ($purchases as $p) {
                    fputcsv($file, [$p->date, 'Purchase', $p->reference, $p->supplier->name ?? '', $p->paid_amount, $p->payment_method ?? '']);
                }
            });
    }
}
