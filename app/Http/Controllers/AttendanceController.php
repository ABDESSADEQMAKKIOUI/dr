<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService) {}

    public function clockIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'nullable|date',
            'clock_in' => 'nullable|date_format:H:i',
        ]);

        $attendance = $this->attendanceService->clockIn($validated['employee_id'], $validated);
        return response()->json($attendance, 201);
    }

    public function clockOut(Request $request, Attendance $attendance)
    {
        $attendance = $this->attendanceService->clockOut($attendance, $request->clock_out);
        return response()->json($attendance);
    }

    public function employeeAttendance(Request $request, int $employeeId)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $attendance = $this->attendanceService->getEmployeeAttendance($employeeId, $month);
        return response()->json($attendance);
    }

    public function stats(Request $request)
    {
        return response()->json($this->attendanceService->getStats($request->all()));
    }
}
