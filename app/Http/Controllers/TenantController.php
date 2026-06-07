<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $tenants = Tenant::withCount('users')->paginate(15);
        return view('tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|unique:tenants',
            'plan' => 'required|in:trial,basic,professional,enterprise',
        ]);

        Tenant::create($validated);
        return redirect()->route('tenants.index')->with('success', __('app.created_success'));
    }

    public function show(Tenant $tenant): View
    {
        return view('tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant): View
    {
        return view('tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|unique:tenants,domain,' . $tenant->id,
            'plan' => 'required|in:trial,basic,professional,enterprise',
            'is_active' => 'boolean',
        ]);

        $tenant->update($validated);
        return redirect()->route('tenants.index')->with('success', __('app.updated_success'));
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('tenants.index')->with('success', __('app.deleted_success'));
    }
}
