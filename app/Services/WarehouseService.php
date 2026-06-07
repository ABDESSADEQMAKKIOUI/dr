<?php

namespace App\Services;

use App\Models\Warehouse;

class WarehouseService
{
    public function list()
    {
        return Warehouse::withCount('productStocks')->get();
    }

    public function create(array $data): Warehouse
    {
        return Warehouse::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update([
            'name' => $data['name'],
            'code' => $data['code'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return $warehouse->fresh();
    }

    public function delete(Warehouse $warehouse): bool
    {
        if ($warehouse->productStocks()->count() > 0) {
            throw new \Exception('Impossible de supprimer un entrepôt contenant du stock.');
        }

        return $warehouse->delete();
    }

    public function getStockByWarehouse(int $warehouseId)
    {
        return \App\Models\ProductWarehouse::where('warehouse_id', $warehouseId)
            ->with('product')
            ->get();
    }
}
