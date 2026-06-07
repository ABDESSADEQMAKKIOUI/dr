<?php

namespace App\Services;

use App\Models\Lead;

class LeadService
{
    public function list(array $filters = [])
    {
        $query = Lead::with('user');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Lead
    {
        return Lead::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'source' => $data['source'] ?? null,
            'status' => $data['status'] ?? 'new',
            'notes' => $data['notes'] ?? null,
            'user_id' => $data['user_id'] ?? auth()->id(),
        ]);
    }

    public function convertToCustomer(Lead $lead): \App\Models\Customer
    {
        $customer = \App\Models\Customer::create([
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'created_by' => auth()->id(),
        ]);

        $lead->update(['status' => 'converted']);

        return $customer;
    }

    public function delete(Lead $lead): bool
    {
        return $lead->delete();
    }
}
