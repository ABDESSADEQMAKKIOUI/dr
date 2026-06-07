<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index()
    {
        return response()->json(Journal::withCount('transactions')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:journals',
            'type' => 'required|in:sales,purchases,bank,cash,general',
        ]);

        $journal = Journal::create($validated);
        return response()->json($journal, 201);
    }

    public function show(Journal $journal)
    {
        return response()->json($journal->load('transactions'));
    }

    public function update(Request $request, Journal $journal)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:journals,code,' . $journal->id,
        ]);

        $journal->update($validated);
        return response()->json($journal);
    }

    public function destroy(Journal $journal)
    {
        $journal->delete();
        return response()->json(['message' => 'Journal supprimé']);
    }
}
