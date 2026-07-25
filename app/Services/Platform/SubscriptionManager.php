<?php

namespace App\Services\Platform;

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The single writer of subscription rows and of the tenant status transitions
 * that are driven by time.
 *
 * MySQL has no partial unique index, so "one live subscription per tenant" is an
 * application invariant only. Every write therefore happens inside a platform
 * transaction that first takes a row lock on the tenants row (SELECT … FOR UPDATE),
 * which serialises two concurrent operator actions on the same tenant.
 */
final class SubscriptionManager
{
    /**
     * Days before expiry at which a warning is emitted by the daily sync.
     */
    public const WARNING_WINDOWS = [30, 14, 7, 3, 1];

    public function __construct(
        private readonly PlatformAudit $audit,
        private readonly TenantLifecycle $lifecycle,
    ) {
    }

    /**
     * Compute the end of a billing period. Returns null for 'lifetime'.
     */
    public static function periodEnd(string $billingPeriod, CarbonInterface $from): ?Carbon
    {
        $start = Carbon::instance($from);

        return match ($billingPeriod) {
            'monthly' => $start->copy()->addMonth(),
            'quarterly' => $start->copy()->addMonths(3),
            'yearly' => $start->copy()->addYear(),
            'lifetime' => null,
            default => $start->copy()->addMonth(),
        };
    }

    /**
     * Build the attribute set for a brand-new subscription against $plan.
     * Shared with the provisioner so a CLI provision and an operator-panel
     * subscription are byte-identical.
     *
     * @return array<string, mixed>
     */
    public static function attributesFor(Plan $plan, ?int $trialDays = null, ?CarbonInterface $startsAt = null): array
    {
        $start = $startsAt !== null ? Carbon::instance($startsAt) : Carbon::now();
        $trialDays = $trialDays ?? (int) ($plan->trial_days ?? 0);
        $trialDays = max(0, $trialDays);

        $endsAt = self::periodEnd((string) $plan->billing_period, $start);

        return [
            'plan_id' => $plan->id,
            'status' => $trialDays > 0 ? Subscription::STATUS_TRIALING : Subscription::STATUS_ACTIVE,
            'price' => $plan->price,
            'currency' => $plan->currency,
            'billing_period' => $plan->billing_period,
            'starts_at' => $start,
            'trial_ends_at' => $trialDays > 0 ? $start->copy()->addDays($trialDays) : null,
            'ends_at' => $endsAt,
            'cancelled_at' => null,
        ];
    }

    /**
     * The subscription currently keeping the tenant alive, most recent first.
     */
    public function current(Tenant $tenant): ?Subscription
    {
        return $tenant->activeSubscription();
    }

    /**
     * Open a subscription for a tenant, cancelling any that is still live.
     */
    public function start(Tenant $tenant, Plan $plan, ?int $trialDays = null, ?CarbonInterface $startsAt = null): Subscription
    {
        $subscription = $this->transaction($tenant, function () use ($tenant, $plan, $trialDays, $startsAt) {
            $this->cancelLive($tenant, 'remplacée par un nouvel abonnement');

            return Subscription::create(array_merge(
                ['tenant_id' => $tenant->id],
                self::attributesFor($plan, $trialDays, $startsAt)
            ));
        });

        $this->applyToTenant($tenant, $subscription);

        $this->audit->log('subscription.started', $subscription, $tenant, [
            'plan' => $plan->slug,
            'status' => $subscription->status,
            'ends_at' => optional($subscription->ends_at)->toDateTimeString(),
        ]);

        return $subscription;
    }

    /**
     * Move the tenant onto a different plan. The running subscription is
     * cancelled and a fresh one is opened from now.
     */
    public function changePlan(Tenant $tenant, Plan $plan, ?int $trialDays = null): Subscription
    {
        $previous = $this->current($tenant);

        $subscription = $this->start($tenant, $plan, $trialDays ?? 0);

        $this->audit->log('subscription.plan_changed', $subscription, $tenant, [
            'from' => optional($previous?->plan)->slug,
            'to' => $plan->slug,
        ]);

        return $subscription;
    }

    /**
     * Extend an existing subscription by one more billing period.
     */
    public function renew(Subscription $subscription): Subscription
    {
        $from = $subscription->ends_at !== null && $subscription->ends_at->isFuture()
            ? $subscription->ends_at
            : Carbon::now();

        $subscription->forceFill([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => self::periodEnd((string) $subscription->billing_period, $from),
            'trial_ends_at' => null,
            'cancelled_at' => null,
        ])->save();

        $tenant = $subscription->tenant;

        if ($tenant !== null) {
            $this->applyToTenant($tenant, $subscription);

            if ($tenant->status === Tenant::STATUS_EXPIRED) {
                $this->lifecycle->reactivate($tenant);
            }
        }

        $this->audit->log('subscription.renewed', $subscription, $tenant, [
            'ends_at' => optional($subscription->ends_at)->toDateTimeString(),
        ]);

        return $subscription;
    }

