<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\ProductWarehouse;
use App\Services\SmsService;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function list(array $filters = [])
    {
        $query = Sale::with('customer', 'warehouse', 'items.product', 'user');

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Sale
    {
        DB::beginTransaction();
        try {
            $sale = Sale::create([
                'reference' => 'SA-' . date('Ymd') . '-' . str_pad(Sale::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => auth()->id(),
                'date' => $data['date'] ?? now(),
                'status' => $data['status'] ?? 'draft',
                'tax_amount' => $data['tax_amount'] ?? 0,
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'discount_value' => $data['discount_value'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'total_amount' => $data['total_amount'],
                'paid_amount' => $data['paid_amount'] ?? 0,
                'payment_status' => $data['payment_status'] ?? 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'tax_type' => $item['tax_type'] ?? 'exclusive',
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'fixed',
                    'discount_value' => $item['discount_value'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // Deduct stock immediately when sale is created as confirmed or delivered
            if (in_array($sale->status, ['confirmed', 'delivered'])) {
                $sale->load('items.product');
                foreach ($sale->items as $saleItem) {
                    $pw = ProductWarehouse::where('product_id', $saleItem->product_id)
                        ->where('warehouse_id', $sale->warehouse_id)
                        ->where('product_variant_id', $saleItem->product_variant_id)
                        ->first();

                    if ($pw) {
                        $pw->decrement('quantity', $saleItem->quantity);
                    }

                    if ($saleItem->product) {
                        $totalStock = ProductWarehouse::where('product_id', $saleItem->product_id)->sum('quantity');
                        $saleItem->product->update(['stock_quantity' => $totalStock]);
                    }
                }
            }

            DB::commit();

            $sale = $sale->fresh('customer', 'warehouse', 'items', 'user');

            // SMS notification
            if ($sale->customer?->phone) {
                app(SmsService::class)->notify('sale_created', $sale->customer->phone, [
                    'customer_name' => $sale->customer->name,
                    'reference'     => $sale->reference,
                    'total'         => number_format($sale->total_amount, 2),
                    'date'          => $sale->date instanceof \Carbon\Carbon ? $sale->date->format('d/m/Y') : date('d/m/Y', strtotime($sale->date)),
                ], $sale);
            }

            return $sale;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function confirm(Sale $sale): Sale
    {
        DB::beginTransaction();
        try {
            // Déduire du stock
            foreach ($sale->items as $item) {
                $productWarehouse = ProductWarehouse::where([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $sale->warehouse_id,
                    'product_variant_id' => $item->product_variant_id,
                ])->first();

                if ($productWarehouse) {
                    $productWarehouse->decrement('quantity', $item->quantity);
                }

                // Mettre à jour stock global
                $product = $item->product;
                $totalStock = ProductWarehouse::where('product_id', $product->id)->sum('quantity');
                $product->update(['stock_quantity' => $totalStock]);
            }

            // Créer facture automatiquement
            $this->createInvoice($sale);

            $sale->update(['status' => 'confirmed']);

            DB::commit();
            return $sale->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function createInvoice(Sale $sale): void
    {
        $invoice = \App\Models\Invoice::create([
            'reference' => 'INV-' . date('Ymd') . '-' . str_pad(\App\Models\Invoice::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'date' => now(),
            'due_date' => now()->addDays(30),
            'status' => $sale->payment_status === 'paid' ? 'paid' : 'unpaid',
            'tax_amount' => $sale->tax_amount,
            'discount_amount' => $sale->discount_amount,
            'total_amount' => $sale->total_amount,
            'paid_amount' => $sale->paid_amount,
        ]);

        foreach ($sale->items as $item) {
            $invoice->items()->create([
                'product_id' => $item->product_id,
                'description' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'subtotal' => $item->subtotal,
            ]);
        }
    }

    public function addPayment(Sale $sale, array $data): void
    {
        $sale->payments()->create([
            'payment_method_id' => $data['payment_method_id'],
            'amount' => $data['amount'],
            'date' => $data['date'] ?? now(),
            'notes' => $data['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);

        $totalPaid = $sale->payments()->sum('amount');
        $sale->update([
            'paid_amount' => $totalPaid,
            'payment_status' => $totalPaid >= $sale->total_amount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid'),
        ]);

        // Mettre à jour facture si existe
        if ($sale->invoice) {
            $sale->invoice->update([
                'paid_amount' => $totalPaid,
                'status' => $totalPaid >= $sale->total_amount ? 'paid' : 'unpaid',
            ]);
        }
    }

    public function delete(Sale $sale): bool
    {
        if ($sale->status === 'confirmed') {
            throw new \Exception('Impossible de supprimer une vente confirmée.');
        }

        return $sale->delete();
    }

    public function createReturn(array $data): \App\Models\SaleReturn
    {
        DB::beginTransaction();
        try {
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            $sale = Sale::findOrFail($data['sale_id']);

            $return = \App\Models\SaleReturn::create([
                'reference' => 'SR-' . date('Ymd') . '-' . str_pad(\App\Models\SaleReturn::count() + 1, 4, '0', STR_PAD_LEFT),
                'sale_id' => $data['sale_id'],
                'warehouse_id' => $sale->warehouse_id,
                'user_id' => auth()->id(),
                'date' => $data['return_date'] ?? now(),
                'status' => 'pending',
                'reason' => $data['reason'],
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $return->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();
            return $return;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
