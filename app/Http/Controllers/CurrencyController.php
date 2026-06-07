<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        return response()->json(Currency::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:currencies',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'is_default' => 'boolean',
        ]);

        // Si is_default = true, mettre les autres à false
        if ($validated['is_default'] ?? false) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        $currency = Currency::create($validated);
        return response()->json($currency, 201);
    }

    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'exchange_rate' => 'required|numeric|min:0',
            'is_default' => 'boolean',
        ]);

        if ($validated['is_default'] ?? false) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        $currency->update($validated);
        return response()->json($currency);
    }

    public function destroy(Currency $currency)
    {
        if ($currency->is_default) {
            return response()->json(['error' => 'Impossible de supprimer la devise par défaut'], 400);
        }

        $currency->delete();
        return response()->json(['message' => 'Devise supprimée']);
    }
}
