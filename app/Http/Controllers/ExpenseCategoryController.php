<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = ExpenseCategory::withCount('expenses')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($categories);
        }

        return view('expenses.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('expenses.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:expense_categories,name',
            'description' => 'nullable|string',
        ]);

        $category = ExpenseCategory::create($validated);

        if ($request->expectsJson()) {
            return response()->json($category, 201);
        }

        return redirect()->route('expenses.categories.index')
            ->with('success', __('app.created_success'));
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->loadCount('expenses');

        if (request()->expectsJson()) {
            return response()->json($expenseCategory);
        }

        return view('expenses.categories.show', ['category' => $expenseCategory]);
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expenses.categories.edit', ['category' => $expenseCategory]);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id,
            'description' => 'nullable|string',
        ]);

        $expenseCategory->update($validated);

        if ($request->expectsJson()) {
            return response()->json($expenseCategory);
        }

        return redirect()->route('expenses.categories.index')
            ->with('success', __('app.updated_success'));
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $expenseCategory->delete();

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('expenses.categories.index')
            ->with('success', __('app.deleted_success'));
    }
}
