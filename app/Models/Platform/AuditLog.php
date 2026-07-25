<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    /**
     * The platform database connection.
     */
    protected $connection = 'platform';

    protected $table = 'platform_audit_logs';

    /**
     * The table has no updated_at column; only created_at is tracked.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'platform_user_id',
        'actor_email',
        'action',
        'auditable_type',
        'auditable_id',
        'tenant_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The operator who performed the action (may be null after deletion).
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(PlatformUser::class, 'platform_user_id');
    }

    /**
     * The tenant the action concerned, if any.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * The polymorphic subject of the action.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope by action name.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
