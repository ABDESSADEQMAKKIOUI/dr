<?php

namespace App\Http\Controllers;

use App\Models\OfficeShift;
use Illuminate\Http\Request;

class OfficeShiftController extends Controller
{
    public function index()
    {
        $shifts = OfficeShift::withCount('employees')->get();
        return view('shifts.index', compact('shifts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'start_time'         => 'required',
            'end_time'           => 'required',
            'late_after_minutes' => 'nullable|integer|min:0',
        ]);

        OfficeShift::create($data);

        return redirect()->route('shifts.index')->with('success', 'Shift created.');
    }

    public function update(Request $request, OfficeShift $shift)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'start_time'         => 'required',
            'end_time'           => 'required',
            'late_after_minutes' => 'nullable|integer|min:0',
        ]);

        $shift->update($data);

        return redirect()->route('shifts.index')->with('success', 'Shift updated.');
    }

    public function destroy(OfficeShift $shift)
    {
        $shift->delete();
        return redirect()->route('shifts.index')->with('success', 'Shift deleted.');
    }
}
