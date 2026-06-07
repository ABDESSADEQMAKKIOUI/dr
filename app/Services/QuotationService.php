<?php

namespace App\Services;

use App\Models\Quotation;

class QuotationService
{
    public function list(array $filters = [])
    {
        $query = Quotation::with('customer', 'warehouse', 'items.product', 'user');

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Quotation
    {
        $quotation = Quotation::create([
            'reference' => 'QT-' . date('Ymd') . '-' . str_pad(Quotation::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
            'customer_id' => $data['customer_id'],
            'warehouse_id' => $data['warehouse_id'],
            'user_id' => auth()->id(),
            'date' => $data['date'] ?? now(),
            'valid_until' => $data['valid_until'] ?? now()->addDays(30),
            'status' => 'pending',
            'tax_amount' => $data['tax_amount'] ?? 0,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'shipping_cost' => $data['shipping_cost'] ?? 0,
            'total_amount' => $data['total_amount'],
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $quotation->items()->create($item);
        }

        return $quotation->fresh('customer', 'warehouse', 'items');
    }

    public function convertToSale(Quotation $quotation): \App\Models\Sale
    {
        $saleData = [
            'customer_id' => $quotation->customer_id,
            'warehouse_id' => $quotation->warehouse_id,
            'date' => now(),
            'status' => 'confirmed',
            'tax_amount' => $quotation->tax_amount,
            'discount_amount' => $quotation->discount_amount,
            'shipping_cost' => $quotation->shipping_cost,
            'total_amount' => $quotation->total_amount,
            'items' => $quotation->items->map(fn($item) => [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'subtotal' => $item->subtotal,
            ])->toArray(),
        ];

        $saleService = new SaleService();
        $sale = $saleService->create($saleData);

        $quotation->update(['status' => 'accepted']);

        return $sale;
    }

    public function delete(Quotation $quotation): bool
    {
        return $quotation->delete();
    }
}
