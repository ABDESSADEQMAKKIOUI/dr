<?php

namespace App\Http\Controllers;

use App\Services\CustomerService;
use App\Models\Customer;
use App\Imports\CustomerImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(protected CustomerService $customerService) {}

    public function index(Request $request): View
    {
        $customers       = $this->customerService->list($request->all());
        $totalCustomers  = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->count();
        $withBalance     = 0;
        return view('customers.index', compact('customers', 'totalCustomers', 'activeCustomers', 'withBalance'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric',
        ]);

        $this->customerService->create($validated);
        return redirect()->route('customers.index')->with('success', __('app.created_success'));
    }

    public function show(Customer $customer): View
    {
        $recentSales  = $customer->sales()->latest()->take(10)->get();
        $salesCount   = $customer->sales()->count();
        $totalRevenue = $customer->sales()->sum('total_amount');
        $totalPaid    = $customer->sales()->sum('paid_amount');
        $balanceDue   = max(0, $totalRevenue - $totalPaid);

        return view('customers.show', compact('customer', 'recentSales', 'salesCount', 'totalRevenue', 'balanceDue'));
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $this->customerService->update($customer, $validated);
        return redirect()->route('customers.index')->with('success', __('app.updated_success'));
    }

    public function destroy(Customer $customer)
    {
        try {
            $this->customerService->delete($customer);
            return redirect()->route('customers.index')->with('success', __('app.deleted_success'));
        } catch (\Exception $e) {
            return redirect()->route('customers.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Customers with outstanding balance (due report).
     */
    public function due(Request $request): View
    {
        $customers = Customer::withSum('sales as total_sales_amount', 'total_amount')
            ->withSum('sales as total_paid_amount', 'paid_amount')
            ->having(\DB::raw('(total_sales_amount - total_paid_amount)'), '>', 0)
            ->orderByRaw('(total_sales_amount - total_paid_amount) DESC')
            ->get()
            ->map(function ($c) {
                $c->due_amount = ($c->total_sales_amount ?? 0) - ($c->total_paid_amount ?? 0);
                return $c;
            });

        return view('customers.due', compact('customers'));
    }

    /**
     * Record a payment covering the customer's full outstanding balance.
     */
    public function payDue(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'notes'          => 'nullable|string|max:500',
        ]);

        $unpaidSales = $customer->sales()
            ->where('payment_status', '!=', 'paid')
            ->orderBy('date')
            ->get();

        $remaining = $validated['amount'];

        foreach ($unpaidSales as $sale) {
            if ($remaining <= 0) break;
            $due = $sale->total_amount - $sale->paid_amount;
            $paying = min($due, $remaining);
            $sale->paid_amount += $paying;
            $sale->payment_status = $sale->paid_amount >= $sale->total_amount ? 'paid' : 'partial';
            $sale->save();
            $remaining -= $paying;
        }

        return redirect()->route('customers.due')->with('success', 'Payment of ' . number_format($validated['amount'], 2) . ' DH recorded for ' . $customer->name . '.');
    }

    /**
     * Import customers from CSV/Excel.
     */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls|max:5120']);

        $import = new CustomerImport();
        Excel::import($import, $request->file('file'));

        $failures = $import->failures();
        $msg = $import->getImportedCount() . ' customers imported, ' . $import->getSkippedCount() . ' duplicates skipped.';

        if ($failures->count()) {
            $msg .= ' ' . $failures->count() . ' rows failed validation.';
        }

        return redirect()->route('customers.index')->with('success', $msg);
    }

    /**
     * Export customers to CSV.
     */
    public function export()
    {
        $customers = Customer::all();

        return response()->streamDownload(function () use ($customers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'email', 'phone', 'address', 'city', 'postal_code', 'country', 'tax_number']);
            foreach ($customers as $c) {
                fputcsv($handle, [$c->name, $c->email, $c->phone, $c->address, $c->city, $c->postal_code, $c->country, $c->tax_number]);
            }
            fclose($handle);
        }, 'customers_' . date('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Download sample CSV template.
     */
    public function downloadSample()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'email', 'phone', 'address', 'city', 'postal_code', 'country', 'tax_number']);
            fputcsv($handle, ['John Doe', 'john@example.com', '+212600000000', '123 Main St', 'Casablanca', '20000', 'Morocco', 'TAX123']);
            fclose($handle);
        }, 'customers_template.csv', ['Content-Type' => 'text/csv']);
    }
}
