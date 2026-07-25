<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\AuditLog;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Illuminate\View\View;

/**
 * Operator console home. Everything it renders is read from safm_platform via
 * the platform-pinned models — it never touches a tenant database.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        // Tenant counts by status, every status represented even at zero.
        $raw = Tenant::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statuses = [
            Tenant::STATUS_PROVISIONING,
            Tenant::STATUS_ACTIVE,
            Tenant::STATUS_SUSPENDED,
            Tenant::STATUS_EXPIRED,
            Tenant::STATUS_FAILED,
            Tenant::STATUS_ARCHIVED,
        ];

        $tenantsByStatus = [];
        foreach ($statuses as $status) {
            $tenantsByStatus[$status] = (int) ($raw[$status] ?? 0);
        }

        $tenantsTotal = array_sum($tenantsByStatus);

        // Live subscriptions (trialing / active / past_due).
        $activeSubscriptions = Subscription::query()->active()->count();

        // Monthly recurring revenue: each live subscription's snapshotted price
        // normalised to a monthly figure. Lifetime plans contribute nothing
        // recurring.
        $mrr = Subscription::query()
            ->active()
            ->get(['price', 'billing_period'])
            ->reduce(function (float $carry, Subscription $subscription): float {
                $price = (float) $subscription->price;

                $monthly = match ($subscription->billing_period) {
                    'monthly' => $price,
                    'quarterly' => $price / 3,
                    'yearly' => $price / 12,
                    default => 0.0, // lifetime or unknown: no recurring revenue
                };

                return $carry + $monthly;
            }, 0.0);

        // Subscriptions whose paid period ends within the next 30 days.
        $expiringSoon = Subscription::query()
            ->active()
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->copy()->addDays(30)])
            ->with(['tenant', 'plan'])
            ->orderBy('ends_at')
            ->limit(15)
            ->get();

        $recentAudits = AuditLog::query()
            ->with(['operator', 'tenant'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $plansCount = Plan::query()->where('is_active', true)->count();

        return view('platform.dashboard.index', [
            'tenantsByStatus' => $tenantsByStatus,
            'tenantsTotal' => $tenantsTotal,
            'activeSubscriptions' => $activeSubscriptions,
            'mrr' => round($mrr, 2),
            'expiringSoon' => $expiringSoon,
            'recentAudits' => $recentAudits,
            'plansCount' => $plansCount,
        ]);
    }
}
