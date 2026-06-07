<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\DepositCategory;
use App\Models\Account;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        $deposits = Deposit::with(['category', 'account'])
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->from, fn($q) => $q->whereDate('date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('date', '<=', $request->to))
            ->latest('date')
            ->paginate(25);

        $categories = DepositCategory::orderBy('name')->get();
        $total = Deposit::sum('amount');

        return view('deposits.index', compact('deposits', 'categories', 'total'));
    }

    public function create()
    {
        $categories = DepositCategory::orderBy('name')->get();
        $accounts   = Account::orderBy('name')->get();
        return view('deposits.create', compact('categories', 'accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'category_id'    => 'nullable|exists:deposit_categories,id',
            'account_id'     => 'nullable|exists:accounts,id',
            'reference'      => 'nullable|string|max:255',
            'date'           => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes'          => 'nullable|string',
        ]);

        Deposit::create($data);

        return redirect()->route('deposits.index')->with('success', 'Deposit recorded.');
    }

    public function show(Deposit $deposit)
    {
        $deposit->load(['category', 'account']);
        return view('deposits.show', compact('deposit'));
    }

    public function edit(Deposit $deposit)
    {
        $categories = DepositCategory::orderBy('name')->get();
        $accounts   = Account::orderBy('name')->get();
        return view('deposits.edit', compact('deposit', 'categories', 'accounts'));
    }

    public function update(Request $request, Deposit $deposit)
    {
        $data = $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'category_id'    => 'nullable|exists:deposit_categories,id',
            'account_id'     => 'nullable|exists:accounts,id',
            'reference'      => 'nullable|string|max:255',
            'date'           => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes'          => 'nullable|string',
        ]);

        $deposit->update($data);

        return redirect()->route('deposits.index')->with('success', 'Deposit updated.');
    }

    public function destroy(Deposit $deposit)
    {
        $deposit->delete();
        return redirect()->route('deposits.index')->with('success', 'Deposit deleted.');
    }
}
