<?php

namespace App\Services;

use App\Models\Payment;

class PaymentService
{
    public function getAllPayments(array $filters = [])
    {
        $query = Payment::with('payable', 'paymentMethod', 'user');

        if (isset($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (isset($filters['payment_method_id'])) {
            $query->where('payment_method_id', $filters['payment_method_id']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function createPayment(string $payableType, int $payableId, array $data): Payment
    {
        return Payment::create([
            'payable_type' => $payableType,
            'payable_id' => $payableId,
            'payment_method_id' => $data['payment_method_id'],
            'amount' => $data['amount'],
            'date' => $data['date'] ?? now(),
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);
    }

    public function getPaymentsByEntity(string $entityType, int $entityId)
    {
        return Payment::where('payable_type', $entityType)
            ->where('payable_id', $entityId)
            ->with('paymentMethod', 'user')
            ->latest()
            ->get();
    }

    public function deletePayment(Payment $payment): bool
    {
        return $payment->delete();
    }

    public function getPaymentStats(array $filters = []): array
    {
        $query = Payment::query();

        if (isset($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        return [
            'total_payments' => $query->sum('amount'),
            'count' => $query->count(),
            'by_method' => Payment::selectRaw('payment_method_id, SUM(amount) as total')
                ->groupBy('payment_method_id')
                ->with('paymentMethod')
                ->get(),
        ];
    }
}
