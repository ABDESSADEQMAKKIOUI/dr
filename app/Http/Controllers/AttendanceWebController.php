<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class AttendanceWebController extends Controller
{
    public function index(Request $request): View
    {
        $query = Attendance::with('employee.user')
            ->orderBy('date', 'desc')
            ->orderBy('employee_id');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        $attendance = $query->paginate(25);
        $employees  = Employee::with('user')->where('is_active', true)->get();

        $allForStats = $query->get();
        $stats = [
            'present' => $allForStats->whereIn('status', ['present', 'late'])->count(),
            'absent'  => $allForStats->where('status', 'absent')->count(),
            'late'    => $allForStats->where('status', 'late')->count(),
            'leave'   => $allForStats->where('status', 'half_day')->count(),
        ];

        return view('employees.attendance.index', compact('attendance', 'employees', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'check_in'    => 'nullable|date_format:H:i',
            'check_out'   => 'nullable|date_format:H:i',
            'status'      => 'required|in:present,absent,late,half_day',
            'notes'       => 'nullable|string',
        ]);

        // Calculate work hours if both check_in and check_out given
        $workHours = null;
        if ($request->check_in && $request->check_out) {
            $in  = Carbon::createFromFormat('H:i', $request->check_in);
            $out = Carbon::createFromFormat('H:i', $request->check_out);
            $workHours = $out->gt($in) ? round($out->diffInMinutes($in) / 60, 2) : null;
        }

        Attendance::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            [
                'check_in'   => $request->check_in,
                'check_out'  => $request->check_out,
                'status'     => $request->status,
                'work_hours' => $workHours,
                'notes'      => $request->notes,
            ]
        );

        return back()->with('success', __('app.attendance_saved') ?? 'Attendance saved.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return back()->with('success', __('app.deleted_success'));
    }
}
