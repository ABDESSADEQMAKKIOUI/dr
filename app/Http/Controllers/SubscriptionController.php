<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptionService) {}

    public function plans()
    {
        return response()->json(SubscriptionPlan::where('is_active', true)->get());
    }

    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $tenant = \App\Models\Tenant::findOrFail($validated['tenant_id']);
        $subscription = $this->subscriptionService->subscribe($tenant, $validated['plan_id']);

        return response()->json($subscription, 201);
    }

    public function cancel(Subscription $subscription)
    {
        $subscription = $this->subscriptionService->cancel($subscription);
        return response()->json($subscription);
    }

    public function upgrade(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $subscription = $this->subscriptionService->upgrade($subscription, $validated['plan_id']);
        return response()->json($subscription);
    }
}
