<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;

/**
 * Operator-driven tenant status transitions.
 *
 * Every write happens on the 'platform' connection, because App\Models\Platform\Tenant
 * pins `protected $connection = 'platform'` — these methods are therefore safe to
 * call while the default connection is pointed at a tenant schema.
 */
final class TenantLifecycle
{
    public function __construct(
        private readonly PlatformAudit $audit,
    ) {
    }

    /**
     * Take a tenant offline. ResolveTenant renders tenancy/suspended for it.
     */
    public function suspend(Tenant $tenant, ?string $reason = null): void
    {
        $previous = $tenant->status;

        $tenant->forceFill([
            'status' => Tenant::STATUS_SUSPENDED,
            'suspended_at' => now(),
        ])->save();

        $this->audit->log('tenant.suspended', $tenant, $tenant, [
            'from' => $previous,
            'reason' => $reason,
        ], $reason);
    }

    /**
     * Bring a suspended or expired tenant back online.
     */
    public function reactivate(Tenant $tenant): void
    {
        $previous = $tenant->status;

        $tenant->forceFill([
            'status' => Tenant::STATUS_ACTIVE,
            'suspended_at' => null,
        ])->save();

        $this->audit->log('tenant.reactivated', $tenant, $tenant, [
            'from' => $previous,
        ]);
    }

    /**
     * Mark a tenant expired. Only ever called by SubscriptionManager::sync()
     * (via platform:sync-subscriptions) or by an explicit operator action.
     */
    public function markExpired(Tenant $tenant): void
    {
        $previous = $tenant->status;

        $tenant->forceFill([
            'status' => Tenant::STATUS_EXPIRED,
        ])->save();

        $this->audit->log('tenant.expired', $tenant, $tenant, [
            'from' => $previous,
            'expires_at' => optional($tenant->expires_at)->toDateTimeString(),
        ]);
    }
}
