<?php

namespace App\Services;

use App\Models\Expense;

class ExpenseService
{
    public function list(array $filters = [])
    {
        $query = Expense::with('category', 'user');

        if (isset($filters['category_id'])) {
            $query->where('expense_category_id', $filters['category_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        return $query->latest('date')->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Expense
    {
        return Expense::create([
            'expense_category_id' => $data['expense_category_id'],
            'reference' => 'EXP-' . date('Ymd') . '-' . str_pad(Expense::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
            'date' => $data['date'] ?? now(),
            'amount' => $data['amount'],
            'payment_method_id' => $data['payment_method_id'] ?? null,
            'description' => $data['description'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recurring' => $data['recurring'] ?? false,
            'recurring_type' => $data['recurring_type'] ?? null,
            'user_id' => auth()->id(),
        ]);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update([
            'expense_category_id' => $data['expense_category_id'],
            'date' => $data['date'],
            'amount' => $data['amount'],
            'payment_method_id' => $data['payment_method_id'] ?? null,
            'description' => $data['description'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $expense->fresh();
    }

    public function delete(Expense $expense): bool
    {
        return $expense->delete();
    }

    public function getStats(array $filters = []): array
    {
        $query = Expense::query();

        if (isset($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        return [
            'total' => $query->sum('amount'),
            'count' => $query->count(),
            'by_category' => Expense::selectRaw('expense_category_id, SUM(amount) as total')
                ->groupBy('expense_category_id')
                ->with('category')
                ->get(),
        ];
    }
}
