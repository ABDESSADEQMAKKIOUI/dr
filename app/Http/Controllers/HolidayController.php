<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::orderBy('date')->paginate(30);
        return view('holidays.index', compact('holidays'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'date'         => 'required|date',
            'is_recurring' => 'boolean',
        ]);

        $data['is_recurring'] = $request->boolean('is_recurring');
        Holiday::create($data);

        return redirect()->route('holidays.index')->with('success', 'Holiday added.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'date'         => 'required|date',
            'is_recurring' => 'boolean',
        ]);

        $data['is_recurring'] = $request->boolean('is_recurring');
        $holiday->update($data);

        return redirect()->route('holidays.index')->with('success', 'Holiday updated.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->route('holidays.index')->with('success', 'Holiday deleted.');
    }
}
