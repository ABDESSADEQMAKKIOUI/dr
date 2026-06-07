<?php

namespace App\Services;

use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\ProductWarehouse;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Ajustement de stock
     */
    public function adjust(array $data): StockAdjustment
    {
        DB::beginTransaction();
        try {
            $adjustment = StockAdjustment::create([
                'reference' => 'ADJ-' . date('Ymd') . '-' . str_pad(StockAdjustment::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
                'warehouse_id' => $data['warehouse_id'],
                'date' => $data['date'] ?? now(),
                'reason' => $data['reason'],
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $adjustment->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'type' => $item['type'], // add or subtract
                    'quantity' => $item['quantity'],
                ]);

                // Mettre à jour le stock
                $this->updateStock(
                    $item['product_id'],
                    $data['warehouse_id'],
                    $item['product_variant_id'] ?? null,
                    $item['quantity'],
                    $item['type']
                );
            }

            DB::commit();
            return $adjustment->fresh('items');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Transfert inter-entrepôts
     */
    public function transfer(array $data): StockTransfer
    {
        DB::beginTransaction();
        try {
            $transfer = StockTransfer::create([
                'reference' => 'TRF-' . date('Ymd') . '-' . str_pad(StockTransfer::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'date' => $data['date'] ?? now(),
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();
            return $transfer->fresh('items');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Confirmer transfert (retrait du stock source)
     */
    public function sendTransfer(StockTransfer $transfer): StockTransfer
    {
        DB::beginTransaction();
        try {
            foreach ($transfer->items as $item) {
                $this->updateStock(
                    $item->product_id,
                    $transfer->from_warehouse_id,
                    $item->product_variant_id,
                    $item->quantity,
                    'subtract'
                );
            }

            $transfer->update(['status' => 'sent']);

            DB::commit();
            return $transfer;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Recevoir transfert (ajout au stock destination)
     */
    public function receiveTransfer(StockTransfer $transfer): StockTransfer
    {
        DB::beginTransaction();
        try {
            foreach ($transfer->items as $item) {
                $this->updateStock(
                    $item->product_id,
                    $transfer->to_warehouse_id,
                    $item->product_variant_id,
                    $item->quantity,
                    'add'
                );
            }

            $transfer->update(['status' => 'received']);

            DB::commit();
            return $transfer;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mettre à jour le stock d'un produit
     */
    protected function updateStock(int $productId, int $warehouseId, ?int $variantId, float $quantity, string $type): void
    {
        $productWarehouse = ProductWarehouse::firstOrCreate([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'product_variant_id' => $variantId,
        ], [
            'quantity' => 0,
        ]);

        if ($type === 'add') {
            $productWarehouse->increment('quantity', $quantity);
        } else {
            $productWarehouse->decrement('quantity', $quantity);
        }

        // Mettre à jour le stock global du produit
        $product = \App\Models\Product::find($productId);
        $totalStock = ProductWarehouse::where('product_id', $productId)->sum('quantity');
        $product->update(['stock_quantity' => $totalStock]);
    }

    /**
     * Alertes stock
     */
    public function getLowStockAlerts(int $warehouseId = null)
    {
        $query = \App\Models\Product::whereColumn('stock_quantity', '<=', 'stock_alert')
            ->where('track_stock', true)
            ->with('category', 'brand');

        if ($warehouseId) {
            $query->whereHas('warehouses', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        return $query->get();
    }

    /**
     * Historique des mouvements
     */
    public function getMovementHistory(int $productId, int $warehouseId = null)
    {
        // Cette méthode retournerait l'historique complet des achats, ventes, ajustements, transferts
        // Pour simplifier, on retourne les ajustements uniquement
        $query = \App\Models\StockAdjustmentItem::where('product_id', $productId)
            ->with('stockAdjustment.warehouse');

        if ($warehouseId) {
            $query->whereHas('stockAdjustment', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        return $query->latest()->get();
    }
}
