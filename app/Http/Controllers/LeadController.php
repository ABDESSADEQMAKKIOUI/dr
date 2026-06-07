<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Customer;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $leads = Lead::with('assignedTo')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($leads);
        }

        return view('crm.leads.index', compact('leads'));
    }

    public function create()
    {
        return view('crm.leads.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'source'      => 'nullable|string|max:100',
            'status'      => 'nullable|string|in:new,contacted,qualified,lost',
            'assigned_to' => 'nullable|exists:users,id',
            'notes'       => 'nullable|string',
        ]);

        $lead = Lead::create($validated);

        if ($request->expectsJson()) {
            return response()->json($lead->load('assignedTo'), 201);
        }

        return redirect()->route('crm.leads.index')
            ->with('success', __('app.created_success'));
    }

    public function show(Lead $lead)
    {
        $lead->load('assignedTo');

        if (request()->expectsJson()) {
            return response()->json($lead);
        }

        return view('crm.leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        return view('crm.leads.edit', compact('lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'source'      => 'nullable|string|max:100',
            'status'      => 'nullable|string|in:new,contacted,qualified,lost',
            'assigned_to' => 'nullable|exists:users,id',
            'notes'       => 'nullable|string',
        ]);

        $lead->update($validated);

        if ($request->expectsJson()) {
            return response()->json($lead->fresh('assignedTo'));
        }

        return redirect()->route('crm.leads.index')
            ->with('success', __('app.updated_success'));
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        if (request()->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('crm.leads.index')
            ->with('success', __('app.deleted_success'));
    }

    public function convertToCustomer(Lead $lead)
    {
        $customer = Customer::firstOrCreate(
            ['email' => $lead->email],
            [
                'name'  => $lead->name,
                'phone' => $lead->phone,
            ]
        );

        $lead->update(['status' => 'qualified']);

        if (request()->expectsJson()) {
            return response()->json(['customer' => $customer, 'lead' => $lead]);
        }

        return redirect()->route('customers.show', $customer)
            ->with('success', __('app.lead_converted'));
    }
}
