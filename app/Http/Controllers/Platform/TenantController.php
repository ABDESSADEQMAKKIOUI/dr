<?php

namespace App\Http\Controllers\Platform;

use App\Exceptions\TenantProvisioningException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreTenantRequest;
use App\Http\Requests\Platform\UpdateTenantRequest;
use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Services\Platform\PlatformAudit;
use App\Services\Platform\TenantLifecycle;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Operator management of tenants. Every query runs against safm_platform through
 * the platform-pinned App\Models\Platform\Tenant model, so nothing here touches a
 * tenant database directly — provisioning is delegated to TenantProvisioner and
 * status transitions to TenantLifecycle.
 */
class TenantController extends Controller
{
    /**
     * Paginated tenant list with a free-text search and a status filter.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $tenants = Tenant::query()
            ->with('plan')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('contact_email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, $this->statuses(), true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('platform.tenants.index', [
            'tenants' => $tenants,
            'search' => $search,
            'status' => $status,
            'statuses' => $this->statuses(),
        ]);
    }

    /**
     * Show the new-tenant / provisioning form.
     */
    public function create(): View
    {
        return view('platform.tenants.create', [
            'plans' => Plan::query()->active()->ordered()->get(),
        ]);
    }

    /**
     * Provision a brand-new tenant.
     *
     * Provisioning is slow (CREATE DATABASE + 102 migrations + seeders) and the
     * queue connection is 'sync' with no jobs table, so there is no worker to
     * hand it off to. It therefore runs INLINE with the execution time limit
     * lifted; the create view warns the operator the request may take a minute.
     */
    public function store(StoreTenantRequest $request, TenantProvisioner $provisioner): RedirectResponse
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        $data = $request->validated();

        // Map the validated payload straight onto the provisioner's $data
        // contract. TenantProvisioner audits 'tenant.provisioned' itself.
        $payload = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'contact_name' => $data['contact_name'] ?? null,
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'] ?? null,
            'locale' => $data['locale'],
            'currency' => $data['currency'],
            'timezone' => $data['timezone'],
            'notes' => $data['notes'] ?? null,
            'plan_id' => (int) $data['plan_id'],
            'trial_days' => isset($data['trial_days']) ? (int) $data['trial_days'] : null,
            'admin_first_name' => $data['admin_first_name'],
            'admin_last_name' => $data['admin_last_name'],
            'admin_email' => $data['admin_email'],
            'admin_password' => $data['admin_password'],
            'admin_phone' => $data['admin_phone'] ?? null,
            'warehouse_name' => $data['warehouse_name'] ?? null,
            'warehouse_code' => $data['warehouse_code'] ?? null,
        ];

        try {
            $tenant = $provisioner->provision($payload);
        } catch (TenantProvisioningException $e) {
            return back()
                ->withInput()
                ->with('error', __('app.error_occurred').' — '.$e->getMessage());
        }

        return redirect()
            ->route('platform.tenants.show', $tenant)
            ->with('success', __('app.created_success'));
    }

    /**
     * Tenant detail: descriptive fields, full subscription history, recent audit
     * entries and the plans available for a quick plan change.
     */
    public function show(Tenant $tenant): View
    {
        $tenant->load('plan');

        $subscriptions = $tenant->subscriptions()
            ->with('plan')
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->get();

        $currentSubscription = $tenant->activeSubscription();

        $auditLogs = $tenant->auditLogs()
            ->with('operator')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('platform.tenants.show', [
            'tenant' => $tenant,
            'subscriptions' => $subscriptions,
            'currentSubscription' => $currentSubscription,
            'auditLogs' => $auditLogs,
            'plans' => Plan::query()->active()->ordered()->get(),
        ]);
    }

    /**
     * Show the edit form for a tenant's descriptive fields.
     */
    public function edit(Tenant $tenant): View
    {
        return view('platform.tenants.edit', [
            'tenant' => $tenant,
        ]);
    }

    /**
     * Update a tenant's descriptive fields. The slug and database_name are
     * immutable and are not accepted by UpdateTenantRequest; plan changes flow
     * through the subscription screens.
     */
    public function update(UpdateTenantRequest $request, Tenant $tenant, PlatformAudit $audit): RedirectResponse
    {
        $data = $request->validated();

        $tenant->fill([
            'name' => $data['name'],
            'contact_name' => $data['contact_name'] ?? null,
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'] ?? null,
            'locale' => $data['locale'],
            'currency' => $data['currency'],
            'timezone' => $data['timezone'],
            'notes' => $data['notes'] ?? null,
        ]);

        $changed = $tenant->getDirty();
        $tenant->save();

        $audit->log('tenant.updated', $tenant, $tenant, [
            'changed' => array_keys($changed),
        ]);

        return redirect()
            ->route('platform.tenants.show', $tenant)
            ->with('success', __('app.updated_success'));
    }

    /**
     * Take a tenant offline. ResolveTenant then renders tenancy/suspended.
     */
    public function suspend(Request $request, Tenant $tenant, TenantLifecycle $lifecycle): RedirectResponse
    {
        $reason = trim((string) $request->input('reason', '')) ?: null;

        $lifecycle->suspend($tenant, $reason);

        return redirect()
            ->route('platform.tenants.show', $tenant)
            ->with('success', __('app.saved_success'));
    }

    /**
     * Bring a suspended or expired tenant back online.
     */
    public function reactivate(Tenant $tenant, TenantLifecycle $lifecycle): RedirectResponse
    {
        $lifecycle->reactivate($tenant);

        return redirect()
            ->route('platform.tenants.show', $tenant)
            ->with('success', __('app.saved_success'));
    }

    /**
     * Retry a failed provision (or re-apply migrations + system seeders to an
     * existing tenant). Idempotent; also runs inline with the time limit lifted.
     */
    public function reprovision(Tenant $tenant, TenantProvisioner $provisioner, PlatformAudit $audit): RedirectResponse
    {
        @set_time_limit(0);
        @ini_set('max_execution_time', '0');

        try {
            $provisioner->reprovision($tenant);
        } catch (TenantProvisioningException $e) {
            return back()->with('error', __('app.error_occurred').' — '.$e->getMessage());
        }

        // A tenant that previously failed becomes usable again once reprovision
        // completes without throwing.
        if ($tenant->status === Tenant::STATUS_FAILED) {
            $tenant->forceFill([
                'status' => Tenant::STATUS_ACTIVE,
                'provisioned_at' => $tenant->provisioned_at ?? now(),
                'provision_error' => null,
            ])->save();
        }

        $audit->log('tenant.reprovisioned', $tenant, $tenant);

        return redirect()
            ->route('platform.tenants.show', $tenant)
            ->with('success', __('app.saved_success'));
    }

    /**
     * Destroy a tenant. Without drop_database the schema is left on disk and only
     * the tenants row is soft-deleted; TenantProvisioner always audits the action.
     */
    public function destroy(Request $request, Tenant $tenant, TenantProvisioner $provisioner): RedirectResponse
    {
        $dropDatabase = $request->boolean('drop_database');

        $provisioner->destroy($tenant, $dropDatabase);

        return redirect()
            ->route('platform.tenants.index')
            ->with('success', __('app.deleted_success'));
    }

    /**
     * The full ordered set of tenant status values, for the list filter.
     *
     * @return array<int, string>
     */
    private function statuses(): array
    {
        return [
            Tenant::STATUS_PROVISIONING,
            Tenant::STATUS_ACTIVE,
            Tenant::STATUS_SUSPENDED,
            Tenant::STATUS_EXPIRED,
            Tenant::STATUS_FAILED,
            Tenant::STATUS_ARCHIVED,
        ];
    }
}
