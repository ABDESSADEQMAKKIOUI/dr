#!/usr/bin/env bash
#
# SAFM entrypoint.
#
# Two mutually exclusive boot paths, chosen by TENANCY_ENABLED:
#
#   TENANCY_ENABLED=false  LEGACY SINGLE-TENANT DEMO (docker-compose.yml alone).
#                          One schema, `migrate` + `db:seed`, the demo dataset,
#                          admin@admin.com / password. Unchanged behaviour.
#
#   TENANCY_ENABLED=true   SAAS PLATFORM (+ docker-compose.prod.yml). The app
#                          schema is a deliberately non-existent sentinel, the
#                          platform schema is installed and migrated, and every
#                          tenant schema is brought up to date on every boot.
#
# Ordering inside both provision paths is load-bearing — see the comments there.

set -euo pipefail

APP_DIR=/var/www/html
cd "$APP_DIR"

READY_MARKER="storage/app/.installed"
KEY_FILE="storage/app/.appkey"
HEALTH_HOST_FILE="storage/app/.healthhost"

log()  { printf '\033[0;36m[safm]\033[0m %s\n' "$*"; }
warn() { printf '\033[0;33m[safm] %s\033[0m\n' "$*" >&2; }
die()  { printf '\033[0;31m[safm] %s\033[0m\n' "$*" >&2; exit 1; }

# ──────────────────────────────────────────────────────────────────────────────
# 0. Resolve the mode and the effective database credentials
# ──────────────────────────────────────────────────────────────────────────────
# PRIVILEGE SPLIT. The application does NOT run as root.
#
# The provisioner needs CREATE DATABASE / DROP DATABASE, which the app user has
# no grant for — but handing the whole ERP root would turn any SQL injection in
# 83 models into server-wide compromise. So there are two identities:
#
#   DB_* / PLATFORM_DB_*        the app user. docker/mysql/init/10-safm-grants.sh
#                               grants it ALL PRIVILEGES ON `safm\_%`.*, which
#                               covers safm_platform and every safm_<tenant>
#                               schema — but NOT *.*, so it cannot CREATE
#                               DATABASE and cannot touch mysql.user.
#   PLATFORM_ADMIN_DB_*         root. Used by exactly one class,
#                               TenantDatabaseManager, on the 'platform_admin'
#                               connection, solely for CREATE/DROP DATABASE.
#
# DB_DATABASE becomes a schema that MUST NOT EXIST. Every request is expected to
# swap the `mysql` connection onto a tenant schema before touching the database;
# if that swap is ever missed, the query has to fail loudly with
# SQLSTATE[HY000][1049] Unknown database rather than silently succeed against
# whatever schema happened to be configured — which would mean serving one
# customer's data to another.
#
# EXPORTING THESE IS LOAD-BEARING, NOT TIDINESS. Laravel's Dotenv is immutable:
# it never overwrites a variable that already exists in the real environment. The
# compose file passes DB_DATABASE=safm and DB_USERNAME=safm as container env
# vars, so writing different values into .env has NO EFFECT — the compose values
# win. Without the exports below, the app would quietly connect to the `safm`
# schema instead of the non-existent sentinel, and the fail-closed guarantee
# above would be silently void.
TENANCY_ENABLED="${TENANCY_ENABLED:-false}"

TENANCY_ROOT_DOMAIN="${TENANCY_ROOT_DOMAIN:-facturation.cfpss.ma}"
TENANCY_ADMIN_DOMAIN="${TENANCY_ADMIN_DOMAIN:-admin.${TENANCY_ROOT_DOMAIN}}"
TENANCY_DB_PREFIX="${TENANCY_DB_PREFIX:-safm_}"
SENTINEL_DATABASE="${TENANCY_SENTINEL_DATABASE:-safm_unassigned}"
PLATFORM_DB_DATABASE="${PLATFORM_DB_DATABASE:-safmctl_platform}"

