#!/bin/sh
#
# Runs from /docker-entrypoint.d/ before nginx starts.
#
#   1. builds the basic-auth file from the environment
#   2. writes the shared proxy snippet included by every vhost
#   3. picks certificates for the apex and for admin.<root> — the real Let's
#      Encrypt ones if they exist, otherwise a self-signed placeholder so nginx
#      can boot and answer the ACME challenge
#   4. renders conf.d/default.conf from safm.conf.template
#   5. renders one conf.d/tenant-<host>.conf per issued tenant certificate
#   6. backgrounds a watcher that re-renders (5) and reloads when
#      /etc/letsencrypt changes — a newly issued tenant certificate goes live
#      within SAFM_CERT_WATCH_INTERVAL without anyone touching the container

set -eu

SERVER_NAME="${SAFM_SERVER_NAME:-}"
ROOT_DOMAIN="${SAFM_ROOT_DOMAIN:-$SERVER_NAME}"
ADMIN_SERVER_NAME="${SAFM_ADMIN_SERVER_NAME:-admin.${ROOT_DOMAIN}}"
UPSTREAM="${SAFM_UPSTREAM:-app:80}"
AUTH_USER="${SAFM_AUTH_USER:-}"
AUTH_PASSWORD="${SAFM_AUTH_PASSWORD:-}"
AUTH_REALM="${SAFM_AUTH_REALM:-SAFM Demo}"
TENANT_BASIC_AUTH="${SAFM_TENANT_BASIC_AUTH:-false}"
APEX_BASIC_AUTH="${SAFM_APEX_BASIC_AUTH:-false}"
PUBLIC_STORAGE="${SAFM_PUBLIC_STORAGE:-false}"
WATCH_INTERVAL="${SAFM_CERT_WATCH_INTERVAL:-60}"

LE_ROOT="/etc/letsencrypt/live"
SELF_DIR="/etc/nginx/ssl"
HTPASSWD="/etc/nginx/safm.htpasswd"
PROXY_SNIPPET="/etc/nginx/safm-proxy.conf"
TENANT_TEMPLATE="/etc/nginx/tenant.conf.template"

log()  { printf '\033[0;36m[nginx]\033[0m %s\n' "$*"; }
die()  { printf '\033[0;31m[nginx] %s\033[0m\n' "$*" >&2; exit 1; }

[ -n "$SERVER_NAME" ] || die "SAFM_SERVER_NAME is not set (expected e.g. facturation.cfpss.ma)"

# ── 1. Basic auth ─────────────────────────────────────────────────────────────
# Deliberately fails rather than falling back to a default: a guessable password
# on a public demo is worse than a container that refuses to start.
#
# The htpasswd file is still built unconditionally even when every vhost is
# open, because `auth_basic_user_file` is present in each of them regardless and
# nginx wants the file to exist. It costs nothing and it means flipping any of
# the SAFM_*_BASIC_AUTH switches to true takes effect on restart with no other
# change.
if [ -z "$AUTH_USER" ] || [ -z "$AUTH_PASSWORD" ]; then
    die "SAFM_AUTH_USER and SAFM_AUTH_PASSWORD must both be set. Put them in .env — see .env.prod.example"
fi

# -B bcrypt, -b take the password as an argument, -c create.
htpasswd -Bbc "$HTPASSWD" "$AUTH_USER" "$AUTH_PASSWORD" >/dev/null 2>&1 \
    || die "failed to write $HTPASSWD"

# This script runs as root, but the workers that actually read the file run as
# the nginx user. Owned root:root and mode 640 they get EACCES, and nginx turns
# that into a 500 on every request rather than an auth prompt.
chown root:nginx "$HTPASSWD"
chmod 640 "$HTPASSWD"
log "basic auth enabled for user '${AUTH_USER}'"

# `auth_basic <value>;` — the value carries its own quoting so the same token
# can render either a realm or the literal `off`.
AUTH_REALM_DIRECTIVE="\"${AUTH_REALM}\""

