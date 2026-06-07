<?php

namespace App\Http\Controllers;

use App\Models\ComboProduct;
use App\Models\ComboProductItem;
use App\Models\Product;
use Illuminate\Http\Request;

class ComboProductController extends Controller
{
    public function index()
    {
        $combos = ComboProduct::withCount('items')->latest()->paginate(20);
        return view('combos.index', compact('combos'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('combos.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'sku'         => 'nullable|string|max:100|unique:combo_products',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $combo = ComboProduct::create([
            'name'        => $data['name'],
            'sku'         => $data['sku'] ?? ('CMB-' . strtoupper(\Str::random(6))),
            'price'       => $data['price'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        foreach ($data['items'] as $item) {
            ComboProductItem::create([
                'combo_id'   => $combo->id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
            ]);
        }

        return redirect()->route('combos.index')->with('success', 'Combo product created.');
    }

    public function show(ComboProduct $combo)
    {
        $combo->load('items.product');
        return view('combos.show', compact('combo'));
    }

    public function edit(ComboProduct $combo)
    {
        $combo->load('items.product');
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('combos.edit', compact('combo', 'products'));
    }

    public function update(Request $request, ComboProduct $combo)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $combo->update([
            'name'        => $data['name'],
            'price'       => $data['price'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        $combo->items()->delete();
        foreach ($data['items'] as $item) {
            ComboProductItem::create([
                'combo_id'   => $combo->id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
            ]);
        }

        return redirect()->route('combos.index')->with('success', 'Combo product updated.');
    }

    public function destroy(ComboProduct $combo)
    {
        $combo->items()->delete();
        $combo->delete();
        return redirect()->route('combos.index')->with('success', 'Combo product deleted.');
    }
}
