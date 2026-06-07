<?php

namespace App\Services;

use App\Models\Unit;

class UnitService
{
    public function list()
    {
        return Unit::all();
    }

    public function create(array $data): Unit
    {
        return Unit::create([
            'name' => $data['name'],
            'short_name' => $data['short_name'],
            'operator' => $data['operator'] ?? '*',
            'operation_value' => $data['operation_value'] ?? 1.0000,
        ]);
    }

    public function update(Unit $unit, array $data): Unit
    {
        $unit->update([
            'name' => $data['name'],
            'short_name' => $data['short_name'],
            'operator' => $data['operator'] ?? '*',
            'operation_value' => $data['operation_value'] ?? 1.0000,
        ]);

        return $unit->fresh();
    }

    public function delete(Unit $unit): bool
    {
        if ($unit->products()->count() > 0) {
            throw new \Exception('Impossible de supprimer une unité utilisée par des produits.');
        }

        return $unit->delete();
    }
}
