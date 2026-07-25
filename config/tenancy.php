<?php

return [
    // Master switch. false = legacy single-tenant behaviour (install wizard on,
    // no host validation). true = SaaS mode. Set TENANCY_ENABLED=true in prod.
    'enabled' => (bool) env('TENANCY_ENABLED', true),

    'root_domain'  => env('TENANCY_ROOT_DOMAIN',  'facturation.cfpss.ma'),
    'admin_domain' => env('TENANCY_ADMIN_DOMAIN', 'admin.facturation.cfpss.ma'),

    // Connection names. 'tenant_connection' is the EXISTING 'mysql' entry whose
    // `database` key is rewritten per request. Never rename it.
    'tenant_connection'  => 'mysql',
    'platform_connection' => 'platform',

    // The value DB_DATABASE points at. MUST be a schema that DOES NOT EXIST so a
    // missed swap fails closed with SQLSTATE[HY000][1049] instead of silently
    // serving another tenant. NEVER set this to safm_platform.
    'sentinel_database' => env('DB_DATABASE', 'safm_unassigned'),

    'database_prefix' => env('TENANCY_DB_PREFIX', 'safm_'),
    'database_charset'   => 'utf8mb4',
    'database_collation' => 'utf8mb4_unicode_ci',

    'reserved_slugs' => [
        'admin', 'www', 'api', 'app', 'mail', 'smtp', 'imap', 'ftp', 'cdn',
        'static', 'assets', 'platform', 'status', 'support', 'billing',
        'help', 'docs', 'blog', 'demo', 'test', 'staging', 'dev', 'localhost',
    ],

    'slug_pattern' => '/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/',

    // Per-tenant TLS. TenantProvisioner drops one request file per tenant into
    // cert_request_dir; the certbot sidecar (docker/certbot/issue-tenant-cert.sh)
    // picks it up and answers the HTTP-01 challenge.
    //
    // Fire-and-forget on purpose: Let's Encrypt allows 50 certificates per
    // registered domain per week and every tenant subdomain draws from that one
    // bucket, so this must never retry in a loop. Until a request is answered the
    // tenant is served by the wildcard self-signed vhost — a browser warning,
    // not an outage.
    'cert_issuance' => env('SAFM_CERT_ISSUANCE', 'off') !== 'off',
    'cert_request_dir' => env('SAFM_CERT_REQUEST_DIR', '/var/www/certbot-requests'),
];