    /**
     * Cancel a subscription. The tenant keeps working until ends_at.
     */
    public function cancel(Subscription $subscription, ?string $reason = null): Subscription
    {
        $subscription->forceFill([
            'status' => Subscription::STATUS_CANCELLED,
            'cancelled_at' => Carbon::now(),
            'notes' => $reason !== null && $reason !== ''
                ? trim(((string) $subscription->notes)."\n".$reason)
                : $subscription->notes,
        ])->save();

        $this->audit->log('subscription.cancelled', $subscription, $subscription->tenant, [
            'reason' => $reason,
        ]);

        return $subscription;
    }

    /**
     * Mark a subscription expired.
     */
    public function expire(Subscription $subscription): Subscription
    {
        $subscription->forceFill(['status' => Subscription::STATUS_EXPIRED])->save();

        $this->audit->log('subscription.expired', $subscription, $subscription->tenant, [
            'ends_at' => optional($subscription->ends_at)->toDateTimeString(),
        ]);

        return $subscription;
    }

    /**
     * Reconcile one tenant's subscriptions against the clock.
     *
     * - a trialing subscription past trial_ends_at becomes 'active'
     *   (or 'expired' when it has no paid period at all);
     * - a live subscription past ends_at + grace_days becomes 'expired' and the
     *   tenant is marked expired;
     * - tenants.expires_at is refreshed from the current subscription.
     *
     * @return array<int, string> human-readable list of the changes applied
     */
    public function sync(Tenant $tenant, bool $dryRun = false): array
    {
        $changes = [];
        $now = Carbon::now();

        foreach ($tenant->subscriptions()->whereIn('status', [
            Subscription::STATUS_TRIALING,
            Subscription::STATUS_ACTIVE,
            Subscription::STATUS_PAST_DUE,
        ])->orderBy('id')->get() as $subscription) {
            // Trial elapsed.
            if ($subscription->status === Subscription::STATUS_TRIALING
                && $subscription->trial_ends_at !== null
                && $subscription->trial_ends_at->isPast()) {
                if ($subscription->ends_at === null || $subscription->ends_at->isFuture()) {
                    $changes[] = sprintf('subscription #%d: trialing -> active', $subscription->id);

                    if (! $dryRun) {
                        $subscription->forceFill(['status' => Subscription::STATUS_ACTIVE])->save();
                        $this->audit->log('subscription.trial_ended', $subscription, $tenant);
                    }
                } else {
                    $changes[] = sprintf('subscription #%d: trialing -> expired', $subscription->id);

                    if (! $dryRun) {
                        $this->expire($subscription);
                    }
                }
            }

            // Paid period elapsed, grace window included.
            if ($subscription->ends_at !== null
                && $now->greaterThan($subscription->ends_at->copy()->addDays((int) $subscription->grace_days))) {
                $changes[] = sprintf('subscription #%d: %s -> expired', $subscription->id, $subscription->status);

                if (! $dryRun) {
                    $this->expire($subscription);
                }

                if ($tenant->status !== Tenant::STATUS_EXPIRED) {
                    $changes[] = sprintf('tenant %s: %s -> expired', $tenant->slug, $tenant->status);

                    if (! $dryRun) {
                        $this->lifecycle->markExpired($tenant);
                    }
                }

                continue;
            }

            // Still live: emit the warning-window notice.
            $remaining = $subscription->daysUntilExpiry();

            if ($remaining !== null && in_array($remaining, self::WARNING_WINDOWS, true)) {
                $changes[] = sprintf('subscription #%d: expires in %d day(s)', $subscription->id, $remaining);

                if (! $dryRun) {
                    $this->audit->log('subscription.expiring', $subscription, $tenant, [
                        'days_remaining' => $remaining,
                        'ends_at' => $subscription->ends_at?->toDateTimeString(),
                    ]);
                }
            }
        }

        // Refresh the denormalised expiry on the tenant row.
        $current = $tenant->fresh()?->activeSubscription();

        if (! $dryRun && $current !== null) {
            $this->applyToTenant($tenant, $current);
        }

        return $changes;
    }

    /**
     * Mirror the subscription's plan and end date onto the tenant row.
     */
    public function applyToTenant(Tenant $tenant, Subscription $subscription): void
    {
        $tenant->forceFill([
            'plan_id' => $subscription->plan_id,
            'expires_at' => $subscription->ends_at,
        ])->save();
    }

    /**
     * Cancel every still-live subscription of the tenant.
     */
    private function cancelLive(Tenant $tenant, string $reason): void
    {
        foreach ($tenant->subscriptions()->active()->get() as $live) {
            $this->cancel($live, $reason);
        }
    }

    /**
     * Run $callback inside a platform transaction holding a row lock on the
     * tenants row, so two concurrent operator actions cannot both open a
     * subscription for the same tenant.
     */
    private function transaction(Tenant $tenant, callable $callback): mixed
    {
        $connection = (string) config('tenancy.platform_connection', 'platform');

        return DB::connection($connection)->transaction(function () use ($connection, $tenant, $callback) {
            DB::connection($connection)
                ->table('tenants')
                ->where('id', $tenant->id)
                ->lockForUpdate()
                ->first();

            return $callback();
        });
    }
}
