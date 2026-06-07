<?php

namespace App\Http\Controllers;

use App\Services\SupplierService;
use App\Models\Supplier;
use App\Imports\SupplierImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function __construct(protected SupplierService $supplierService) {}

    public function index(Request $request): View
    {
        $suppliers       = $this->supplierService->list($request->all());
        $totalSuppliers  = Supplier::count();
        $activeSuppliers = Supplier::where('is_active', true)->count();
        $withBalance     = 0;
        return view('suppliers.index', compact('suppliers', 'totalSuppliers', 'activeSuppliers', 'withBalance'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string',
        ]);

        $this->supplierService->create($validated);
        return redirect()->route('suppliers.index')->with('success', __('app.created_success'));
    }

    public function show(Supplier $supplier): View
    {
        $recentPurchases  = $supplier->purchases()->latest()->take(10)->get();
        $purchasesCount   = $supplier->purchases()->count();
        $totalSpent       = $supplier->purchases()->sum('total_amount');
        $totalPaid        = $supplier->purchases()->sum('paid_amount');
        $balanceDue       = max(0, $totalSpent - $totalPaid);
        return view('suppliers.show', compact('supplier', 'recentPurchases', 'purchasesCount', 'totalSpent', 'balanceDue'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $this->supplierService->update($supplier, $validated);
        return redirect()->route('suppliers.index')->with('success', __('app.updated_success'));
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $this->supplierService->delete($supplier);
            return redirect()->route('suppliers.index')->with('success', __('app.deleted_success'));
        } catch (\Exception $e) {
            return redirect()->route('suppliers.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Suppliers with outstanding balance (due report).
     */
    public function due(): View
    {
        $suppliers = Supplier::withSum('purchases as total_purchase_amount', 'total_amount')
            ->withSum('purchases as total_paid_amount', 'paid_amount')
            ->having(\DB::raw('(total_purchase_amount - total_paid_amount)'), '>', 0)
            ->orderByRaw('(total_purchase_amount - total_paid_amount) DESC')
            ->get()
            ->map(function ($s) {
                $s->due_amount = ($s->total_purchase_amount ?? 0) - ($s->total_paid_amount ?? 0);
                return $s;
            });

        return view('suppliers.due', compact('suppliers'));
    }

    /**
     * Record a payment covering the supplier's full outstanding balance.
     */
    public function payDue(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'notes'          => 'nullable|string|max:500',
        ]);

        $unpaidPurchases = $supplier->purchases()
            ->where('payment_status', '!=', 'paid')
            ->orderBy('date')
            ->get();

        $remaining = $validated['amount'];

        foreach ($unpaidPurchases as $purchase) {
            if ($remaining <= 0) break;
            $due = $purchase->total_amount - $purchase->paid_amount;
            $paying = min($due, $remaining);
            $purchase->paid_amount += $paying;
            $purchase->payment_status = $purchase->paid_amount >= $purchase->total_amount ? 'paid' : 'partial';
            $purchase->save();
            $remaining -= $paying;
        }

        return redirect()->route('suppliers.due')->with('success', 'Payment of ' . number_format($validated['amount'], 2) . ' DH recorded for ' . $supplier->name . '.');
    }

    /**
     * Import suppliers from CSV/Excel.
     */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls|max:5120']);

        $import = new SupplierImport();
        Excel::import($import, $request->file('file'));

        $failures = $import->failures();
        $msg = $import->getImportedCount() . ' suppliers imported, ' . $import->getSkippedCount() . ' duplicates skipped.';

        if ($failures->count()) {
            $msg .= ' ' . $failures->count() . ' rows failed validation.';
        }

        return redirect()->route('suppliers.index')->with('success', $msg);
    }

    /**
     * Export suppliers to CSV.
     */
    public function export()
    {
        $suppliers = Supplier::all();

        return response()->streamDownload(function () use ($suppliers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'email', 'phone', 'address', 'city', 'country', 'tax_number']);
            foreach ($suppliers as $s) {
                fputcsv($handle, [$s->name, $s->email, $s->phone, $s->address, $s->city, $s->country, $s->tax_number]);
            }
            fclose($handle);
        }, 'suppliers_' . date('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Download sample CSV template.
     */
    public function downloadSample()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'email', 'phone', 'address', 'city', 'country', 'tax_number']);
            fputcsv($handle, ['Supplier Co.', 'info@supplier.com', '+212600000000', '456 Industrial Ave', 'Rabat', 'Morocco', 'TAX456']);
            fclose($handle);
        }, 'suppliers_template.csv', ['Content-Type' => 'text/csv']);
    }
}
