<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Raised by App\Services\Tenancy\TenantProvisioner when a provisioning step
 * fails. The failing step name is preserved so it can be written verbatim into
 * tenants.provision_error and surfaced in the operator panel.
 *
 * NOTE ON THE API: PHP does not allow a static factory and an instance method
 * to share a name, so the failing step is exposed through the readonly `$step`
 * PROPERTY (properties and methods live in separate symbol tables) and through
 * the failedStep() accessor. `TenantProvisioningException::step()` stays the
 * factory, exactly as the provisioning contract names it.
 */
final class TenantProvisioningException extends RuntimeException
{
    /** The provisioning step that failed, e.g. 'migrate', 'seed-settings', 'verify'. */
    public readonly string $step;

    private function __construct(string $message, string $step, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->step = $step;
    }

    /**
     * Wrap the throwable that aborted a named provisioning step.
     */
    public static function step(string $step, Throwable $previous): self
    {
        return new self(
            sprintf('Provisioning step [%s] failed: %s', $step, $previous->getMessage()),
            $step,
            $previous
        );
    }

    /**
     * A post-provision assertion (step 11) that did not hold. The tenant schema
     * is structurally wrong and the tenant must not be flipped to active.
     */
    public static function verification(string $assertion): self
    {
        return new self(
            sprintf('Provisioning verification failed: %s', $assertion),
            'verify'
        );
    }

    /**
     * A pre-flight validation failure (step 1). Nothing has been written yet.
     */
    public static function validation(string $message): self
    {
        return new self($message, 'validate');
    }

    /**
     * The name of the step that failed.
     */
    public function failedStep(): string
    {
        return $this->step;
    }
}
