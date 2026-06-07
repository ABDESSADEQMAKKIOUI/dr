<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = Transaction::with(['account'])->paginate(15);
        return view('accounting.transactions.index', compact('transactions'));
    }

    public function create(): View
    {
        $accounts = Account::all();
        return view('accounting.transactions.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        Transaction::create($validated);
        return redirect()->route('accounting.transactions.index')->with('success', __('app.created_success'));
    }

    public function show(Transaction $transaction): View
    {
        return view('accounting.transactions.show', compact('transaction'));
    }
}
