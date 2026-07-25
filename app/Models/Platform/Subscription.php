<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    public const STATUS_TRIALING = 'trialing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    /**
     * The platform database connection.
     */
    protected $connection = 'platform';

    protected $table = 'subscriptions';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'price',
        'currency',
        'billing_period',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'cancelled_at',
        'grace_days',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    /**
     * The tenant this subscription belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * The plan this subscription was issued against.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Whether the paid period has elapsed. A perpetual (lifetime / NULL ends_at)
     * subscription never expires.
     */
    public function isExpired(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }

    /**
     * Whole days from today until the paid period ends. Negative once expired,
     * null when perpetual.
     */
    public function daysUntilExpiry(): ?int
    {
        if ($this->ends_at === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->ends_at->copy()->startOfDay(), false);
    }

    /**
     * Whether the subscription has expired but is still inside its grace window.
     */
    public function isInGrace(): bool
    {
        if ($this->ends_at === null || ! $this->ends_at->isPast()) {
            return false;
        }

        return now()->lessThanOrEqualTo($this->ends_at->copy()->addDays($this->grace_days));
    }

    /**
     * Scope to subscriptions considered live (trialing / active / past_due).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_TRIALING,
            self::STATUS_ACTIVE,
            self::STATUS_PAST_DUE,
        ]);
    }
}
