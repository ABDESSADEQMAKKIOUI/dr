<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $expenses = Expense::with(['category', 'user'])->paginate(15);
        return view('expenses.index', compact('expenses'));
    }

    public function create(): View
    {
        $categories = ExpenseCategory::all();
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:5120', // Max 5MB
            'payment_method' => 'required|string|exists:payment_methods,code',
        ]);

        $data = [
            'expense_category_id' => $validated['category_id'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'user_id' => auth()->id(),
        ];

        // Handle Payment Method
        $paymentMethod = \App\Models\PaymentMethod::where('code', $validated['payment_method'])->first();
        $data['payment_method_id'] = $paymentMethod->id;

        // Handle Attachment Upload
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('expenses', 'public');
            $data['attachment'] = $path;
        }

        Expense::create($data);
        return redirect()->route('expenses.index')->with('success', __('app.created_success'));
    }

    public function show(Expense $expense): View
    {
        $expense->load('category', 'user', 'paymentMethod');
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense): View
    {
        $expense->load('paymentMethod');
        $categories = ExpenseCategory::all();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'reference' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:5120', // Max 5MB
            'payment_method' => 'required|string|exists:payment_methods,code',
        ]);

        $data = [
            'expense_category_id' => $validated['category_id'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'reference' => $validated['reference'] ?? null,
        ];

        // Handle Payment Method
        $paymentMethod = \App\Models\PaymentMethod::where('code', $validated['payment_method'])->first();
        $data['payment_method_id'] = $paymentMethod->id;

        // Handle Attachment Upload
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($expense->attachment) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($expense->attachment);
            }
            $path = $request->file('attachment')->store('expenses', 'public');
            $data['attachment'] = $path;
        } elseif ($request->has('remove_attachment') && $request->remove_attachment == '1') {
             if ($expense->attachment) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($expense->attachment);
            }
            $data['attachment'] = null;
        }

        $expense->update($data);
        return redirect()->route('expenses.index')->with('success', __('app.updated_success'));
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', __('app.deleted_success'));
    }

    public function categories(): View
    {
        $categories = ExpenseCategory::withCount('expenses')->paginate(15);
        return view('expenses.categories.index', compact('categories'));
    }

    public function categoriesCreate(): View
    {
        return view('expenses.categories.create');
    }

    public function categoriesStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        ExpenseCategory::create($validated);
        return redirect()->route('expenses.categories.index')->with('success', __('app.created_success'));
    }

    public function categoriesEdit(ExpenseCategory $category): View
    {
        return view('expenses.categories.edit', compact('category'));
    }

    public function categoriesUpdate(Request $request, ExpenseCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $category->update($validated);
        return redirect()->route('expenses.categories.index')->with('success', __('app.updated_success'));
    }

    public function categoriesDestroy(ExpenseCategory $category)
    {
        $category->delete();
        return redirect()->route('expenses.categories.index')->with('success', __('app.deleted_success'));
    }
}
