<?php

namespace App\Support;

/**
 * Hard-coded operator authorization matrix. There is no permissions table in
 * safm_platform: adding an ability is a one-line code edit here.
 *
 * Roles: owner, admin, support, billing.
 */
class PlatformAbilities
{
    public const MAP = [
        'owner' => ['*'],
        'admin' => [
            'tenants.view',
            'tenants.create',
            'tenants.update',
            'tenants.suspend',
            'tenants.delete',
            'tenants.provision',
            'subscriptions.view',
            'subscriptions.update',
            'plans.view',
            'audit.view',
        ],
        'billing' => [
            'tenants.view',
            'subscriptions.view',
            'subscriptions.update',
            'plans.view',
            'plans.create',
            'plans.update',
            'audit.view',
        ],
        'support' => [
            'tenants.view',
            'subscriptions.view',
            'plans.view',
        ],
    ];

    /**
     * Whether the given role is granted the ability. A null or unknown role is
     * granted nothing; the owner wildcard grants everything.
     */
    public static function allows(?string $role, string $ability): bool
    {
        $granted = self::MAP[$role] ?? [];

        return in_array('*', $granted, true) || in_array($ability, $granted, true);
    }
}
