<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function index(Request $request): View
    {
        $query = Leave::with(['employee.user', 'approver']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves    = $query->latest()->paginate(20);
        $employees = Employee::with('user')->where('is_active', true)->get();

        $stats = [
            'pending'  => Leave::where('status', 'pending')->count(),
            'approved' => Leave::where('status', 'approved')->count(),
            'rejected' => Leave::where('status', 'rejected')->count(),
            'total'    => Leave::count(),
        ];

        return view('employees.leaves.index', compact('leaves', 'employees', 'stats'));
    }

    public function create(): View
    {
        $employees = Employee::with('user')->where('is_active', true)->get();
        return view('employees.leaves.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|string|max:50',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'nullable|string',
        ]);

        $days = \Carbon\Carbon::parse($request->start_date)
            ->diffInWeekdays(\Carbon\Carbon::parse($request->end_date)) + 1;

        Leave::create([
            'employee_id' => $request->employee_id,
            'type'        => $request->type,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'days'        => $days,
            'reason'      => $request->reason,
            'status'      => 'pending',
        ]);

        return redirect()->route('employees.leaves.index')
            ->with('success', __('app.leave_submitted') ?? 'Leave request submitted.');
    }

    public function show(Leave $leave): View
    {
        $leave->load(['employee.user', 'employee.department', 'employee.designation', 'approver']);
        return view('employees.leaves.show', compact('leave'));
    }

    public function updateStatus(Request $request, Leave $leave)
    {
        $request->validate(['status' => 'required|in:approved,rejected']);

        $leave->update([
            'status'      => $request->status,
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', __('app.leave_status_updated') ?? 'Leave status updated.');
    }

    public function destroy(Leave $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', __('app.cannot_delete_processed_leave') ?? 'Cannot delete a processed leave request.');
        }
        $leave->delete();
        return redirect()->route('employees.leaves.index')->with('success', __('app.deleted_success'));
    }
}
