<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use Illuminate\Http\Request;

class ErrorLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ErrorLog::with('user')
            ->when($request->search, fn($q) => $q->where('message', 'like', '%' . $request->search . '%'))
            ->when($request->from, fn($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest()
            ->paginate(30);

        return view('system.error-logs', compact('logs'));
    }

    public function destroy(ErrorLog $errorLog)
    {
        $errorLog->delete();
        return back()->with('success', 'Log entry deleted.');
    }

    public function clear()
    {
        ErrorLog::truncate();
        return back()->with('success', 'All error logs cleared.');
    }
}