if [ "$TENANT_BASIC_AUTH" = "true" ]; then
    TENANT_AUTH="$AUTH_REALM_DIRECTIVE"
    log "basic auth is ON for tenant subdomains (SAFM_TENANT_BASIC_AUTH=true)"
else
    TENANT_AUTH="off"
    log "basic auth is OFF for tenant subdomains — customers reach their own host directly"
fi

# The apex. It serves the PUBLIC LANDING PAGE — what SAFM is, the plans, and the
# "request a demo" form — so a password prompt in front of it would make the page
# unreachable by exactly the people it is written for. Open by default.
#
# The same switch decides whether crawlers are invited: a public apex is meant to
# be found, a gated one keeps the noindex every other vhost sends. This is the
# ONLY vhost whose robots policy is conditional — the operator console and the
# tenant ERPs must never be indexed, so their header stays hard-coded in the
# template.
#
# Set SAFM_APEX_BASIC_AUTH=true to put the gate back: a staging domain, or a
# production host where the landing page is not ready to be seen yet.
if [ "$APEX_BASIC_AUTH" = "true" ]; then
    APEX_AUTH="$AUTH_REALM_DIRECTIVE"
    APEX_ROBOTS='add_header X-Robots-Tag "noindex, nofollow" always;'
    log "basic auth is ON for the apex (SAFM_APEX_BASIC_AUTH=true) — landing page gated, noindex"
else
    APEX_AUTH="off"
    APEX_ROBOTS='add_header X-Robots-Tag "index, follow" always;'
    log "apex is PUBLIC — the landing page is open and indexable"
fi

if [ "$PUBLIC_STORAGE" = "true" ]; then
    APEX_STORAGE_AUTH="off"
else
    APEX_STORAGE_AUTH="$AUTH_REALM_DIRECTIVE"
fi

# The operator console. Public by default: it has its own login, its own guard,
# per-ability authorisation and a 5/minute rate limit on the login form, so the
# shared basic-auth password added nothing except an extra secret to distribute.
# Set SAFM_ADMIN_BASIC_AUTH=true to put the second lock back.
if [ "${SAFM_ADMIN_BASIC_AUTH:-false}" = "true" ]; then
    ADMIN_AUTH="$AUTH_REALM_DIRECTIVE"
    log "basic auth is ON for the operator console (SAFM_ADMIN_BASIC_AUTH=true)"
else
    ADMIN_AUTH="off"
    log "operator console is PUBLIC — protected by its own login and rate limiting"
fi

# ── 2. Shared proxy snippet ───────────────────────────────────────────────────
# Included from every `location` that proxies. Written here rather than shipped
# as a file so that the four vhosts in two templates can never drift apart.
# proxy_pass itself stays in the templates — it is the only per-block part.
cat > "$PROXY_SNIPPET" <<'PROXY'
proxy_http_version 1.1;

# $host, not a literal: this is what carries the tenant subdomain through to
# Laravel, where ResolveTenant reads it off the request and swaps the database.
# Hard-coding a Host here would make every tenant resolve to the same schema.
proxy_set_header Host              $host;
proxy_set_header X-Real-IP         $remote_addr;
proxy_set_header X-Forwarded-For   $proxy_add_x_forwarded_for;
# The app's Apache vhost turns this into HTTPS=on, which is what makes Laravel
# generate https:// URLs without any application source change.
proxy_set_header X-Forwarded-Proto $scheme;
proxy_set_header X-Forwarded-Host  $host;
proxy_set_header X-Forwarded-Port  $server_port;

# Reports and CSV exports can be slow on a cold cache.
proxy_connect_timeout 10s;
proxy_send_timeout    120s;
proxy_read_timeout    120s;

proxy_buffering off;
proxy_redirect off;
PROXY

