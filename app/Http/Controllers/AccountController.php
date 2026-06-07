<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $accounts = Account::paginate(15);
        return view('accounting.accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        $parentAccounts = Account::whereNull('parent_id')->get();
        $accountTypes = \App\Models\AccountType::all();
        return view('accounting.accounts.create', compact('parentAccounts', 'accountTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'required|string|unique:accounts',
            'pcg_class'           => 'nullable|integer|between:1,7',
            'type'                => 'nullable|string|max:50',
            'parent_id'           => 'nullable|exists:accounts,id',
            'description'         => 'nullable|string',
            'is_active'           => 'nullable|boolean',
            'opening_balance'     => 'nullable|numeric',
            'currency'            => 'nullable|string|max:3',
            'acquisition_date'    => 'nullable|date',
            'amortization_rate'   => 'nullable|numeric|min:0|max:100',
            'useful_life_years'   => 'nullable|integer|min:1',
            'valuation_method'    => 'nullable|in:fifo,lifo,cmup',
            'vat_rate'            => 'nullable|numeric|min:0',
            'payment_terms_days'  => 'nullable|integer|min:0',
            'bank_name'           => 'nullable|string|max:255',
            'iban'                => 'nullable|string|max:40',
            'budget_amount'       => 'nullable|numeric|min:0',
            'vat_deductible'      => 'nullable|boolean',
        ]);

        $type = $request->input('type', 'asset');
        $accountType = \App\Models\AccountType::firstOrCreate(
            ['name' => ucfirst($type)],
            ['code' => strtoupper(substr($type, 0, 3))]
        );

        Account::create([
            'account_type_id'   => $accountType->id,
            'name'              => $request->name,
            'code'              => $request->code,
            'parent_id'         => $request->parent_id,
            'description'       => $request->description,
            'is_active'         => $request->boolean('is_active', true),
            'pcg_class'         => $request->pcg_class,
            'normal_balance'    => $request->normal_balance,
            'opening_balance'   => $request->opening_balance ?? 0,
            'currency'          => $request->currency ?? 'MAD',
            'acquisition_date'  => $request->acquisition_date,
            'amortization_rate' => $request->amortization_rate,
            'useful_life_years' => $request->useful_life_years,
            'valuation_method'  => $request->valuation_method,
            'vat_rate'          => $request->vat_rate,
            'payment_terms_days'=> $request->payment_terms_days,
            'bank_name'         => $request->bank_name,
            'iban'              => $request->iban,
            'budget_amount'     => $request->budget_amount,
            'vat_deductible'    => $request->boolean('vat_deductible'),
        ]);

        return redirect()->route('accounting.accounts.index')->with('success', __('app.created_success'));
    }

    public function show(Account $account): View
    {
        $account->load(['accountType', 'parent', 'children', 'transactions' => fn($q) => $q->latest()->take(10)]);
        return view('accounting.accounts.show', compact('account'));
    }

    public function edit(Account $account): View
    {
        $account->load('accountType');
        $parentAccounts = Account::whereNull('parent_id')->where('id', '!=', $account->id)->get();
        return view('accounting.accounts.edit', compact('account', 'parentAccounts'));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:accounts,code,' . $account->id,
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Get or create account type
        $accountType = \App\Models\AccountType::firstOrCreate(
            ['name' => ucfirst($validated['type'])],
            ['code' => strtoupper(substr($validated['type'], 0, 3))]
        );

        $data = [
            'account_type_id' => $accountType->id,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'parent_id' => $validated['parent_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        $account->update($data);
        return redirect()->route('accounting.accounts.index')->with('success', __('app.updated_success'));
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return redirect()->route('accounting.accounts.index')->with('success', __('app.deleted_success'));
    }

    public function journals(): View
    {
        return view('accounting.journals.index');
    }
}
