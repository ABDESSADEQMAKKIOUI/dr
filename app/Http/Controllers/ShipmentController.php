<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $shipments = Shipment::with(['sale', 'customer'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->search . '%')
                  ->orWhere('tracking_number', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(25);

        return view('shipments.index', compact('shipments'));
    }

    public function create()
    {
        $sales     = Sale::with('customer')->latest()->limit(100)->get();
        $customers = Customer::orderBy('name')->get();
        return view('shipments.create', compact('sales', 'customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sale_id'            => 'nullable|exists:sales,id',
            'customer_id'        => 'nullable|exists:customers,id',
            'carrier'            => 'nullable|string|max:255',
            'tracking_number'    => 'nullable|string|max:255',
            'estimated_delivery' => 'nullable|date',
            'delivery_address'   => 'nullable|string',
            'notes'              => 'nullable|string',
        ]);

        $data['status'] = 'pending';
        $shipment = Shipment::create($data);

        return redirect()->route('shipments.show', $shipment)->with('success', 'Shipment created.');
    }

    public function show(Shipment $shipment)
    {
        $shipment->load(['sale.items.product', 'customer']);
        return view('shipments.show', compact('shipment'));
    }

    public function edit(Shipment $shipment)
    {
        $sales     = Sale::with('customer')->latest()->limit(100)->get();
        $customers = Customer::orderBy('name')->get();
        return view('shipments.edit', compact('shipment', 'sales', 'customers'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        $data = $request->validate([
            'status'             => 'required|in:pending,shipped,delivered,cancelled',
            'carrier'            => 'nullable|string|max:255',
            'tracking_number'    => 'nullable|string|max:255',
            'estimated_delivery' => 'nullable|date',
            'delivered_at'       => 'nullable|date',
            'delivery_address'   => 'nullable|string',
            'notes'              => 'nullable|string',
        ]);

        $shipment->update($data);

        return redirect()->route('shipments.show', $shipment)->with('success', 'Shipment updated.');
    }

    public function destroy(Shipment $shipment)
    {
        $shipment->delete();
        return redirect()->route('shipments.index')->with('success', 'Shipment deleted.');
    }
}