# ── 3. Certificates ───────────────────────────────────────────────────────────
ensure_placeholder() {
    SELF_CERT="${SELF_DIR}/selfsigned.crt"
    SELF_KEY="${SELF_DIR}/selfsigned.key"

    # Regenerate when the file is missing OR when it predates the wildcard SAN.
    # An upgraded deployment reuses the nginx_ssl volume, and the placeholder an
    # older bootstrap wrote carries only a CN. Browsers ignore CN entirely, so
    # that stale cert turns the intended soft "untrusted issuer" warning into a
    # hard ERR_CERT_COMMON_NAME_INVALID on the admin console and on every tenant
    # awaiting issuance.
    if [ -s "$SELF_CERT" ] && ! openssl x509 -in "$SELF_CERT" -noout -ext subjectAltName 2>/dev/null | grep -q "DNS:\*\.${ROOT_DOMAIN}"; then
        log "placeholder certificate lacks the *.${ROOT_DOMAIN} SAN — regenerating"
        rm -f "$SELF_CERT" "$SELF_KEY"
    fi

    if [ ! -s "$SELF_CERT" ] || [ ! -s "$SELF_KEY" ]; then
        log "generating the self-signed placeholder certificate"
        mkdir -p "$SELF_DIR"
        # The SAN covers the wildcard as well as the apex, so the fallback vhost
        # that serves brand-new tenants presents a name-matching (if untrusted)
        # certificate rather than a second, louder error.
        openssl req -x509 -nodes -newkey rsa:2048 -days 3650 \
            -keyout "$SELF_KEY" -out "$SELF_CERT" \
            -subj "/CN=${ROOT_DOMAIN}" \
            -addext "subjectAltName=DNS:${ROOT_DOMAIN},DNS:*.${ROOT_DOMAIN}" \
            >/dev/null 2>&1 \
            || die "openssl failed to generate the placeholder certificate"
        chmod 600 "$SELF_KEY"
    fi
}

ensure_placeholder

# Echoes "<fullchain> <privkey>" for $1, falling back to the placeholder.
cert_pair_for() {
    _dir="${LE_ROOT}/$1"
    if [ -s "${_dir}/fullchain.pem" ] && [ -s "${_dir}/privkey.pem" ]; then
        printf '%s %s' "${_dir}/fullchain.pem" "${_dir}/privkey.pem"
    else
        printf '%s %s' "$SELF_CERT" "$SELF_KEY"
    fi
}

# shellcheck disable=SC2046
set -- $(cert_pair_for "$SERVER_NAME")
SSL_CERT="$1"; SSL_KEY="$2"

set -- $(cert_pair_for "$ADMIN_SERVER_NAME")
ADMIN_SSL_CERT="$1"; ADMIN_SSL_KEY="$2"

[ "$SSL_CERT" = "$SELF_CERT" ] \
    && log "no Let's Encrypt certificate for ${SERVER_NAME} yet — using the placeholder" \
    || log "using the Let's Encrypt certificate for ${SERVER_NAME}"

[ "$ADMIN_SSL_CERT" = "$SELF_CERT" ] \
    && log "no Let's Encrypt certificate for ${ADMIN_SERVER_NAME} yet — using the placeholder" \
    || log "using the Let's Encrypt certificate for ${ADMIN_SERVER_NAME}"

# ── HSTS, but only where a TRUSTED certificate is actually being served ───────
# Strict-Transport-Security is a one-way door: once a browser has seen it, it
# refuses plain http AND refuses to let the user click through a certificate
# warning for max-age seconds. Sending it from a host still on the self-signed
# placeholder would therefore lock that host out of every browser that had
# already visited it — a self-inflicted outage with no quick undo.
#
# So each host gets the header only once it has a real certificate. Tenant
# vhosts are rendered exclusively for issued certificates, so they always get it.
#
# includeSubDomains is deliberately OMITTED. It would apply to every tenant
# subdomain, including ones still waiting on issuance, and turn their browser
# warning into a hard failure.
HSTS_MAX_AGE="${SAFM_HSTS_MAX_AGE:-31536000}"
hsts_for() {
    if [ "$1" = "$SELF_CERT" ]; then
        printf '# HSTS withheld: still serving the self-signed placeholder'
    else
        printf 'add_header Strict-Transport-Security "max-age=%s" always;' "$HSTS_MAX_AGE"
    fi
}
APEX_HSTS="$(hsts_for "$SSL_CERT")"
ADMIN_HSTS="$(hsts_for "$ADMIN_SSL_CERT")"