if [ "$TENANCY_ENABLED" = "true" ]; then
    [ -n "${DB_ROOT_PASSWORD:-}" ] \
        || die "TENANCY_ENABLED=true requires DB_ROOT_PASSWORD (provisioning issues CREATE DATABASE / DROP DATABASE)"

    EFFECTIVE_DB_DATABASE="$SENTINEL_DATABASE"
    EFFECTIVE_DB_USERNAME="${DB_USERNAME:-safm}"
    EFFECTIVE_DB_PASSWORD="${DB_PASSWORD:-secret}"

    # The elevated identity, used only by TenantDatabaseManager.
    # The control plane has its OWN MySQL identity, with no grant on any
    # tenant schema, so an SQL injection in the ERP cannot reach operator
    # password hashes or subscriptions.
    PLATFORM_DB_USERNAME="${PLATFORM_DB_USERNAME:-safmctl}"
    PLATFORM_DB_PASSWORD="${PLATFORM_DB_PASSWORD:-$DB_PASSWORD}"
    export PLATFORM_DB_USERNAME PLATFORM_DB_PASSWORD

    PLATFORM_ADMIN_DB_USERNAME="${PLATFORM_ADMIN_DB_USERNAME:-root}"
    PLATFORM_ADMIN_DB_PASSWORD="${PLATFORM_ADMIN_DB_PASSWORD:-$DB_ROOT_PASSWORD}"
    export PLATFORM_ADMIN_DB_USERNAME PLATFORM_ADMIN_DB_PASSWORD

    if [ "$SENTINEL_DATABASE" = "$PLATFORM_DB_DATABASE" ]; then
        die "TENANCY_SENTINEL_DATABASE must never equal PLATFORM_DB_DATABASE (${PLATFORM_DB_DATABASE})"
    fi
else
    EFFECTIVE_DB_DATABASE="${DB_DATABASE:-safm}"
    EFFECTIVE_DB_USERNAME="${DB_USERNAME:-safm}"
    EFFECTIVE_DB_PASSWORD="${DB_PASSWORD:-secret}"
fi

# Override the inherited container environment so Apache, artisan and every
# child process see the EFFECTIVE values rather than compose's raw ones.
export DB_DATABASE="$EFFECTIVE_DB_DATABASE"
export DB_USERNAME="$EFFECTIVE_DB_USERNAME"
export DB_PASSWORD="$EFFECTIVE_DB_PASSWORD"
export PLATFORM_DB_DATABASE
export TENANCY_ENABLED TENANCY_ROOT_DOMAIN TENANCY_ADMIN_DOMAIN TENANCY_DB_PREFIX

# ──────────────────────────────────────────────────────────────────────────────
# 1. Rebuild the storage tree
# ──────────────────────────────────────────────────────────────────────────────
# A named volume is mounted over storage/. A fresh volume is empty and masks the
# directories baked into the image, so Laravel would fail on its first attempt to
# write a compiled view or a session file. Recreate the whole subtree every boot.
#
# app/exports matters specifically: ProductService::export() builds
# storage_path('app/exports/...') without mkdir'ing it first, so CSV export fails
# unless the directory already exists.
prepare_filesystem() {
    mkdir -p \
        storage/app/public \
        storage/app/backups \
        storage/app/exports \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache

    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache

    # The certificate request queue shared with the certbot container.
    # TenantProvisioner step 14 runs as www-data under Apache and as root under
    # `artisan tenant:provision`, so it has to be group-writable. A fresh named
    # volume arrives root:root 0755, which www-data cannot write.
    if [ "${SAFM_CERT_ISSUANCE:-off}" != "off" ]; then
        local request_dir="${SAFM_CERT_REQUEST_DIR:-/var/www/certbot-requests}"
        mkdir -p "$request_dir"
        chown www-data:www-data "$request_dir"
        chmod 775 "$request_dir"
    fi
}

