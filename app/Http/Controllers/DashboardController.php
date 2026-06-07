<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    /**
     * Show the dashboard page
     */
    public function index(Request $request): View
    {
        $period = $request->get('period', 'today'); // today, week, month, year

        $stats = $this->dashboardService->getMainStats($period);

        return view('dashboard.index', [
            'stats' => $stats,
            'period' => $period,
        ]);
    }

    /**
     * Get dashboard statistics (API endpoint)
     */
    public function stats(Request $request): JsonResponse
    {
        $period = $request->get('period', 'today');

        $stats = $this->dashboardService->getMainStats($period);

        return response()->json([
            'success' => true,
            'data' => $stats,
            'period' => $period,
        ]);
    }

    /**
     * Graphique des ventes (API)
     */
    public function salesChart(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');

        $chart = $this->dashboardService->getSalesChart($period);

        return response()->json([
            'success' => true,
            'data' => $chart,
        ]);
    }

    /**
     * Revenus vs Dépenses (API)
     */
    public function revenueVsExpenses(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');

        $data = $this->dashboardService->getRevenueVsExpensesChart($period);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
