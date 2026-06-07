<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $warehouses = Warehouse::withCount('products')->paginate(15);
        return view('stock.warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('stock.warehouses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Map 'location' to 'address' for the database
        if (isset($validated['location'])) {
            $validated['address'] = $validated['location'];
            unset($validated['location']);
        }

        // Handle checkbox - if not sent, default to false
        $validated['is_active'] = $request->has('status');

        Warehouse::create($validated);
        return redirect()->route('stock.warehouses.index')->with('success', __('app.created_success'));
    }

    public function show(Warehouse $warehouse): View
    {
        return view('stock.warehouses.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('stock.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $warehouse->update($validated);
        return redirect()->route('stock.warehouses.index')->with('success', __('app.updated_success'));
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()->route('stock.warehouses.index')->with('success', __('app.deleted_success'));
    }
}
