<?php

namespace App\Http\Controllers;

use App\Models\SaleDraft;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleDraftController extends Controller
{
    /**
     * List all drafts for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $drafts = SaleDraft::where('user_id', auth()->id())
            ->with(['customer:id,name', 'warehouse:id,name'])
            ->latest()
            ->get()
            ->map(fn($d) => [
                'id'           => $d->id,
                'customer_id'  => $d->customer_id,
                'customer_name'=> $d->customer?->name ?? 'Walk-in',
                'warehouse_id' => $d->warehouse_id,
                'items'        => $d->items,
                'items_count'  => count($d->items),
                'discount'     => $d->discount,
                'total'        => number_format($d->total, 2),
                'created_at'   => $d->created_at->format('H:i d/m'),
            ]);

        return response()->json([
            'drafts' => $drafts,
            'count'  => $drafts->count(),
        ]);
    }

    /**
     * Save current POS cart as a draft.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'        => 'required|array|min:1',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'customer_id'  => 'nullable|exists:customers,id',
            'discount'     => 'nullable|numeric|min:0',
        ]);

        $draft = SaleDraft::create([
            'user_id'      => auth()->id(),
            'warehouse_id' => $validated['warehouse_id'] ?? null,
            'customer_id'  => $validated['customer_id'] ?? null,
            'items'        => $validated['items'],
            'discount'     => $validated['discount'] ?? 0,
        ]);

        $count = SaleDraft::where('user_id', auth()->id())->count();

        return response()->json([
            'success' => true,
            'id'      => $draft->id,
            'count'   => $count,
        ]);
    }

    /**
     * Delete a draft (discard or after resume).
     */
    public function destroy(SaleDraft $draft): JsonResponse
    {
        if ($draft->user_id !== auth()->id()) {
            abort(403);
        }

        $draft->delete();

        $count = SaleDraft::where('user_id', auth()->id())->count();

        return response()->json(['success' => true, 'count' => $count]);
    }
}