# ──────────────────────────────────────────────────────────────────────────────
# 2. Resolve APP_KEY
# ──────────────────────────────────────────────────────────────────────────────
# Sessions and encrypted settings break if the key changes between boots, and
# .env lives in the image layer rather than a volume — so a key generated into
# .env would be lost on every `docker compose up --force-recreate`. Persist it on
# the storage volume instead. An APP_KEY supplied by compose always wins.
resolve_app_key() {
    if [ -n "${APP_KEY:-}" ]; then
        return
    fi

    if [ -s "$KEY_FILE" ]; then
        APP_KEY="$(cat "$KEY_FILE")"
        log "reusing the APP_KEY persisted on the storage volume"
    else
        # Same output format as `artisan key:generate`, without booting the
        # framework (which would need a database connection at this point).
        APP_KEY="$(php -r 'echo "base64:" . base64_encode(random_bytes(32));')"
        printf '%s' "$APP_KEY" > "$KEY_FILE"
        chown www-data:www-data "$KEY_FILE"
        chmod 600 "$KEY_FILE"
        log "generated a new APP_KEY and persisted it"
    fi

    export APP_KEY
}

# ──────────────────────────────────────────────────────────────────────────────
# 3. Materialise .env
# ──────────────────────────────────────────────────────────────────────────────
# The container environment is the single source of truth, so .env is rewritten
# on every boot. A real file is still needed: InstallController checks
# is_writable(base_path('.env')), and it keeps `artisan` usable via `exec`.
#
# The driver choices here are not cosmetic. config/session.php, config/cache.php
# and config/queue.php all default to 'database', and .env.example sets them to
# database — but this project has NO sessions, cache, cache_locks, jobs,
# job_batches or failed_jobs migration. Leaving the defaults makes the very first
# request fatal with "Base table or view not found: sessions".
write_env() {
    log "writing .env from the container environment"

    local app_url="${APP_URL:-http://localhost:8080}"

    # A secure cookie over plain http is simply never sent, which looks exactly
    # like "login does nothing". Derive the default from the scheme instead of
    # hard-coding true, so a local https-less SaaS test still logs in.
    local session_secure="${SESSION_SECURE_COOKIE:-}"
    if [ -z "$session_secure" ]; then
        case "$app_url" in
            https://*) session_secure=true ;;
            *)         session_secure=false ;;
        esac
    fi

    cat > .env <<ENV
APP_NAME="${APP_NAME:-SAFM}"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_TIMEZONE=${APP_TIMEZONE:-Africa/Casablanca}
APP_URL=${app_url}

# SystemDataSeeder marks 'fr' as the default language and SettingsSeeder sets
# default_language=fr, so the seeded demo content is French.
APP_LOCALE=${APP_LOCALE:-fr}
APP_FALLBACK_LOCALE=${APP_FALLBACK_LOCALE:-en}
APP_FAKER_LOCALE=fr_FR

APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=${LOG_LEVEL:-warning}

# MySQL is not optional: migrations use MODIFY COLUMN ... ENUM, DemoDataSeeder
# uses SET FOREIGN_KEY_CHECKS + TRUNCATE, and DashboardService uses DATE_FORMAT.
DB_CONNECTION=mysql
DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${EFFECTIVE_DB_DATABASE}
DB_USERNAME=${EFFECTIVE_DB_USERNAME}
DB_PASSWORD=${EFFECTIVE_DB_PASSWORD}

# file/file/sync — the matching database tables have no migrations in this repo.
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
# MUST stay null. A shared cookie domain (.${TENANCY_ROOT_DOMAIN}) would send one
# session cookie to every tenant host AND to the operator console, which is a
# cross-tenant account takeover. Host-only cookies are the outer control; the
# per-tenant session directory is the inner one.
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=${session_secure}

CACHE_STORE=file
CACHE_PREFIX=
QUEUE_CONNECTION=sync
BROADCAST_CONNECTION=log

# Uploads are written to the 'public' disk explicitly, but several views call
# Storage::url() on the DEFAULT disk. They only agree when the default is public.
FILESYSTEM_DISK=public

# 'log' keeps the demo from attempting outbound SMTP when an invoice is emailed.
MAIL_MAILER=${MAIL_MAILER:-log}
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS:-demo@safm.local}"
MAIL_FROM_NAME="\${APP_NAME}"
ENV

    if [ "$TENANCY_ENABLED" = "true" ]; then
        cat >> .env <<ENV

