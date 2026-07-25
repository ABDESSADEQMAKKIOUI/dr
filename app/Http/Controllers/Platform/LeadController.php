<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Lead;
use App\Services\Platform\PlatformAudit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Operator handling of the inbound enquiries captured on the apex landing page.
 *
 * Every query runs against safm_platform through the platform-pinned
 * App\Models\Platform\Lead model — a lead only ever becomes a tenant database
 * through TenantController, which this screen links to with a prefilled form.
 */
class LeadController extends Controller
{
    /**
     * Paginated lead list with a free-text search plus status and source filters.
     * Filtering is server-side so it applies across every page.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $source = (string) $request->query('source', '');

        $leads = Lead::query()
            ->with(['plan', 'tenant', 'handler'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, Lead::STATUSES, true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(in_array($source, Lead::SOURCES, true), function ($query) use ($source) {
                $query->where('source', $source);
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Counts for the pipeline chips: every status represented even at zero,
        // and computed over the whole table rather than the current page.
        $raw = Lead::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = [];
        foreach (Lead::STATUSES as $value) {
            $counts[$value] = (int) ($raw[$value] ?? 0);
        }

        return view('platform.leads.index', [
            'leads' => $leads,
            'search' => $search,
            'status' => $status,
            'source' => $source,
            'statuses' => Lead::STATUSES,
            'sources' => Lead::SOURCES,
            'counts' => $counts,
        ]);
    }

    /**
     * Lead detail: the submitted enquiry, the handling trail and the forms that
     * move it along the pipeline.
     */
    public function show(Lead $lead): View
    {
        $lead->load(['plan', 'tenant', 'handler']);

        return view('platform.leads.show', [
            'lead' => $lead,
            'statuses' => Lead::STATUSES,
            'prefill' => $this->tenantPrefill($lead),
        ]);
    }

    /**
     * Move the lead along the pipeline and record the operator's notes.
     *
     * handled_by / handled_at are stamped here rather than in the model so a
     * pure note edit still records who last touched the lead.
     */
    public function update(Request $request, Lead $lead, PlatformAudit $audit): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::in(Lead::STATUSES)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $before = $lead->status;

        $lead->fill([
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        $changed = array_keys($lead->getDirty());

        $lead->forceFill([
            'handled_by' => auth()->guard('platform')->id(),
            'handled_at' => now(),
        ])->save();

        $audit->log('lead.updated', $lead, $lead->tenant, [
            'changed' => $changed,
            'status_from' => $before,
            'status_to' => $lead->status,
        ], $lead->displayName().' — '.$before.' → '.$lead->status);

        return redirect()
            ->route('platform.leads.show', $lead)
            ->with('success', __('app.saved_success'));
    }

    /**
     * Soft-delete a lead. The row stays in safm_platform so the audit trail keeps
     * pointing at something real.
     */
    public function destroy(Lead $lead, PlatformAudit $audit): RedirectResponse
    {
        $audit->log('lead.deleted', $lead, $lead->tenant, [
            'email' => $lead->email,
            'status' => $lead->status,
        ], $lead->displayName());

        $lead->delete();

        return redirect()
            ->route('platform.leads.index')
            ->with('success', __('app.deleted_success'));
    }

    /**
     * Query-string payload handed to the existing tenant-create form so the
     * operator does not retype the prospect's details.
     *
     * The keys are exactly the field names of platform/tenants/create.blade.php;
     * provisioning itself is untouched and still validated by StoreTenantRequest.
     *
     * @return array<string, string|int>
     */
    private function tenantPrefill(Lead $lead): array
    {
        $company = $lead->company_name ?: $lead->contact_name;

        // The contact's full name split on the first space: the create form asks
        // for the admin's first and last name separately.
        $parts = preg_split('/\s+/', trim((string) $lead->contact_name), 2) ?: [];
        $first = $parts[0] ?? '';
        $last = $parts[1] ?? '';

        $prefill = [
            'lead_id' => $lead->getKey(),
            'name' => $company,
            'slug' => Str::limit(Str::slug($company), 40, ''),
            'contact_name' => (string) $lead->contact_name,
            'contact_email' => (string) $lead->email,
            'admin_first_name' => $first,
            'admin_last_name' => $last !== '' ? $last : $first,
            'admin_email' => (string) $lead->email,
        ];

        if ($lead->phone) {
            $prefill['contact_phone'] = $lead->phone;
            $prefill['admin_phone'] = $lead->phone;
        }

        if ($lead->plan_id) {
            $prefill['plan_id'] = (int) $lead->plan_id;
        }

        if ($lead->message) {
            $prefill['notes'] = Str::limit(
                __('app.lead').' #'.$lead->getKey().' — '.$lead->message,
                480
            );
        }

        return $prefill;
    }
}
