<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $units = Unit::paginate(15);
        return view('products.units.index', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('products.units.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'operator' => 'nullable|string|in:*,-,+,/',
            'operation_value' => 'nullable|numeric',
        ]);

        Unit::create($validated);

        return redirect()->route('products.units.index')
            ->with('success', __('app.created_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit): View
    {
        return view('products.units.edit', compact('unit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'operator' => 'nullable|string|in:*,-,+,/',
            'operation_value' => 'nullable|numeric',
        ]);

        $unit->update($validated);

        return redirect()->route('products.units.index')
            ->with('success', __('app.updated_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        if ($unit->products()->exists() || $unit->saleProducts()->exists() || $unit->purchaseProducts()->exists()) {
            return back()->with('error', 'Cannot delete unit with associated products.');
        }

        $unit->delete();

        return redirect()->route('products.units.index')
            ->with('success', __('app.deleted_success'));
    }
}
