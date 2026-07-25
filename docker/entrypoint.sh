#!/usr/bin/env bash
#
# SAFM demo entrypoint.
#
# Takes a freshly started container to a state where http://localhost:8080/login
# works, with no manual steps and without the install wizard.
#
# Ordering in provision() is load-bearing — see the comments there.

set -euo pipefail

APP_DIR=/var/www/html
cd "$APP_DIR"

READY_MARKER="storage/app/.installed"
KEY_FILE="storage/app/.appkey"

log()  { printf '\033[0;36m[safm]\033[0m %s\n' "$*"; }
warn() { printf '\033[0;33m[safm] %s\033[0m\n' "$*" >&2; }
die()  { printf '\033[0;31m[safm] %s\033[0m\n' "$*" >&2; exit 1; }

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

    cat > .env <<ENV
APP_NAME="${APP_NAME:-SAFM}"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_TIMEZONE=${APP_TIMEZONE:-Africa/Casablanca}
APP_URL=${APP_URL:-http://localhost:8080}

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
DB_DATABASE=${DB_DATABASE:-safm}
DB_USERNAME=${DB_USERNAME:-safm}
DB_PASSWORD=${DB_PASSWORD:-secret}

# file/file/sync — the matching database tables have no migrations in this repo.
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=file
CACHE_PREFIX=
QUEUE_CONNECTION=sync
BROADCAST_CONNECTION=log

# Uploads are written to the 'public' disk explicitly, but several views call
# Storage::url() on the DEFAULT disk. They only agree when the default is public.
FILESYSTEM_DISK=public

# 'log' keeps the demo from attempting outbound SMTP when an invoice is emailed.
MAIL_MAILER=log
MAIL_FROM_ADDRESS="demo@safm.local"
MAIL_FROM_NAME="\${APP_NAME}"
ENV

    chown www-data:www-data .env
    chmod 664 .env
}

# ──────────────────────────────────────────────────────────────────────────────
# 4. Wait for MySQL
# ──────────────────────────────────────────────────────────────────────────────
wait_for_database() {
    local attempts="${DB_WAIT_ATTEMPTS:-90}"
    local i=1

    log "waiting for mysql at ${DB_HOST:-db}:${DB_PORT:-3306}/${DB_DATABASE:-safm} ..."
    while [ "$i" -le "$attempts" ]; do
        if php -r '
            $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s",
                getenv("DB_HOST") ?: "db",
                getenv("DB_PORT") ?: "3306",
                getenv("DB_DATABASE") ?: "safm");
            try {
                new PDO($dsn, getenv("DB_USERNAME") ?: "safm", getenv("DB_PASSWORD") ?: "secret",
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

# Returns 0 when every table named in $1 (comma-separated) exists and is
# non-empty. State is read from the database rather than a marker file so that
# wiping the db volume alone re-triggers a seed.
tables_are_populated() {
    TABLES="$1" php -r '
        $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s",
            getenv("DB_HOST") ?: "db",
            getenv("DB_PORT") ?: "3306",
            getenv("DB_DATABASE") ?: "safm");
        try {
            $pdo = new PDO($dsn, getenv("DB_USERNAME") ?: "safm", getenv("DB_PASSWORD") ?: "secret");
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
base_is_seeded() { tables_are_populated 'users,settings,languages,units,sms_templates'; }

# Gating the demo phase on its own table is what makes DEMO_SEED reversible:
# booting once with DEMO_SEED=false must not prevent a later DEMO_SEED=true from
# seeding. DemoDataSeeder truncates before it writes, so re-running is safe.
demo_is_seeded() { tables_are_populated 'products,customers,sales,invoices'; }

# ──────────────────────────────────────────────────────────────────────────────
# 5. Provision
# ──────────────────────────────────────────────────────────────────────────────
provision() {
    # composer ran with --no-scripts during the build, so the package manifest
    # was never generated.
    php artisan package:discover --ansi

    php artisan optimize:clear --ansi

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

    # Flip the gate LAST. CheckInstalled redirects every non-/install request to
    # the wizard while this file is missing; and with the marker present but the
    # languages table unseeded, LocaleMiddleware -> LanguageService::getDefault()
    # firstOrFail()s and every page 404s. Both failure modes are avoided by
    # writing it only after migrate + seed have succeeded.
    date '+%Y-%m-%d %H:%M:%S' > "$READY_MARKER"
    chown www-data:www-data "$READY_MARKER"

    # artisan wrote caches and logs as root; hand them back to Apache.
    chown -R www-data:www-data storage bootstrap/cache

    log "provisioning complete — log in at ${APP_URL:-http://localhost:8080}/login"
    log "  email:    admin@admin.com"
    log "  password: password"
}

# ──────────────────────────────────────────────────────────────────────────────
main() {
    prepare_filesystem
    resolve_app_key
    write_env
    wait_for_database
    provision

    log "starting Apache"
    exec "$@"
}

main "$@"
