<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Customer;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function index(Request $request)
    {
        $opportunities = Opportunity::with(['customer', 'assignedTo'])
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->when($request->stage, fn($q) => $q->where('stage', $request->stage))
            ->latest()
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($opportunities);
        }

        return view('crm.opportunities.index', compact('opportunities'));
    }

    public function create()
    {
        $customers = Customer::select('id', 'name')->orderBy('name')->get();
        return view('crm.opportunities.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'title'               => 'required|string|max:255',
            'value'               => 'nullable|numeric|min:0',
            'probability'         => 'nullable|integer|min:0|max:100',
            'stage'               => 'nullable|string|in:prospecting,proposal,negotiation,won,lost',
            'expected_close_date' => 'nullable|date',
            'assigned_to'         => 'nullable|exists:users,id',
            'notes'               => 'nullable|string',
        ]);

        $opportunity = Opportunity::create($validated);

        if ($request->expectsJson()) {
            return response()->json($opportunity->load(['customer', 'assignedTo']), 201);
        }

        return redirect()->route('crm.opportunities.index')
            ->with('success', __('app.created_success'));
    }

    public function show(Opportunity $opportunity)
    {
        $opportunity->load(['customer', 'assignedTo']);

        if (request()->expectsJson()) {
            return response()->json($opportunity);
        }

        return view('crm.opportunities.show', compact('opportunity'));
    }

    public function edit(Opportunity $opportunity)
    {
        $customers = Customer::select('id', 'name')->orderBy('name')->get();
        return view('crm.opportunities.edit', compact('opportunity', 'customers'));
    }

    public function update(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'customer_id'         => 'required|exists:customers,id',
            'title'               => 'required|string|max:255',
            'value'               => 'nullable|numeric|min:0',
            'probability'         => 'nullable|integer|min:0|max:100',
            'stage'               => 'nullable|string|in:prospecting,proposal,negotiation,won,lost',
            'expected_close_date' => 'nullable|date',
            'assigned_to'         => 'nullable|exists:users,id',
            'notes'               => 'nullable|string',
        ]);

        $opportunity->update($validated);

        if ($request->expectsJson()) {
            return response()->json($opportunity->fresh(['customer', 'assignedTo']));
        }

        return redirect()->route('crm.opportunities.index')
            ->with('success', __('app.updated_success'));
    }

    public function destroy(Opportunity $opportunity)
    {
        $opportunity->delete();

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('crm.opportunities.index')
            ->with('success', __('app.deleted_success'));
    }

    public function win(Opportunity $opportunity)
    {
        $opportunity->update(['stage' => 'won', 'probability' => 100]);

        if (request()->expectsJson()) {
            return response()->json($opportunity->fresh());
        }

        return back()->with('success', __('app.updated_success'));
    }

    public function lose(Opportunity $opportunity)
    {
        $opportunity->update(['stage' => 'lost', 'probability' => 0]);

        if (request()->expectsJson()) {
            return response()->json($opportunity->fresh());
        }

        return back()->with('success', __('app.updated_success'));
    }
}
