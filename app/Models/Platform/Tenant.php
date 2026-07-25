<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    public const STATUS_PROVISIONING = 'provisioning';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_FAILED = 'failed';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * The platform database connection. Pinned literally so a tenant lookup
     * always lands in safm_platform regardless of the swapped default.
     */
    protected $connection = 'platform';

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'database_name',
        'status',
        'plan_id',
        'contact_name',
        'contact_email',
        'contact_phone',
        'locale',
        'currency',
        'timezone',
        'provisioned_at',
        'suspended_at',
        'expires_at',
        'last_seen_at',
        'notes',
        'provision_error',
    ];

    protected function casts(): array
    {
        return [
            'provisioned_at' => 'datetime',
            'suspended_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * The denormalised current plan for list screens.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Every subscription this tenant has ever held.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * The most recent subscription, regardless of status.
     */
    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    /**
     * Audit log entries scoped to this tenant.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Fully-qualified host, e.g. acme.facturation.cfpss.ma.
     */
    public function getHostAttribute(): string
    {
        return $this->slug.'.'.config('tenancy.root_domain');
    }

    /**
     * Absolute HTTPS URL for the tenant.
     */
    public function getUrlAttribute(): string
    {
        return 'https://'.$this->host;
    }

    /**
     * Whether the tenant is live and serving requests.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Whether the tenant is suspended by an operator.
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * The subscription currently keeping the tenant alive (trialing / active /
     * past_due), most recent first, or null if none applies.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', [
                Subscription::STATUS_TRIALING,
                Subscription::STATUS_ACTIVE,
                Subscription::STATUS_PAST_DUE,
            ])
            ->orderByDesc('starts_at')
            ->first();
    }

    /**
     * Scope to active tenants only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope by a given status value.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
