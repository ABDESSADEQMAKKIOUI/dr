<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierService
{
    public function list(array $filters = [])
    {
        $query = Supplier::query();

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->withCount('purchases')
            ->withSum('purchases', 'total_amount')
            ->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Supplier
    {
        return Supplier::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'notes' => $data['notes'] ?? null,
        ]);

        return $supplier->fresh();
    }

    public function delete(Supplier $supplier): bool
    {
        if ($supplier->purchases()->count() > 0) {
            throw new \Exception('Impossible de supprimer un fournisseur ayant des achats.');
        }

        return $supplier->delete();
    }

    public function getBalance(Supplier $supplier): float
    {
        return $supplier->balance;
    }
}
