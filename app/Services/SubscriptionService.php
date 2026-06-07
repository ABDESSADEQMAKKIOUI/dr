<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;

class SubscriptionService
{
    public function subscribe(Tenant $tenant, int $planId): Subscription
    {
        $plan = SubscriptionPlan::findOrFail($planId);

        // Annuler l'abonnement actuel si existe
        $currentSubscription = $tenant->subscription;
        if ($currentSubscription) {
            $currentSubscription->update(['status' => 'cancelled']);
        }

        // Créer nouvel abonnement
        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $planId,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
            'price' => $plan->price,
        ]);

        return $subscription;
    }

    public function cancel(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => 'cancelled',
            'ends_at' => now(),
        ]);

        return $subscription;
    }

    public function renew(Subscription $subscription): Subscription
    {
        $subscription->update([
            'current_period_start' => $subscription->current_period_end,
            'current_period_end' => $subscription->current_period_end->addMonth(),
        ]);

        return $subscription;
    }

    public function upgrade(Subscription $subscription, int $newPlanId): Subscription
    {
        $newPlan = SubscriptionPlan::findOrFail($newPlanId);

        $subscription->update([
            'subscription_plan_id' => $newPlanId,
            'price' => $newPlan->price,
        ]);

        return $subscription;
    }

    public function checkLimits(Tenant $tenant, string $feature): bool
    {
        $subscription = $tenant->subscription;
        
        if (!$subscription || $subscription->status !== 'active') {
            return false;
        }

        $plan = $subscription->plan;

        return match($feature) {
            'users' => $tenant->users()->count() < ($plan->limits['max_users'] ?? 999),
            'products' => \App\Models\Product::where('tenant_id', $tenant->id)->count() < ($plan->limits['max_products'] ?? 9999),
            'invoices' => \App\Models\Invoice::where('tenant_id', $tenant->id)->count() < ($plan->limits['max_invoices'] ?? 9999),
            default => true,
        };
    }
}