# ── Multi-tenancy ─────────────────────────────────────────────────────────────
# DB_DATABASE above is a schema that DOES NOT EXIST. ResolveTenant swaps the
# 'mysql' connection onto the tenant's own schema at position 0 of the 'tenant'
# middleware group; anything that reaches the database without that swap fails
# with SQLSTATE[HY000][1049] instead of reading another customer's data.
TENANCY_ENABLED=true
TENANCY_ROOT_DOMAIN=${TENANCY_ROOT_DOMAIN}
TENANCY_ADMIN_DOMAIN=${TENANCY_ADMIN_DOMAIN}
TENANCY_DB_PREFIX=${TENANCY_DB_PREFIX}

# The platform connection is a separate entry in config/database.php and is the
# only one with a live PDO while a tenant schema is being created or dropped.
PLATFORM_DB_DATABASE=${PLATFORM_DB_DATABASE}
ENV
    else
        cat >> .env <<ENV

# Single-tenant legacy mode: no host validation, the install wizard is reachable
# and DB_DATABASE above is a real schema.
TENANCY_ENABLED=false
ENV
    fi

    chown www-data:www-data .env
    chmod 664 .env
}

# ──────────────────────────────────────────────────────────────────────────────
# 4. Wait for MySQL
# ──────────────────────────────────────────────────────────────────────────────
# In SaaS mode the DSN carries NO dbname: DB_DATABASE is the sentinel and does
# not exist, so a dbname-qualified probe could never succeed.
wait_for_database() {
    local attempts="${DB_WAIT_ATTEMPTS:-90}"
    local i=1
    local probe_database=""

    if [ "$TENANCY_ENABLED" != "true" ]; then
        probe_database="$EFFECTIVE_DB_DATABASE"
        log "waiting for mysql at ${DB_HOST:-db}:${DB_PORT:-3306}/${probe_database} ..."
    else
        log "waiting for mysql at ${DB_HOST:-db}:${DB_PORT:-3306} ..."
    fi

    while [ "$i" -le "$attempts" ]; do
        if PROBE_DB="$probe_database" \
           PROBE_USER="$EFFECTIVE_DB_USERNAME" \
           PROBE_PASS="$EFFECTIVE_DB_PASSWORD" php -r '
            $dsn = sprintf("mysql:host=%s;port=%s",
                getenv("DB_HOST") ?: "db",
                getenv("DB_PORT") ?: "3306");
            if (getenv("PROBE_DB") !== "") {
                $dsn .= ";dbname=" . getenv("PROBE_DB");
            }
            try {
                new PDO($dsn, getenv("PROBE_USER"), getenv("PROBE_PASS"),
                    [PDO::ATTR_TIMEOUT => 3]);
                exit(0);
            } catch (Throwable $e) {
                exit(1);
            }
        ' 2>/dev/null; then
            log "database is up (after ${i}s)"
            return 0
        fi
        i=$((i + 1))
        sleep 1
    done

    die "database did not become reachable after ${attempts}s"
}

# Returns 0 when the named schema exists on the server.
database_exists() {
    PROBE_SCHEMA="$1" \
    PROBE_USER="$EFFECTIVE_DB_USERNAME" \
    PROBE_PASS="$EFFECTIVE_DB_PASSWORD" php -r '
        $dsn = sprintf("mysql:host=%s;port=%s",
            getenv("DB_HOST") ?: "db",
            getenv("DB_PORT") ?: "3306");
        try {
            $pdo = new PDO($dsn, getenv("PROBE_USER"), getenv("PROBE_PASS"));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name = ?"
            );
            $stmt->execute([getenv("PROBE_SCHEMA")]);
            exit(((int) $stmt->fetchColumn()) > 0 ? 0 : 1);
        } catch (Throwable $e) {
            exit(1);
        }
    ' 2>/dev/null
}

