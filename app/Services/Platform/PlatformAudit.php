<?php

namespace App\Services\Platform;

use App\Models\Platform\AuditLog;
use App\Models\Platform\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Writes operator audit entries to safm_platform. Never throws: an audit outage
 * must never abort a provision or any other operator action.
 */
final class PlatformAudit
{
    /**
     * Record an audit entry.
     *
     * @param  string       $action       e.g. 'tenant.provisioned', 'operator.login'
     * @param  Model|null   $subject      the auditable subject (morphs to type + id)
     * @param  Tenant|null  $tenant       the tenant the action concerned, if any
     * @param  array        $properties   before/after payload
     * @param  string|null  $description  human-readable summary (truncated to 255)
     */
    public function log(
        string $action,
        ?Model $subject = null,
        ?Tenant $tenant = null,
        array $properties = [],
        ?string $description = null
    ): AuditLog {
        $operator = Auth::guard('platform')->user();

        $attributes = [
            'platform_user_id' => $operator?->getKey(),
            'actor_email' => $operator?->email,
            'action' => $action,
            'auditable_type' => $subject ? $subject->getMorphClass() : null,
            'auditable_id' => $subject?->getKey(),
            'tenant_id' => $tenant?->getKey() ?? ($subject instanceof Tenant ? $subject->getKey() : null),
            'description' => $description !== null ? Str::limit($description, 255, '') : null,
            'properties' => $properties !== [] ? $properties : null,
            'ip_address' => $this->clientIp(),
            'user_agent' => $this->userAgent(),
        ];

        try {
            return AuditLog::create($attributes);
        } catch (Throwable $e) {
            Log::warning('PlatformAudit failed to write audit entry', [
                'action' => $action,
                'exception' => $e->getMessage(),
            ]);

            // Return an unsaved instance so callers keep a non-null AuditLog and
            // the action they were performing is never interrupted.
            return new AuditLog($attributes);
        }
    }

    private function clientIp(): ?string
    {
        try {
            return request()?->ip();
        } catch (Throwable) {
            return null;
        }
    }

    private function userAgent(): ?string
    {
        try {
            $agent = request()?->userAgent();
        } catch (Throwable) {
            return null;
        }

        return $agent !== null ? Str::limit($agent, 255, '') : null;
    }
}
