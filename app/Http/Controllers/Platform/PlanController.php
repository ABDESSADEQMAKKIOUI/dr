<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StorePlanRequest;
use App\Http\Requests\Platform\UpdatePlanRequest;
use App\Models\Platform\Plan;
use App\Services\Platform\PlatformAudit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Subscription plan CRUD.
 *
 * The route group only enforces the coarse 'plans.view' ability, so the finer
 * mutations (create / update / delete) are gated here with the operator's own
 * abilities from App\Support\PlatformAbilities. Authorisation NEVER flows through
 * the framework Gate on the operator console.
 */
class PlanController extends Controller
{
    /**
     * List every plan in display order.
     */
    public function index(): View
    {
        return view('platform.plans.index', [
            'plans' => Plan::query()
                ->withCount(['subscriptions', 'tenants'])
                ->ordered()
                ->get(),
        ]);
    }

    /**
     * Show the new-plan form.
     */
    public function create(): View
    {
        $this->requireAbility('plans.create');

        return view('platform.plans.create');
    }

    /**
     * Persist a new plan.
     */
    public function store(StorePlanRequest $request, PlatformAudit $audit): RedirectResponse
    {
        $this->requireAbility('plans.create');

        $plan = Plan::create($this->attributes($request->validated()));

        $audit->log('plan.created', $plan, null, [
            'slug' => $plan->slug,
        ]);

        return redirect()
            ->route('platform.plans.index')
            ->with('success', __('app.created_success'));
    }

    /**
     * Show the edit form for a plan.
     */
    public function edit(Plan $plan): View
    {
        $this->requireAbility('plans.update');

        return view('platform.plans.edit', [
            'plan' => $plan,
        ]);
    }

    /**
     * Update a plan. Editing a plan's price does NOT retroactively change any
     * running subscription: subscriptions snapshot the price at subscribe time.
     */
    public function update(UpdatePlanRequest $request, Plan $plan, PlatformAudit $audit): RedirectResponse
    {
        $this->requireAbility('plans.update');

        $plan->fill($this->attributes($request->validated()));
        $changed = $plan->getDirty();
        $plan->save();

        $audit->log('plan.updated', $plan, null, [
            'changed' => array_keys($changed),
        ]);

        return redirect()
            ->route('platform.plans.index')
            ->with('success', __('app.updated_success'));
    }

    /**
     * Delete a plan. Refused while any subscription references it — the
     * subscriptions.plan_id foreign key is ON DELETE RESTRICT, so this guards
     * against a raw QueryException and gives the operator a clean message.
     */
    public function destroy(Plan $plan, PlatformAudit $audit): RedirectResponse
    {
        $this->requireAbility('plans.update');

        if ($plan->subscriptions()->exists()) {
            return redirect()
                ->route('platform.plans.index')
                ->with('error', __('app.error_occurred'));
        }

        $slug = $plan->slug;
        $plan->delete();

        $audit->log('plan.deleted', null, null, [
            'slug' => $slug,
        ]);

        return redirect()
            ->route('platform.plans.index')
            ->with('success', __('app.deleted_success'));
    }

    /**
     * Normalise the validated payload into the plan's fillable attributes. The
     * three nullable limits mean "unlimited" when left blank, and the boolean
     * checkboxes are coerced explicitly.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'currency' => $data['currency'],
            'billing_period' => $data['billing_period'],
            'trial_days' => (int) $data['trial_days'],
            'max_users' => $data['max_users'] ?? null,
            'max_warehouses' => $data['max_warehouses'] ?? null,
            'max_products' => $data['max_products'] ?? null,
            'features' => $this->cleanFeatures($data['features'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }

    /**
     * Drop blank feature lines and reindex; return null for an empty list so the
     * json column stays NULL rather than an empty array.
     *
     * @param  array<int, string|null>|null  $features
     * @return array<int, string>|null
     */
    private function cleanFeatures(?array $features): ?array
    {
        if ($features === null) {
            return null;
        }

        $clean = array_values(array_filter(
            array_map(static fn ($feature) => trim((string) $feature), $features),
            static fn (string $feature) => $feature !== ''
        ));

        return $clean !== [] ? $clean : null;
    }

    /**
     * Abort with 403 unless the current operator holds $ability.
     */
    private function requireAbility(string $ability): void
    {
        $operator = Auth::guard('platform')->user();

        if ($operator === null || ! $operator->hasAbility($ability)) {
            abort(403, __('tenancy.forbidden'));
        }
    }
}