# Returns 0 when every table named in $1 (comma-separated) exists in schema $2
# and is non-empty. State is read from the database rather than a marker file so
# that wiping the db volume alone re-triggers a seed.
tables_are_populated() {
    TABLES="$1" \
    PROBE_SCHEMA="$2" \
    PROBE_USER="$EFFECTIVE_DB_USERNAME" \
    PROBE_PASS="$EFFECTIVE_DB_PASSWORD" php -r '
        $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s",
            getenv("DB_HOST") ?: "db",
            getenv("DB_PORT") ?: "3306",
            getenv("PROBE_SCHEMA"));
        try {
            $pdo = new PDO($dsn, getenv("PROBE_USER"), getenv("PROBE_PASS"));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            foreach (explode(",", getenv("TABLES")) as $table) {
                $n = (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
                if ($n === 0) {
                    exit(1);
                }
            }
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }
    ' 2>/dev/null
}

# ── Legacy gates ──────────────────────────────────────────────────────────────
# The two phases are gated separately, and each gate checks a table that the LAST
# seeder of its phase writes.
#
# Checking `users` alone would be wrong: AdminUserSeeder is 4th of the 6 seeders
# in DatabaseSeeder, so an interruption after it — a transient DB error, an OOM
# kill, a `docker compose stop` during first boot — would latch the gate to
# "seeded" with UnitSeeder, SmsTemplateSeeder and the entire demo dataset still
# missing. Combined with `restart: unless-stopped`, that state is permanent.
#
# `settings` and `languages` come from SettingsSeeder and SystemDataSeeder, which
# are also what LocaleMiddleware and the login page depend on.
base_is_seeded() {
    tables_are_populated 'users,settings,languages,units,sms_templates' "$EFFECTIVE_DB_DATABASE"
}

# Gating the demo phase on its own table is what makes DEMO_SEED reversible:
# booting once with DEMO_SEED=false must not prevent a later DEMO_SEED=true from
# seeding. DemoDataSeeder truncates before it writes, so re-running is safe.
demo_is_seeded() {
    tables_are_populated 'products,customers,sales,invoices' "$EFFECTIVE_DB_DATABASE"
}

# ── SaaS gate ─────────────────────────────────────────────────────────────────
# `platform:install` creates the first operator, and re-running it with --force
# on every boot would rewrite that operator's password from a value nobody kept.
# So install once, and from then on apply schema changes only.
platform_is_installed() {
    tables_are_populated 'platform_users,plans' "$PLATFORM_DB_DATABASE"
}

# ──────────────────────────────────────────────────────────────────────────────
# 5a. Provision — legacy single-tenant
# ──────────────────────────────────────────────────────────────────────────────
provision_single_tenant() {
    php artisan migrate --force --ansi

    # The base seeders are not idempotent — SettingsSeeder uses Setting::create()
    # against a UNIQUE key column and AdminUserSeeder uses User::create() against
    # a UNIQUE email — so a second run against a populated database throws.
    if base_is_seeded; then
        log "base data already present, skipping base seeders"
    else
        log "seeding roles, permissions, settings, languages and the admin account"
        php artisan db:seed --force --ansi
    fi

    # Left out of DatabaseSeeder upstream (commented out). It truncates and
    # refills the catalogue, customers, suppliers, purchases, sales, invoices and
    # expenses with Carbon-relative dates, which is what makes the dashboard
    # charts look alive. Gated independently of the base seeders so that flipping
    # DEMO_SEED from false to true on an existing instance actually works.
    if [ "${DEMO_SEED:-true}" != "true" ]; then
        log "DEMO_SEED is not 'true', skipping demo data"
    elif demo_is_seeded; then
        log "demo data already present, skipping demo seeder"
    else
        log "seeding demo data (products, customers, sales, invoices, expenses)"
        php artisan db:seed --force --ansi --class='Database\Seeders\DemoDataSeeder'
    fi
}

