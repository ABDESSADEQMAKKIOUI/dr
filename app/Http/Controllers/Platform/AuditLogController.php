<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\AuditLog;
use App\Models\Platform\PlatformUser;
use App\Models\Platform\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Read-only, paginated, filterable view of the platform audit trail
 * (safm_platform.platform_audit_logs).
 */
class AuditLogController extends Controller
{
    /**
     * Paginated audit log with filters on action, tenant, operator, a free-text
     * search and a created-at date range.
     */
    public function index(Request $request): View
    {
        $action = (string) $request->query('action', '');
        $tenantId = (int) $request->query('tenant_id', 0);
        $operatorId = (int) $request->query('operator_id', 0);
        $search = trim((string) $request->query('search', ''));
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));

        $logs = AuditLog::query()
            ->with(['operator', 'tenant'])
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->when($tenantId > 0, fn ($query) => $query->where('tenant_id', $tenantId))
            ->when($operatorId > 0, fn ($query) => $query->where('platform_user_id', $operatorId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('description', 'like', "%{$search}%")
                        ->orWhere('actor_email', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%");
                });
            })
            ->when($this->isValidDate($from), fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($this->isValidDate($to), fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('platform.audit.index', [
            'logs' => $logs,
            'action' => $action,
            'tenantId' => $tenantId,
            'operatorId' => $operatorId,
            'search' => $search,
            'from' => $from,
            'to' => $to,
            'actions' => $this->distinctActions(),
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'operators' => PlatformUser::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * The distinct action names present in the log, for the filter selector.
     *
     * @return array<int, string>
     */
    private function distinctActions(): array
    {
        return AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->all();
    }

    /**
     * Whether $value is a parseable Y-m-d date string.
     */
    private function isValidDate(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        return (bool) strtotime($value);
    }
}