if [ "$SSL_CERT" = "$SELF_CERT" ] || [ "$ADMIN_SSL_CERT" = "$SELF_CERT" ]; then
    log "-------------------------------------------------------------------"
    log "  Serving a SELF-SIGNED certificate - browsers will show a warning."
    log "  Issue the real ones (note --entrypoint), then restart this container:"
    log ""
    log "    docker compose -f docker-compose.yml -f docker-compose.prod.yml \\"
    log "      run --rm --entrypoint certbot certbot certonly \\"
    log "        --webroot -w /var/www/certbot \\"
    log "        --cert-name ${SERVER_NAME} -d ${SERVER_NAME} \\"
    log "        --email you@cfpss.ma --agree-tos --no-eff-email"
    log ""
    log "    docker compose -f docker-compose.yml -f docker-compose.prod.yml \\"
    log "      run --rm --entrypoint certbot certbot certonly \\"
    log "        --webroot -w /var/www/certbot \\"
    log "        --cert-name ${ADMIN_SERVER_NAME} -d ${ADMIN_SERVER_NAME} \\"
    log "        --email you@cfpss.ma --agree-tos --no-eff-email"
    log ""
    log "    docker compose -f docker-compose.yml -f docker-compose.prod.yml \\"
    log "      restart nginx"
    log ""
    log "  Tenant subdomains are issued automatically by the certbot service;"
    log "  see docker/certbot/issue-tenant-cert.sh."
    log "-------------------------------------------------------------------"
fi

# ── 4. Render the main config ─────────────────────────────────────────────────
# sed with __TOKEN__ placeholders rather than envsubst, so nginx's own $variables
# ($host, $remote_addr, $proxy_add_x_forwarded_for, ...) survive untouched.
sed \
    -e "s|__SERVER_NAME__|${SERVER_NAME}|g" \
    -e "s|__ADMIN_SERVER_NAME__|${ADMIN_SERVER_NAME}|g" \
    -e "s|__ROOT_DOMAIN__|${ROOT_DOMAIN}|g" \
    -e "s|__UPSTREAM__|${UPSTREAM}|g" \
    -e "s|__SSL_CERT__|${SSL_CERT}|g" \
    -e "s|__SSL_KEY__|${SSL_KEY}|g" \
    -e "s|__ADMIN_SSL_CERT__|${ADMIN_SSL_CERT}|g" \
    -e "s|__ADMIN_SSL_KEY__|${ADMIN_SSL_KEY}|g" \
    -e "s|__FALLBACK_SSL_CERT__|${SELF_CERT}|g" \
    -e "s|__FALLBACK_SSL_KEY__|${SELF_KEY}|g" \
    -e "s|__APEX_AUTH__|${APEX_AUTH}|g" \
    -e "s|__ADMIN_AUTH__|${ADMIN_AUTH}|g" \
    -e "s|__TENANT_AUTH__|${TENANT_AUTH}|g" \
    -e "s|__APEX_STORAGE_AUTH__|${APEX_STORAGE_AUTH}|g" \
    -e "s|__APEX_ROBOTS__|${APEX_ROBOTS}|g" \
    -e "s|__APEX_HSTS__|${APEX_HSTS}|g" \
    -e "s|__ADMIN_HSTS__|${ADMIN_HSTS}|g" \
    /etc/nginx/safm.conf.template > /etc/nginx/conf.d/default.conf