# ──────────────────────────────────────────────────────────────────────────────
# 5b. Provision — SaaS platform
# ──────────────────────────────────────────────────────────────────────────────
provision_saas() {
    # The sentinel is load-bearing precisely BECAUSE it does not exist. If
    # somebody points MYSQL_DATABASE at it, or a stray CREATE DATABASE brings it
    # into being, an unresolved tenant silently starts reading an empty-but-real
    # schema instead of throwing 1049 — and the fail-closed guarantee is gone.
    if database_exists "$SENTINEL_DATABASE"; then
        die "the sentinel schema '${SENTINEL_DATABASE}' EXISTS on the server. It must not. \
Drop it (DROP DATABASE \`${SENTINEL_DATABASE}\`) or point TENANCY_SENTINEL_DATABASE at a name \
that is never created. While it exists, a request that fails to resolve its tenant reads an \
empty schema instead of failing loudly."
    fi

    # The control-plane schema has to be created with root. Neither the ERP user
    # nor the control-plane user holds CREATE on it, by design — that is exactly
    # what stops an SQL injection in the ERP from reaching operators and billing.
    #
    # Laravel 11's `migrate` will silently create a missing MySQL database if the
    # connection's user happens to have the grant. Relying on that is what hid
    # this dependency before; doing it explicitly here keeps the grants tight.
    ensure_platform_schema() {
        PLATFORM_DB_DATABASE="$PLATFORM_DB_DATABASE" \
        DB_HOST="${DB_HOST:-db}" DB_PORT="${DB_PORT:-3306}" \
        ADMIN_USER="$PLATFORM_ADMIN_DB_USERNAME" ADMIN_PASS="$PLATFORM_ADMIN_DB_PASSWORD" \
        php -r '
            $db = getenv("PLATFORM_DB_DATABASE");
            if (!preg_match("/^[A-Za-z0-9_]{1,64}$/", $db)) {
                fwrite(STDERR, "refusing an implausible platform schema name: {$db}\n");
                exit(1);
            }
            $dsn = sprintf("mysql:host=%s;port=%s", getenv("DB_HOST"), getenv("DB_PORT"));
            try {
                $pdo = new PDO($dsn, getenv("ADMIN_USER"), getenv("ADMIN_PASS"));
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                exit(0);
            } catch (Throwable $e) {
                fwrite(STDERR, $e->getMessage() . "\n");
                exit(1);
            }
        ' || die "could not create the control-plane schema ${PLATFORM_DB_DATABASE}"
    }

    ensure_platform_schema

    if platform_is_installed; then
        log "platform schema present — applying pending platform migrations"
        php artisan platform:migrate --force --no-interaction --ansi
    else
        local operator_email="${PLATFORM_ADMIN_EMAIL:-admin@${TENANCY_ROOT_DOMAIN}}"
        local operator_name="${PLATFORM_ADMIN_NAME:-Platform Owner}"
        local operator_password="${PLATFORM_ADMIN_PASSWORD:-}"
        local generated=false

        if [ -z "$operator_password" ]; then
            operator_password="$(php -r 'echo bin2hex(random_bytes(9));')"
            generated=true
        fi

        log "installing the platform schema (${PLATFORM_DB_DATABASE}) and the first operator"
        php artisan platform:install --force --no-interaction --ansi \
            --email="$operator_email" \
            --name="$operator_name" \
            --password="$operator_password"

        log "operator account: ${operator_email}"
        if [ "$generated" = "true" ]; then
            warn "generated operator password: ${operator_password}"
            warn "This is printed ONCE. Set PLATFORM_ADMIN_PASSWORD in .env to pin it."
        fi
    fi

    # MANDATORY, NON-SKIPPABLE. A deploy that adds an ERP migration does nothing
    # to the tenant schemas until this runs, and the symptom is a tenant serving
    # new code against an old schema — "Unknown column" on a page that worked
    # yesterday. `set -e` makes a failure here abort the boot BEFORE Apache
    # starts, which is deliberate: a half-migrated fleet must not take traffic.
    log "applying pending ERP migrations to every tenant"
    php artisan tenant:migrate --all --force --no-interaction --ansi
}

