<?php

namespace App\Http\Controllers;

use App\Models\MoneyTransfer;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoneyTransferController extends Controller
{
    public function index(Request $request)
    {
        $transfers = MoneyTransfer::with(['fromAccount', 'toAccount'])
            ->when($request->from, fn($q) => $q->whereDate('date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('date', '<=', $request->to))
            ->latest('date')
            ->paginate(25);

        $accounts = Account::orderBy('name')->get();

        return view('money-transfers.index', compact('transfers', 'accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'from_account_id' => 'required|exists:accounts,id|different:to_account_id',
            'to_account_id'   => 'required|exists:accounts,id',
            'amount'          => 'required|numeric|min:0.01',
            'fee'             => 'nullable|numeric|min:0',
            'date'            => 'required|date',
            'reference'       => 'nullable|string|max:255',
            'notes'           => 'nullable|string',
        ]);

        $data['fee'] = $data['fee'] ?? 0;
        MoneyTransfer::create($data);

        return redirect()->route('money-transfers.index')->with('success', 'Transfer recorded.');
    }

    public function show(MoneyTransfer $moneyTransfer)
    {
        $moneyTransfer->load(['fromAccount', 'toAccount']);
        return view('money-transfers.show', compact('moneyTransfer'));
    }

    public function destroy(MoneyTransfer $moneyTransfer)
    {
        $moneyTransfer->delete();
        return redirect()->route('money-transfers.index')->with('success', 'Transfer deleted.');
    }
}
