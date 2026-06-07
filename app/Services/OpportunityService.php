<?php

namespace App\Services;

use App\Models\Opportunity;

class OpportunityService
{
    public function list(array $filters = [])
    {
        $query = Opportunity::with('customer', 'user');

        if (isset($filters['stage'])) {
            $query->where('stage', $filters['stage']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): Opportunity
    {
        return Opportunity::create([
            'customer_id' => $data['customer_id'],
            'title' => $data['title'],
            'amount' => $data['amount'],
            'stage' => $data['stage'] ?? 'prospecting',
            'probability' => $data['probability'] ?? 10,
            'expected_close_date' => $data['expected_close_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'user_id' => $data['user_id'] ?? auth()->id(),
        ]);
    }

    public function updateStage(Opportunity $opportunity, string $stage): Opportunity
    {
        $opportunity->update(['stage' => $stage]);
        return $opportunity;
    }

    public function win(Opportunity $opportunity): Opportunity
    {
        $opportunity->update(['stage' => 'won', 'closed_at' => now()]);
        return $opportunity;
    }

    public function lose(Opportunity $opportunity): Opportunity
    {
        $opportunity->update(['stage' => 'lost', 'closed_at' => now()]);
        return $opportunity;
    }
}
