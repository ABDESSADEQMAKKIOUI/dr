<?php

namespace App\Services\Tenancy;

use App\Exceptions\TenantProvisioningException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Owns raw schema DDL for tenant databases.
 *
 * EVERY method runs on the ELEVATED connection
 * (config('tenancy.platform_admin_connection'), i.e. 'platform_admin'), never on
 * 'mysql' and never on 'platform'. Those two hold DML only by design.
 * The 'mysql' entry is the tenant connection whose
 * `database` key is rewritten per request and which, between swaps, points at the
 * deliberately non-existent sentinel schema — it has no usable PDO at the moment
 * CREATE DATABASE / DROP DATABASE has to be issued.
 *
 * Identifiers are validated against a strict allowlist BEFORE they can reach a query.
 * MySQL does not support bound parameters for identifiers, so the allowlist plus
 * backtick quoting is the only defence and it is applied without exception.
 */
final class TenantDatabaseManager
{
    /**
     * The only shape a tenant/platform schema name is ever allowed to take.
     * Lowercase letter first, then lowercase letters, digits and underscores.
     */
    private const IDENTIFIER_PATTERN = '/^[a-z][a-z0-9_]{0,63}$/';

    /**
     * MySQL's own schemas. Never creatable, never droppable.
     */
    private const PROTECTED_DATABASES = [
        'mysql',
        'information_schema',
        'performance_schema',
        'sys',
    ];

    /**
     * Build the schema name for a tenant slug: the configured prefix followed by
     * the slug with hyphens folded to underscores, e.g. 'acme-sarl' -> 'safm_acme_sarl'.
     *
     * @throws TenantProvisioningException when the result is not a legal MySQL identifier
     */
    public function databaseNameFor(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $prefix = (string) config('tenancy.database_prefix', 'safm_');

        $database = $prefix.str_replace('-', '_', $slug);

        if (! $this->isValidIdentifier($database)) {
            throw TenantProvisioningException::validation(
                sprintf('Le slug « %s » produit un nom de base de données invalide (%s).', $slug, $database)
            );
        }

        return $database;
    }

    /**
     * Whether the schema exists, according to information_schema.
     */
    public function exists(string $database): bool
    {
        $database = $this->assertIdentifier($database);

        $row = $this->connection()->selectOne(
            'select schema_name from information_schema.schemata where schema_name = ? limit 1',
            [$database]
        );

        return $row !== null;
    }

    /**
     * CREATE DATABASE `x` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci.
     *
     * The charset/collation MUST match config/database.php connections.mysql or the
     * cross-table foreign keys in the ERP migrations fail with errno 3780.
     *
     * @return bool true when this call created the schema, false when it already existed
     */
    public function create(string $database): bool
    {
        $database = $this->assertIdentifier($database);

        if (in_array($database, self::PROTECTED_DATABASES, true)) {
            throw TenantProvisioningException::validation(
                sprintf('Refus de créer le schéma système « %s ».', $database)
            );
        }

        if ($this->exists($database)) {
            return false;
        }

        $charset = (string) config('tenancy.database_charset', 'utf8mb4');
        $collation = (string) config('tenancy.database_collation', 'utf8mb4_unicode_ci');

        $this->connection()->statement(sprintf(
            'CREATE DATABASE IF NOT EXISTS %s CHARACTER SET %s COLLATE %s',
            $this->quote($database),
            $this->assertCharsetToken($charset),
            $this->assertCharsetToken($collation)
        ));

        return true;
    }

    /**
     * DROP DATABASE IF EXISTS `x`.
     *
     * Refuses the platform schema, the sentinel schema and every MySQL system schema.
     */
    public function drop(string $database): void
    {
        $database = $this->assertIdentifier($database);

        foreach ($this->undroppable() as $protected) {
            if ($protected !== null && $protected !== '' && $database === $protected) {
                throw TenantProvisioningException::validation(
                    sprintf('Refus de supprimer le schéma protégé « %s ».', $database)
                );
            }
        }

        $this->connection()->statement('DROP DATABASE IF EXISTS '.$this->quote($database));
    }

    /**
     * Schema names that may never be dropped by this class.
     *
     * @return array<int, string|null>
     */
    private function undroppable(): array
    {
        $platformConnection = (string) config('tenancy.platform_connection', 'platform');

        return array_merge(self::PROTECTED_DATABASES, [
            config("database.connections.{$platformConnection}.database"),
            config('tenancy.sentinel_database'),
        ]);
    }

    /**
     * The ELEVATED connection. DDL is the one thing the ordinary control-plane and
     * ERP users deliberately cannot do: both hold DML only, so an SQL injection in
     * either cannot create or destroy a schema. Root lives here and nowhere else.
     *
     * Previously the platform connection — the only one guaranteed to have a live PDO while a
     * tenant schema is being created, migrated or dropped.
     */
    private function connection(): \Illuminate\Database\Connection
    {
        return DB::connection((string) config('tenancy.platform_admin_connection', 'platform_admin'));
    }

    private function isValidIdentifier(string $database): bool
    {
        return $database !== ''
            && strlen($database) <= 64
            && preg_match(self::IDENTIFIER_PATTERN, $database) === 1;
    }

    /**
     * @throws TenantProvisioningException
     */
    private function assertIdentifier(string $database): string
    {
        $database = strtolower(trim($database));

        if (! $this->isValidIdentifier($database)) {
            throw TenantProvisioningException::validation(
                sprintf('Nom de base de données invalide : « %s ».', $database)
            );
        }

        return $database;
    }

    /**
     * Charset and collation come from config, not from user input, but they are
     * still interpolated into DDL — validate them the same way.
     */
    private function assertCharsetToken(string $token): string
    {
        if (preg_match('/^[a-z0-9_]{1,64}$/', $token) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid charset/collation token [%s].', $token));
        }

        return $token;
    }

    /**
     * Backtick-quote an identifier that has already passed the allowlist.
     */
    private function quote(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }
}
