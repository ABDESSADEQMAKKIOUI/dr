<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\UpdateSubscriptionRequest;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Services\Platform\PlatformAudit;
use App\Services\Platform\SubscriptionManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Operator management of tenant subscriptions.
 *
 * Every mutation funnels through the single PUT /subscriptions/{subscription}
 * endpoint whose `action` field selects the SubscriptionManager operation. All
 * subscription writes go through SubscriptionManager so its row-lock invariant
 * ("one live subscription per tenant") is never bypassed.
 */
class SubscriptionController extends Controller
{
    /**
     * Paginated subscription list with a status filter and a tenant search.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $subscriptions = Subscription::query()
            ->with(['tenant', 'plan'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('tenant', function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, $this->statuses(), true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('platform.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'search' => $search,
            'status' => $status,
            'statuses' => $this->statuses(),
        ]);
    }

    /**
     * Show the edit form for a subscription, with the plans available for a
     * plan change.
     */
    public function edit(Subscription $subscription): View
    {
        $subscription->load(['tenant', 'plan']);

        return view('platform.subscriptions.edit', [
            'subscription' => $subscription,
            'plans' => Plan::query()->active()->ordered()->get(),
        ]);
    }

    /**
     * Apply one of the subscription actions: change plan, renew, cancel, or edit
     * the non-invariant fields (grace_days / notes).
     */
    public function update(
        UpdateSubscriptionRequest $request,
        Subscription $subscription,
        SubscriptionManager $manager,
        PlatformAudit $audit
    ): RedirectResponse {
        $data = $request->validated();
        $tenant = $subscription->tenant;

        switch ($data['action']) {
            case 'change_plan':
                $plan = Plan::query()->findOrFail((int) $data['plan_id']);
                $manager->changePlan($tenant, $plan);
                break;

            case 'renew':
                $manager->renew($subscription);
                break;

            case 'cancel':
                $manager->cancel($subscription, $data['reason'] ?? null);
                break;

            case 'update':
            default:
                $subscription->fill([
                    'grace_days' => (int) ($data['grace_days'] ?? $subscription->grace_days),
                    'notes' => $data['notes'] ?? $subscription->notes,
                ]);
                $changed = $subscription->getDirty();
                $subscription->save();

                $audit->log('subscription.updated', $subscription, $tenant, [
                    'changed' => array_keys($changed),
                ]);
                break;
        }

        return redirect()
            ->route('platform.subscriptions.edit', $subscription)
            ->with('success', __('app.saved_success'));
    }

    /**
     * The full ordered set of subscription status values, for the list filter.
     *
     * @return array<int, string>
     */
    private function statuses(): array
    {
        return [
            Subscription::STATUS_TRIALING,
            Subscription::STATUS_ACTIVE,
            Subscription::STATUS_PAST_DUE,
            Subscription::STATUS_CANCELLED,
            Subscription::STATUS_EXPIRED,
        ];
    }
}
