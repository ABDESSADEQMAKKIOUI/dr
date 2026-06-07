<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    public function list(array $filters = [])
    {
        $query = Customer::with('customerGroup');

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('phone', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['customer_group_id'])) {
            $query->where('customer_group_id', $filters['customer_group_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'customer_group_id' => $data['customer_group_id'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'credit_limit' => $data['credit_limit'] ?? 0,
            'loyalty_points' => $data['loyalty_points'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'customer_group_id' => $data['customer_group_id'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'credit_limit' => $data['credit_limit'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'notes' => $data['notes'] ?? null,
        ]);

        return $customer->fresh();
    }

    public function delete(Customer $customer): bool
    {
        if ($customer->sales()->count() > 0) {
            throw new \Exception('Impossible de supprimer un client ayant des ventes.');
        }

        return $customer->delete();
    }

    public function addLoyaltyPoints(Customer $customer, int $points): void
    {
        $customer->increment('loyalty_points', $points);
    }

    public function getBalance(Customer $customer): float
    {
        return $customer->balance;
    }
}
