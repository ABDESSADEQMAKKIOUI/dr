<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * A public inbound enquiry captured on the apex landing page (demo request or
 * contact form) and worked by an operator in the console until it is either
 * converted into a tenant or rejected.
 */
class Lead extends Model
{
    use SoftDeletes;

    public const SOURCE_DEMO = 'demo';
    public const SOURCE_CONTACT = 'contact';

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Every source accepted by the public form.
     */
    public const SOURCES = [
        self::SOURCE_DEMO,
        self::SOURCE_CONTACT,
    ];

    /**
     * The full status vocabulary, in pipeline order.
     */
    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_CONTACTED,
        self::STATUS_QUALIFIED,
        self::STATUS_CONVERTED,
        self::STATUS_REJECTED,
    ];

    /**
     * Badge class per status, using the platform layout's badge vocabulary.
     */
    private const STATUS_BADGES = [
        self::STATUS_NEW => 'badge-primary',
        self::STATUS_CONTACTED => 'badge-info',
        self::STATUS_QUALIFIED => 'badge-warning',
        self::STATUS_CONVERTED => 'badge-success',
        self::STATUS_REJECTED => 'badge-danger',
    ];

    /**
     * The platform database connection. Pinned literally so a lead lookup always
     * lands in safm_platform regardless of the swapped default.
     */
    protected $connection = 'platform';

    protected $table = 'platform_leads';

    protected $fillable = [
        'company_name',
        'contact_name',
        'email',
        'phone',
        'message',
        'source',
        'status',
        'plan_id',
        'tenant_id',
        'handled_by',
        'handled_at',
        'notes',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
        ];
    }

    /**
     * The plan the prospect expressed interest in, if any.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * The tenant this lead was converted into, once converted.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * The operator working the lead (may be null after deletion).
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(PlatformUser::class, 'handled_by');
    }

    /**
     * Scope to untouched leads awaiting a first contact.
     */
    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    /**
     * Scope by a given status value.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by a given source value.
     */
    public function scopeSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope to leads still in play (neither converted nor rejected).
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [
            self::STATUS_NEW,
            self::STATUS_CONTACTED,
            self::STATUS_QUALIFIED,
        ]);
    }

    /**
     * Whether nobody has picked the lead up yet.
     */
    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    /**
     * Whether the lead already became a tenant.
     */
    public function isConverted(): bool
    {
        return $this->status === self::STATUS_CONVERTED;
    }

    /**
     * Whether the lead was closed out without converting.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Whether the lead is still workable.
     */
    public function isOpen(): bool
    {
        return ! $this->isConverted() && ! $this->isRejected();
    }

    /**
     * Mark the lead as contacted, stamping the acting operator.
     */
    public function markContacted(): bool
    {
        return $this->moveTo(self::STATUS_CONTACTED);
    }

    /**
     * Mark the lead as qualified, stamping the acting operator.
     */
    public function markQualified(): bool
    {
        return $this->moveTo(self::STATUS_QUALIFIED);
    }

    /**
     * Mark the lead as rejected, stamping the acting operator.
     */
    public function markRejected(?string $reason = null): bool
    {
        if ($reason !== null && $reason !== '') {
            $this->notes = trim(($this->notes ? $this->notes."\n" : '').$reason);
        }

        return $this->moveTo(self::STATUS_REJECTED);
    }

    /**
     * Bind the lead to the tenant it produced and close it as converted.
     */
    public function markConverted(Tenant $tenant): bool
    {
        $this->tenant_id = $tenant->getKey();

        if ($this->plan_id === null && $tenant->plan_id !== null) {
            $this->plan_id = $tenant->plan_id;
        }

        return $this->moveTo(self::STATUS_CONVERTED);
    }

    /**
     * Translated status label for badges and filters.
     */
    public function statusLabel(): string
    {
        return __('app.lead_status_'.$this->status);
    }

    /**
     * Badge class for the status, matching the console's badge vocabulary.
     */
    public function statusColor(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'badge-secondary';
    }

    /**
     * Translated source label ("Démo" / "Contact").
     */
    public function sourceLabel(): string
    {
        return __('app.lead_source_'.$this->source);
    }

    /**
     * Best available display name for list screens.
     */
    public function displayName(): string
    {
        return $this->company_name ?: $this->contact_name;
    }

    /**
     * Apply a status transition, stamping the acting operator and the moment the
     * lead was handled. Persists immediately.
     */
    protected function moveTo(string $status): bool
    {
        $this->status = $status;
        $this->handled_by = Auth::guard('platform')->id() ?? $this->handled_by;
        $this->handled_at = now();

        return $this->save();
    }
}
