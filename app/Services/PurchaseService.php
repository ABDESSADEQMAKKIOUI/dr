<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\ProductWarehouse;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function list(array $filters = [])
    {
        $query = Purchase::with('supplier', 'warehouse', 'items.product', 'user');

        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Purchase
    {
        DB::beginTransaction();
        try {
            $purchase = Purchase::create([
                'reference' => 'PO-' . date('Ymd') . '-' . str_pad(Purchase::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => auth()->id(),
                'date' => $data['date'] ?? now(),
                'status' => 'draft',
                'tax_amount' => $data['tax_amount'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'total_amount' => $data['total_amount'],
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'tax_type' => $item['tax_type'] ?? 'exclusive',
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'fixed',
                    'discount_value' => $item['discount_value'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);
            }

            DB::commit();
            return $purchase->fresh('supplier', 'warehouse', 'items');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(Purchase $purchase, array $data): Purchase
    {
        DB::beginTransaction();
        try {
            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'date' => $data['date'],
                'tax_amount' => $data['tax_amount'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'total_amount' => $data['total_amount'],
                'paid_amount' => $data['paid_amount'] ?? $purchase->paid_amount,
                'payment_status' => ($data['paid_amount'] ?? $purchase->paid_amount) >= $data['total_amount'] ? 'paid' : (($data['paid_amount'] ?? $purchase->paid_amount) > 0 ? 'partial' : 'unpaid'),
                'notes' => $data['notes'] ?? null,
            ]);

            // Supprimer les anciens items
            $purchase->items()->delete();

            // Créer les nouveaux items
            foreach ($data['items'] as $item) {
                $purchase->items()->create($item);
            }

            DB::commit();
            return $purchase->fresh('supplier', 'warehouse', 'items');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function confirm(Purchase $purchase): Purchase
    {
        $purchase->update(['status' => 'confirmed']);
        return $purchase;
    }

    public function receive(Purchase $purchase): Purchase
    {
        DB::beginTransaction();
        try {
            // Mettre à jour le stock pour chaque item
            foreach ($purchase->items as $item) {
                $productWarehouse = ProductWarehouse::firstOrCreate([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $purchase->warehouse_id,
                    'product_variant_id' => $item->product_variant_id,
                ], ['quantity' => 0]);

                $productWarehouse->increment('quantity', $item->quantity);

                // Mettre à jour le stock global
                $product = $item->product;
                $totalStock = ProductWarehouse::where('product_id', $product->id)->sum('quantity');
                $product->update(['stock_quantity' => $totalStock]);
            }

            $purchase->update(['status' => 'received']);

            DB::commit();
            return $purchase;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function addPayment(Purchase $purchase, array $data): void
    {
        $purchase->payments()->create([
            'payment_method_id' => $data['payment_method_id'],
            'amount' => $data['amount'],
            'date' => $data['date'] ?? now(),
            'notes' => $data['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);

        // Mettre à jour paid_amount et payment_status
        $totalPaid = $purchase->payments()->sum('amount');
        $purchase->update([
            'paid_amount' => $totalPaid,
            'payment_status' => $totalPaid >= $purchase->total_amount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid'),
        ]);
    }

    public function delete(Purchase $purchase): bool
    {
        if ($purchase->status === 'received') {
            throw new \Exception('Impossible de supprimer un achat déjà reçu.');
        }

        return $purchase->delete();
    }
    public function createReturn(array $data): \App\Models\PurchaseReturn
    {
        DB::beginTransaction();
        try {
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                if ($item['quantity'] > 0) {
                    // Get price from original purchase item if not provided
                    // Ideally we should have it passed, but let's assume it's passed or we fetch it
                    // For now, assuming passed or calculated in controller
                    $price = $item['price'] ?? 0; // Should be handled in controller
                    $totalAmount += $item['quantity'] * $price;
                }
            }

            $purchase = \App\Models\Purchase::findOrFail($data['purchase_id']);

            $return = \App\Models\PurchaseReturn::create([
                'reference'   => 'PR-' . date('Ymd') . '-' . str_pad(\App\Models\PurchaseReturn::count() + 1, 4, '0', STR_PAD_LEFT),
                'purchase_id' => $data['purchase_id'],
                'warehouse_id'=> $purchase->warehouse_id,
                'user_id'     => auth()->id(),
                'date'        => $data['return_date'] ?? now(),
                'status'      => 'pending',
                'reason'      => $data['reason'],
                'total_amount'=> $totalAmount,
                'notes'       => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                if ($item['quantity'] > 0) {
                    $return->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'] ?? 0, // Should be passed
                        'subtotal' => $item['quantity'] * ($item['price'] ?? 0),
                    ]);
                }
            }

            DB::commit();
            return $return;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function applyReturn(\App\Models\PurchaseReturn $return): void
    {
        if ($return->isApplied()) {
            throw new \Exception(__('app.return_already_applied') ?? 'This return has already been applied.');
        }

        $return->load('items.product');

        if ($return->items->isEmpty()) {
            throw new \Exception('No items found for this return.');
        }

        DB::beginTransaction();
        try {
            foreach ($return->items as $item) {
                // Match the same composite key used when stock was received
                $pw = ProductWarehouse::where('product_id', $item->product_id)
                    ->where('warehouse_id', $return->warehouse_id)
                    ->where('product_variant_id', $item->product_variant_id ?? null)
                    ->first();

                if ($pw) {
                    $pw->decrement('quantity', $item->quantity);
                } else {
                    // No stock record exists for this product in this warehouse — create at 0 then decrement
                    $pw = ProductWarehouse::create([
                        'product_id'         => $item->product_id,
                        'warehouse_id'       => $return->warehouse_id,
                        'product_variant_id' => $item->product_variant_id ?? null,
                        'quantity'           => -$item->quantity,
                    ]);
                }

                if ($item->product) {
                    $totalStock = ProductWarehouse::where('product_id', $item->product_id)->sum('quantity');
                    $item->product->update(['stock_quantity' => $totalStock]);
                }
            }

            $return->update(['applied_at' => now()]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
