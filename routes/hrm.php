<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PayrollController;

Route::middleware(['auth:sanctum'])->prefix('hrm')->group(function () {
    // Employees
    Route::apiResource('employees', EmployeeController::class);

    // Attendance
    Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('attendance/{attendance}/clock-out', [AttendanceController::class, 'clockOut']);
    Route::get('attendance/employee/{employeeId}', [AttendanceController::class, 'employeeAttendance']);
    Route::get('attendance/stats', [AttendanceController::class, 'stats']);

    // Payroll
    Route::apiResource('payroll', PayrollController::class);
    Route::post('payroll/{payroll}/approve', [PayrollController::class, 'approve']);
    Route::post('payroll/{payroll}/pay', [PayrollController::class, 'pay']);
});
