<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::withCount('employees')->orderBy('name')->paginate(20);
        return view('departments.index', compact('departments'));
    }

    public function create(): View
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string',
        ]);

        Department::create($request->only('name', 'description'));
        return redirect()->route('departments.index')->with('success', __('app.created_success'));
    }

    public function show(Department $department): View
    {
        $department->loadCount('employees');
        $employees = $department->employees()
            ->with(['user', 'designation'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('departments.show', compact('department', 'employees'));
    }

    public function edit(Department $department): View
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
        ]);

        $department->update($request->only('name', 'description'));
        return redirect()->route('departments.show', $department)->with('success', __('app.updated_success'));
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->exists()) {
            return back()->with('error', __('app.cannot_delete_has_employees') ?? 'Cannot delete: department has employees.');
        }
        $department->delete();
        return redirect()->route('departments.index')->with('success', __('app.deleted_success'));
    }
}