log "serving ${SERVER_NAME}, ${ADMIN_SERVER_NAME} and *.${ROOT_DOMAIN} -> ${UPSTREAM}"

# ── 5. One vhost per issued tenant certificate ────────────────────────────────
# ssl_certificate cannot be a variable, so SNI needs a server block per name.
# The source of truth is the filesystem: whatever certbot has put under
# /etc/letsencrypt/live/ that looks like <something>.<root_domain> and is neither
# the apex nor the admin host gets a block. Names outside the root domain and
# names with no usable key pair are ignored.
#
# --cert-name <host> in issue-tenant-cert.sh is what guarantees the directory is
# named after the host rather than <host>-0001, which is what makes deriving
# server_name from basename() correct.
render_tenant_vhosts() {
    _rendered=0

    rm -f /etc/nginx/conf.d/tenant-*.conf

    [ -d "$LE_ROOT" ] || return 0

    for _dir in "$LE_ROOT"/*/; do
        [ -d "$_dir" ] || continue

        _host="$(basename "$_dir")"

        [ -s "${_dir}fullchain.pem" ] || continue
        [ -s "${_dir}privkey.pem" ]   || continue

        case "$_host" in
            "$SERVER_NAME"|"$ADMIN_SERVER_NAME") continue ;;
            *".${ROOT_DOMAIN}") ;;
            *) continue ;;
        esac

        # Refuse anything that is not a plain hostname before it reaches a
        # filename or a server_name directive.
        case "$_host" in
            *[!a-zA-Z0-9.-]*) continue ;;
        esac

        sed \
            -e "s|__TENANT_HOST__|${_host}|g" \
            -e "s|__TENANT_SSL_CERT__|${_dir}fullchain.pem|g" \
            -e "s|__TENANT_SSL_KEY__|${_dir}privkey.pem|g" \
            -e "s|__UPSTREAM__|${UPSTREAM}|g" \
            -e "s|__TENANT_AUTH__|${TENANT_AUTH}|g" \
            "$TENANT_TEMPLATE" > "/etc/nginx/conf.d/tenant-${_host}.conf"

        _rendered=$(( _rendered + 1 ))
    done

    log "rendered ${_rendered} tenant vhost(s) from ${LE_ROOT}"
    return 0
}

render_tenant_vhosts

# ── 6. Pick up new and renewed certificates ───────────────────────────────────
# certbot writes into /etc/letsencrypt from its own container and cannot signal
# this one. Poll a cheap fingerprint of the live certificates; when it changes,
# re-render the tenant vhosts and reload. This covers BOTH a freshly issued
# tenant certificate (new directory) and a renewal (same directory, new bytes).
#
# The unconditional 12h reload is kept as a belt-and-braces backstop for the
# case where a certificate is replaced with an identically-hashed file.
cert_fingerprint() {
    find "$LE_ROOT" -maxdepth 2 -name fullchain.pem -exec md5sum {} \; 2>/dev/null | sort
}

(
    _seen="$(cert_fingerprint)"
    _elapsed=0

    while :; do
        sleep "$WATCH_INTERVAL"
        _elapsed=$(( _elapsed + WATCH_INTERVAL ))

        _current="$(cert_fingerprint)"

        if [ "$_current" != "$_seen" ]; then
            _seen="$_current"
            log "certificate change detected — re-rendering tenant vhosts"
            render_tenant_vhosts
            if nginx -t >/dev/null 2>&1; then
                nginx -s reload 2>/dev/null || true
            else
                printf '\033[0;31m[nginx] refusing to reload: the regenerated configuration does not pass nginx -t\033[0m\n' >&2
                nginx -t || true
            fi
        elif [ "$_elapsed" -ge 43200 ]; then
            _elapsed=0
            nginx -s reload 2>/dev/null || true
        fi
    done
) &
