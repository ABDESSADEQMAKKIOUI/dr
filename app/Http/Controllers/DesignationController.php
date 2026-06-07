<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesignationController extends Controller
{
    public function index(): View
    {
        $designations = Designation::withCount('employees')->orderBy('name')->paginate(20);
        return view('designations.index', compact('designations'));
    }

    public function create(): View
    {
        return view('designations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:designations',
            'description' => 'nullable|string',
        ]);

        Designation::create($request->only('name', 'description'));
        return redirect()->route('designations.index')->with('success', __('app.created_success'));
    }

    public function show(Designation $designation): View
    {
        $designation->loadCount('employees');
        $employees = $designation->employees()
            ->with(['user', 'department'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('designations.show', compact('designation', 'employees'));
    }

    public function edit(Designation $designation): View
    {
        return view('designations.edit', compact('designation'));
    }

    public function update(Request $request, Designation $designation)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:designations,name,' . $designation->id,
            'description' => 'nullable|string',
        ]);

        $designation->update($request->only('name', 'description'));
        return redirect()->route('designations.show', $designation)->with('success', __('app.updated_success'));
    }

    public function destroy(Designation $designation)
    {
        if ($designation->employees()->exists()) {
            return back()->with('error', __('app.cannot_delete_has_employees') ?? 'Cannot delete: designation has employees.');
        }
        $designation->delete();
        return redirect()->route('designations.index')->with('success', __('app.deleted_success'));
    }
}
