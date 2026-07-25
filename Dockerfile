# syntax=docker/dockerfile:1
#
# SAFM — Docker image for the demo deployment.
#
# Apache + mod_php in a single container, matching the Apache/.htaccess setup the
# project already ships with (public/.htaccess is the stock Laravel front controller).
#
# PHP 8.4 is mandatory, not a preference. composer.json says ">=8.2" and
# InstallController::checkRequirements() checks for 8.2, but both are stale:
# composer.lock pins "platform": { "php": ">=8.4" } and five non-dev symfony v8
# packages hard-require >=8.4. On 8.2/8.3 `composer install` refuses to run.

# ──────────────────────────────────────────────────────────────────────────────
# Stage 1 — base runtime
# ──────────────────────────────────────────────────────────────────────────────
FROM php:8.4-apache AS base

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# Extensions installed on top of the official image's built-ins:
#   pdo_mysql  the app is MySQL-only (raw DATE_FORMAT/TRUNCATE/MODIFY COLUMN SQL)
#   gd         InstallController::checkRequirements() refuses to pass without
#              gd or imagick; gd is the smaller of the two
#   zip        app/Services/BackupService.php uses ZipArchive — an undeclared
#              runtime dependency that appears in no composer requirement
#   bcmath     free (no system library) and expected by stripe-php
#   opcache    the code is baked into the image, so it can be cached hard
#
# Deliberately NOT installed: intl and exif (zero call sites in app/, and intl
# would drag in ~40 MB of libicu), imagick, redis, pcntl.
#
# default-mysql-client is a runtime dependency: BackupService.php:51 shells out
# to `mysqldump` and never checks the exit code, so without the binary the
# backup screen silently writes a corrupt dump.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        unzip \
        default-mysql-client \
    ; \
    rm -rf /var/lib/apt/lists/*

# The -dev headers are installed, the extensions are compiled, and then the
# headers are removed again while the shared libraries they actually link
# against are kept. Those runtime libraries are discovered with ldd rather than
# named explicitly — this is the pattern the official PHP images use, and it
# avoids guessing soname-versioned package names that move between Debian
# releases (bookworm ships libzip4, trixie ships libzip5).
RUN set -eux; \
    savedAptMark="$(apt-mark showmanual)"; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libfreetype-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
    ; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gd \
        opcache \
        pdo_mysql \
        zip \
    ; \
    apt-mark auto '.*' > /dev/null; \
    apt-mark manual $savedAptMark > /dev/null; \
    ldd "$(php -r 'echo ini_get("extension_dir");')"/*.so \
        | awk '/=>/ { so = $(NF-1); if (index(so, "/usr/local/") == 0) { printf "*%s\n", so } }' \
        | sort -u \
        | xargs -r dpkg-query --search 2>/dev/null \
        | cut -d: -f1 \
        | sort -u \
        | xargs -r apt-mark manual \
    ; \
    apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false; \
    rm -rf /var/lib/apt/lists/*

# On Debian, `default-mysql-client` is MariaDB's client: /usr/bin/mysqldump is a
# symlink to mariadb-dump. MariaDB 11.x negotiates TLS by default and verifies
# the server certificate chain, while mysql:8.0 auto-generates a self-signed CA
# on first boot — so the connection is refused before a single byte is dumped:
#
#   mysqldump: Got error: 2026: "TLS/SSL error: self-signed certificate in
#   certificate chain" when trying to connect
#
# app/Services/BackupService.php builds a bare `mysqldump -h ... > file` with no
# --ssl flag and calls exec() WITHOUT checking the exit code, so the failure is
# invisible: the app zips the 0-byte dump and reports success. This drop-in fixes
# it without touching application source — the client reads
# /etc/mysql/my.cnf, which !includedir's conf.d.
#
# no-tablespaces silences the residual "you need the PROCESS privilege" warning,
# which the demo's non-root DB user does not have.
RUN set -eux; \
    mkdir -p /etc/mysql/conf.d; \
    printf '[client]\nssl=0\n\n[mysqldump]\nno-tablespaces\n' \
        > /etc/mysql/conf.d/99-safm-client.cnf

# remoteip: restores the real client IP from X-Forwarded-For when running behind
# the nginx edge (see docker/apache/vhost.conf).
RUN set -eux; \
    a2enmod rewrite headers expires remoteip; \
    printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf; \
    a2enconf servername

COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-safm.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# ──────────────────────────────────────────────────────────────────────────────
# Stage 2 — vendor
# ──────────────────────────────────────────────────────────────────────────────
# Composer runs inside the same PHP build so its platform check sees the exact
# extension set the application will run with.
FROM base AS vendor

WORKDIR /var/www/html

COPY composer.json composer.lock ./

# --no-dev is verified safe: no seeder references fakerphp/faker or
#   database/factories (those are used only by tests/), so seeding still works.
# --no-scripts is deliberate: post-autoload-dump runs `artisan package:discover`,
#   which boots the framework, and there is no .env or database during a build.
#   The entrypoint runs package:discover instead.
# Composer prints "The lock file is not up to date with the latest changes in
#   composer.json" because composer.json still says >=8.2 while the lock says
#   >=8.4. That warning is expected — do not `composer update` to silence it.
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-progress

COPY . .

# --optimize (classmap) but deliberately not --classmap-authoritative: the latter
# makes any class outside the classmap unloadable, which is a sharp edge for very
# little gain on a demo.
RUN composer dump-autoload --no-dev --optimize --no-scripts

# ──────────────────────────────────────────────────────────────────────────────
# Stage 3 — final application image
# ──────────────────────────────────────────────────────────────────────────────
FROM base AS app

WORKDIR /var/www/html

COPY --from=vendor --chown=www-data:www-data /var/www/html /var/www/html

COPY --chmod=0755 docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Files that must never reach a running demo.
#
# run_setup.php sits under public/, so it bypasses Laravel routing and the
# CheckInstalled gate entirely. Unauthenticated, it prints the last 30 lines of
# storage/logs/laravel.log, shell_exec()s chmod, and runs `artisan migrate`.
# error_log leaks absolute host paths and stack traces from the live server.
# composer.phar is 3.3 MB of dead weight and a second executable under the tree.
#
# .dockerignore already excludes them from the build context; this is the
# belt-and-braces pass in case the image is built from a different context.
RUN rm -f \
    run_setup.php \
    public/run_setup.php \
    error_log \
    public/error_log \
    composer.phar

# The compose file mounts a named volume over storage/, so the entrypoint
# rebuilds this subtree on every boot. Creating it here keeps the image usable
# on its own too.
RUN set -eux; \
    mkdir -p \
        storage/app/public \
        storage/app/backups \
        storage/app/exports \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod -R 775 storage bootstrap/cache

EXPOSE 80

# Deliberately NOT Laravel's /up endpoint. ApplicationBuilder registers the
# health route OUTSIDE the web group, so it carries no middleware at all: it
# never touches CheckInstalled, LocaleMiddleware, the session or the database,
# and returns 200 from a static Blade file even when the app is entirely broken.
# A container with a stopped database, or one still redirecting everything to
# /install, would report healthy.
#
# /login exercises the real stack — CheckInstalled, then LocaleMiddleware ->
# LanguageService::getDefault(), which reads the languages table.
#   test -f .installed  fails while provisioning is still running
#   --max-redirs 0      makes the 302 -> /install of a half-provisioned app fail,
#                       which plain `curl -f` would let through
# start-period covers the first-boot migrate + seed.
HEALTHCHECK --interval=15s --timeout=5s --start-period=180s --retries=10 \
    CMD test -f /var/www/html/storage/app/.installed \
     && curl -fsS --max-redirs 0 -o /dev/null http://127.0.0.1/login || exit 1

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
