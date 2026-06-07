<?php

namespace App\Services;

use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceService
{
    public function clockIn(int $employeeId, array $data): Attendance
    {
        return Attendance::create([
            'employee_id' => $employeeId,
            'date' => $data['date'] ?? now()->toDateString(),
            'clock_in' => $data['clock_in'] ?? now(),
            'status' => 'present',
        ]);
    }

    public function clockOut(Attendance $attendance, ?string $clockOut = null): Attendance
    {
        $clockOutTime = $clockOut ? Carbon::parse($clockOut) : now();
        $clockInTime = Carbon::parse($attendance->clock_in);
        
        $attendance->update([
            'clock_out' => $clockOutTime,
            'total_hours' => $clockOutTime->diffInHours($clockInTime),
        ]);

        return $attendance;
    }

    public function getEmployeeAttendance(int $employeeId, string $month)
    {
        return Attendance::where('employee_id', $employeeId)
            ->whereMonth('date', Carbon::parse($month)->month)
            ->whereYear('date', Carbon::parse($month)->year)
            ->get();
    }

    public function getStats(array $filters = []): array
    {
        $query = Attendance::query();

        if (isset($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        return [
            'present' => $query->where('status', 'present')->count(),
            'late' => $query->where('status', 'late')->count(),
            'absent' => $query->where('status', 'absent')->count(),
        ];
    }
}