# ──────────────────────────────────────────────────────────────────────────────
# 5. Provision
# ──────────────────────────────────────────────────────────────────────────────
provision() {
    # composer ran with --no-scripts during the build, so the package manifest
    # was never generated.
    php artisan package:discover --ansi

    php artisan optimize:clear --ansi

    if [ "$TENANCY_ENABLED" = "true" ]; then
        provision_saas
    else
        provision_single_tenant
    fi

    # public/storage -> storage/app/public. Absent from the repo and gitignored;
    # ~15 views call asset('storage/...') for product images and the company logo.
    php artisan storage:link --force --ansi

    if [ "${APP_DEBUG:-false}" = "true" ]; then
        php artisan optimize:clear --ansi
        # docker/php/php.ini sets opcache.validate_timestamps=0, which is right
        # when the code is baked into the image but silently ignores edits if you
        # bind-mount the source or `docker cp` a hotfix in. Re-enable revalidation
        # whenever the container is running in debug mode.
        log "APP_DEBUG=true — enabling opcache timestamp revalidation"
        printf 'opcache.validate_timestamps=1\nopcache.revalidate_freq=0\n' \
            > /usr/local/etc/php/conf.d/zzz-safm-debug.ini
    else
        rm -f /usr/local/etc/php/conf.d/zzz-safm-debug.ini
        # config:cache and view:cache are safe here — the app makes zero env()
        # calls outside config/.
        #
        # route:cache is NOT safe: routes/web.php registers `Route::get('docs',
        # fn() => view(...))` and caching aborts on a Closure route.
        php artisan config:cache --ansi
        php artisan view:cache --ansi
    fi

    # Flip the gate LAST. In legacy mode CheckInstalled redirects every
    # non-/install request to the wizard while this file is missing; and with the
    # marker present but the languages table unseeded, LocaleMiddleware ->
    # LanguageService::getDefault() firstOrFail()s and every page 404s.
    #
    # In SaaS mode CheckInstalled short-circuits on config('tenancy.enabled') and
    # this file is inert — it is still written so that flipping TENANCY_ENABLED
    # back to false in a dev container does not land on the install wizard.
    date '+%Y-%m-%d %H:%M:%S' > "$READY_MARKER"
    chown www-data:www-data "$READY_MARKER"

    # The Host header the Dockerfile's HEALTHCHECK must send. Written from here
    # because the admin domain is derived, not passed: probing 127.0.0.1 with no
    # Host would hit ResolveTenant's 404 and mark a perfectly healthy SaaS
    # container unhealthy, which in turn stops nginx from ever starting.
    if [ "$TENANCY_ENABLED" = "true" ]; then
        printf '%s' "$TENANCY_ADMIN_DOMAIN" > "$HEALTH_HOST_FILE"
    else
        printf '%s' 'localhost' > "$HEALTH_HOST_FILE"
    fi
    chown www-data:www-data "$HEALTH_HOST_FILE"

    # artisan wrote caches and logs as root; hand them back to Apache.
    chown -R www-data:www-data storage bootstrap/cache

    if [ "$TENANCY_ENABLED" = "true" ]; then
        log "provisioning complete"
        log "  operator console: https://${TENANCY_ADMIN_DOMAIN}/login"
        log "  tenants:          https://<slug>.${TENANCY_ROOT_DOMAIN}/login"
        log "  new tenant:       php artisan tenant:provision <slug> ..."
    else
        log "provisioning complete — log in at ${APP_URL:-http://localhost:8080}/login"
        log "  email:    admin@admin.com"
        log "  password: password"
    fi
}

# ──────────────────────────────────────────────────────────────────────────────
main() {
    if [ "$TENANCY_ENABLED" = "true" ]; then
        log "mode: SaaS (root domain ${TENANCY_ROOT_DOMAIN}, console ${TENANCY_ADMIN_DOMAIN})"
    else
        log "mode: single-tenant demo"
    fi

    prepare_filesystem
    resolve_app_key
    write_env
    wait_for_database
    provision

    log "starting Apache"
    exec "$@"
}

main "$@"
