<?php

namespace App\Http\Controllers;

use App\Models\Warranty;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class WarrantyController extends Controller
{
    public function index(Request $request)
    {
        $warranties = Warranty::with(['product', 'customer'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where('serial_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('product', fn($p) => $p->where('name', 'like', '%' . $request->search . '%'))
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', '%' . $request->search . '%'));
            })
            ->latest()
            ->paginate(20);

        return view('warranties.index', compact('warranties'));
    }

    public function create()
    {
        $products  = Product::where('has_warranty', true)->where('is_active', true)->get();
        $customers = Customer::orderBy('name')->get();
        return view('warranties.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'    => 'required|exists:products,id',
            'customer_id'   => 'nullable|exists:customers,id',
            'serial_number' => 'nullable|string|max:255',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after:start_date',
            'notes'         => 'nullable|string',
        ]);

        $data['status'] = 'active';
        Warranty::create($data);

        return redirect()->route('warranties.index')->with('success', 'Warranty created.');
    }

    public function show(Warranty $warranty)
    {
        $warranty->load(['product', 'customer', 'saleItem.sale']);
        return view('warranties.show', compact('warranty'));
    }

    public function edit(Warranty $warranty)
    {
        $products  = Product::where('has_warranty', true)->where('is_active', true)->get();
        $customers = Customer::orderBy('name')->get();
        return view('warranties.edit', compact('warranty', 'products', 'customers'));
    }

    public function update(Request $request, Warranty $warranty)
    {
        $data = $request->validate([
            'status' => 'required|in:active,expired,claimed,voided',
            'notes'  => 'nullable|string',
        ]);

        $warranty->update($data);

        return redirect()->route('warranties.index')->with('success', 'Warranty updated.');
    }

    public function destroy(Warranty $warranty)
    {
        $warranty->delete();
        return redirect()->route('warranties.index')->with('success', 'Warranty deleted.');
    }
}
