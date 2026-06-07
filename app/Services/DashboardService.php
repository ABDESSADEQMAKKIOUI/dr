<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Statistiques principales du dashboard
     */
    public function getMainStats(string $period = 'today'): array
    {
        $dates = $this->getDateRange($period);

        return [
            'sales' => $this->getSalesStats($dates),
            'purchases' => $this->getPurchasesStats($dates),
            'revenue' => $this->getRevenueStats($dates),
            'expenses' => $this->getExpensesStats($dates),
            'profit' => $this->calculateProfit($dates),
            'inventory' => $this->getInventoryStats(),
            'invoices_unpaid' => $this->getUnpaidInvoices(),
            'low_stock_products' => $this->getLowStockProducts(),
            'top_products' => $this->getTopProducts($period),
        ];
    }

    /**
     * Statistiques des ventes
     */
    protected function getSalesStats(array $dates): array
    {
        $sales = Sale::whereBetween('date', $dates)
            ->where('status', '!=', 'cancelled');

        return [
            'total' => $sales->sum('total_amount'),
            'count' => $sales->count(),
            'paid' => $sales->where('payment_status', 'paid')->sum('total_amount'),
            'unpaid' => $sales->where('payment_status', 'unpaid')->sum('total_amount'),
            'partial' => $sales->where('payment_status', 'partial')->sum('total_amount'),
        ];
    }

    /**
     * Statistiques des achats
     */
    protected function getPurchasesStats(array $dates): array
    {
        $purchases = Purchase::whereBetween('date', $dates)
            ->where('status', '!=', 'cancelled');

        return [
            'total' => $purchases->sum('total_amount'),
            'count' => $purchases->count(),
            'paid' => $purchases->where('payment_status', 'paid')->sum('total_amount'),
            'unpaid' => $purchases->where('payment_status', 'unpaid')->sum('total_amount'),
        ];
    }

    /**
     * Statistiques des revenus
     */
    protected function getRevenueStats(array $dates): array
    {
        $revenue = Sale::whereBetween('date', $dates)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        return [
            'total' => $revenue,
            'previous_period' => $this->getPreviousPeriodRevenue($dates),
            'growth_percentage' => $this->calculateGrowth($revenue, $this->getPreviousPeriodRevenue($dates)),
        ];
    }

    /**
     * Statistiques des dépenses
     */
    protected function getExpensesStats(array $dates): array
    {
        $expenses = Expense::whereBetween('date', $dates)->sum('amount');

        return [
            'total' => $expenses,
            'previous_period' => $this->getPreviousPeriodExpenses($dates),
        ];
    }

    /**
     * Calculer le profit
     */
    protected function calculateProfit(array $dates): array
    {
        $revenue = Sale::whereBetween('date', $dates)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $cost = Purchase::whereBetween('date', $dates)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $expenses = Expense::whereBetween('date', $dates)->sum('amount');

        $profit = $revenue - $cost - $expenses;

        return [
            'total' => $profit,
            'margin_percentage' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
        ];
    }

    /**
     * Valeur de l'inventaire des produits
     */
    protected function getInventoryStats(): array
    {
        $purchaseTotal = Product::select(DB::raw('SUM(COALESCE(cost_price, 0) * COALESCE(stock_quantity, 0)) as total'))->value('total') ?? 0;
        $saleTotal = Product::select(DB::raw('SUM(COALESCE(sale_price, 0) * COALESCE(stock_quantity, 0)) as total'))->value('total') ?? 0;

        return [
            'purchase_total' => $purchaseTotal,
            'sale_total' => $saleTotal,
        ];
    }

    /**
     * Factures impayées
     */
    protected function getUnpaidInvoices(): array
    {
        $invoices = Invoice::where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->get();

        return [
            'count' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'overdue_count' => $invoices->where('due_date', '<', now())->count(),
            'overdue_amount' => $invoices->where('due_date', '<', now())->sum('total_amount'),
        ];
    }

    /**
     * Produits en stock critique
     */
    protected function getLowStockProducts(): array
    {
        $products = Product::whereColumn('stock_quantity', '<=', 'stock_alert')
            ->where('track_stock', true)
            ->get();

        return [
            'count' => $products->count(),
            'products' => $products->take(10)->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'stock_quantity' => $p->stock_quantity,
                'stock_alert' => $p->stock_alert,
            ]),
        ];
    }

    /**
     * Produits les plus vendus
     */
    protected function getTopProducts(string $period, int $limit = 10): array
    {
        $dates = $this->getDateRange($period);

        $topProducts = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.date', $dates)
            ->where('sales.status', '!=', 'cancelled')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();

        return $topProducts->toArray();
    }

    /**
     * Graphique des ventes (par jour/mois)
     */
    public function getSalesChart(string $period = 'month'): array
    {
        $dates = $this->getDateRange($period);

        $groupBy = $period === 'year' ? 'month' : 'day';

        $sales = Sale::whereBetween('date', $dates)
            ->where('status', '!=', 'cancelled')
            ->select(
                DB::raw("DATE_FORMAT(date, " . ($groupBy === 'day' ? "'%Y-%m-%d'" : "'%Y-%m'") . ") as period"),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $sales->toArray();
    }

    /**
     * Revenus vs Dépenses (graphique)
     */
    public function getRevenueVsExpensesChart(string $period = 'month'): array
    {
        $dates = $this->getDateRange($period);

        $revenue = Sale::whereBetween('date', $dates)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $expenses = Expense::whereBetween('date', $dates)->sum('amount');

        $purchases = Purchase::whereBetween('date', $dates)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'purchases' => $purchases,
            'net_profit' => $revenue - $expenses - $purchases,
        ];
    }

    /**
     * Obtenir la plage de dates selon la période
     */
    protected function getDateRange(string $period): array
    {
        return match ($period) {
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday(), Carbon::yesterday()->endOfDay()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::today(), Carbon::today()->endOfDay()],
        };
    }

    /**
     * Revenus période précédente
     */
    protected function getPreviousPeriodRevenue(array $currentDates): float
    {
        $diff = Carbon::parse($currentDates[1])->diffInDays(Carbon::parse($currentDates[0]));
        $previousStart = Carbon::parse($currentDates[0])->subDays($diff + 1);
        $previousEnd = Carbon::parse($currentDates[0])->subDay();

        return Sale::whereBetween('date', [$previousStart, $previousEnd])
            ->where('payment_status', 'paid')
            ->sum('total_amount');
    }

    /**
     * Dépenses période précédente
     */
    protected function getPreviousPeriodExpenses(array $currentDates): float
    {
        $diff = Carbon::parse($currentDates[1])->diffInDays(Carbon::parse($currentDates[0]));
        $previousStart = Carbon::parse($currentDates[0])->subDays($diff + 1);
        $previousEnd = Carbon::parse($currentDates[0])->subDay();

        return Expense::whereBetween('date', [$previousStart, $previousEnd])->sum('amount');
    }

    /**
     * Calculer le taux de croissance
     */
    protected function calculateGrowth(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }
}
