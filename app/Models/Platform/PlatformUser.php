<?php

namespace App\Models\Platform;

use App\Support\PlatformAbilities;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class PlatformUser extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The platform database connection. Pinned literally so a platform query
     * issued mid-tenant-request always lands in safm_platform.
     */
    protected $connection = 'platform';

    protected $table = 'platform_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Whether this operator may perform the given ability.
     */
    public function hasAbility(string $ability): bool
    {
        return $this->is_active && PlatformAbilities::allows($this->role, $ability);
    }

    /**
     * Whether this operator holds the owner role.
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Audit log entries authored by this operator.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'platform_user_id');
    }

    /**
     * Scope to active operators only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
