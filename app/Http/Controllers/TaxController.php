<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));

        $taxes = Tax::where('is_active', true)->get();
        
        // Calculate tax collected from sales
        $salesWithTax = \App\Models\Sale::whereBetween('date', [$fromDate, $toDate])
            ->where('tax_amount', '>', 0)
            ->get();
        
        $taxCollectedTotal = $salesWithTax->sum('tax_amount');
        
        // Group by tax rate for breakdown
        $taxCollected = $salesWithTax->groupBy('tax_rate')->map(function($group, $rate) {
            return [
                'description' => 'TVA ' . number_format((float)$rate, 0) . '%',
                'amount' => $group->sum('tax_amount')
            ];
        })->values()->toArray();
        
        // Calculate tax paid on purchases
        $purchasesWithTax = \App\Models\Purchase::whereBetween('date', [$fromDate, $toDate])
            ->where('tax_amount', '>', 0)
            ->get();
        
        $taxPaidTotal = $purchasesWithTax->sum('tax_amount');
        
        // Group by tax rate for breakdown
        $taxPaid = $purchasesWithTax->groupBy('tax_rate')->map(function($group, $rate) {
            return [
                'description' => 'TVA ' . number_format((float)$rate, 0) . '%',
                'amount' => $group->sum('tax_amount')
            ];
        })->values()->toArray();
        
        $stats = [
            'collected' => $taxCollectedTotal,
            'paid' => $taxPaidTotal,
        ];
        
        return view('accounting.tax.index', compact('taxes', 'stats', 'taxCollected', 'taxPaid'));
    }

    public function declaration(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Calculate date range based on period
        if ($period == 'monthly') {
            $fromDate = now()->setYear($year)->setMonth($month)->startOfMonth()->format('Y-m-d');
            $toDate = now()->setYear($year)->setMonth($month)->endOfMonth()->format('Y-m-d');
        } elseif ($period == 'quarterly') {
            $quarter = ceil($month / 3);
            $fromDate = now()->setYear($year)->setMonth(($quarter - 1) * 3 + 1)->startOfMonth()->format('Y-m-d');
            $toDate = now()->setYear($year)->setMonth($quarter * 3)->endOfMonth()->format('Y-m-d');
        } else { // annually
            $fromDate = now()->setYear($year)->startOfYear()->format('Y-m-d');
            $toDate = now()->setYear($year)->endOfYear()->format('Y-m-d');
        }

        $taxes = Tax::where('is_active', true)->get();
        
        // Get all sales and purchases in period
        $sales = \App\Models\Sale::whereBetween('date', [$fromDate, $toDate])->get();
        $purchases = \App\Models\Purchase::whereBetween('date', [$fromDate, $toDate])->get();

        // Calculate totals
        $totalSales = $sales->sum('total_amount');
        $totalTaxCollected = $sales->sum('tax_amount');
        $totalPurchases = $purchases->sum('total_amount');
        $totalTaxPaid = $purchases->sum('tax_amount');

        // Estimate base amounts (total - tax)
        $salesBase = $totalSales - $totalTaxCollected;
        $purchaseBase = $totalPurchases - $totalTaxPaid;

        // Calculate declaration data (simplified without rate breakdown)
        $declaration = [
            // Sales (Output Tax) - assuming 20% standard rate
            'sales_base' => $salesBase,
            'sales_tax' => $totalTaxCollected,
            'sales_reduced_base' => 0,
            'sales_reduced_tax' => 0,
            'sales_exempt' => 0,
            
            // Purchases (Input Tax) - assuming 20% standard rate
            'purchase_base' => $purchaseBase,
            'purchase_tax' => $totalTaxPaid,
            'purchase_reduced_base' => 0,
            'purchase_reduced_tax' => 0,
            'purchase_exempt' => 0,
        ];

        // Calculate net tax (positive = payable, negative = refund)
        $declaration['net_tax'] = $totalTaxCollected - $totalTaxPaid;

        $stats = [
            'collected' => $totalTaxCollected,
            'paid' => $totalTaxPaid,
        ];
        
        return view('accounting.tax.declaration', compact('taxes', 'stats', 'declaration'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $tax = Tax::create($validated);
        return redirect()->route('accounting.tax.index')->with('success', __('app.created_success'));
    }

    public function show(Tax $tax)
    {
        return view('accounting.tax.show', compact('tax'));
    }

    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $tax->update($validated);
        return redirect()->route('accounting.tax.index')->with('success', __('app.updated_success'));
    }

    public function destroy(Tax $tax)
    {
        $tax->delete();
        return redirect()->route('accounting.tax.index')->with('success', __('app.deleted_success'));
    }
    
    // API endpoint for getting taxes as JSON (for forms, etc.)
    public function apiIndex()
    {
        return response()->json(Tax::where('is_active', true)->get());
    }
}
