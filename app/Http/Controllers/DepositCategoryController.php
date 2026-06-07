<?php

namespace App\Http\Controllers;

use App\Models\DepositCategory;
use Illuminate\Http\Request;

class DepositCategoryController extends Controller
{
    public function index()
    {
        $categories = DepositCategory::withCount('deposits')->get();
        return view('deposit-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:deposit_categories',
            'description' => 'nullable|string',
        ]);

        DepositCategory::create($data);
        return redirect()->route('deposit-categories.index')->with('success', 'Category created.');
    }

    public function update(Request $request, DepositCategory $depositCategory)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:deposit_categories,name,' . $depositCategory->id,
            'description' => 'nullable|string',
        ]);

        $depositCategory->update($data);
        return redirect()->route('deposit-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(DepositCategory $depositCategory)
    {
        $depositCategory->delete();
        return redirect()->route('deposit-categories.index')->with('success', 'Category deleted.');
    }
}
