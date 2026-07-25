#!/bin/sh
#
# Runs from /docker-entrypoint.d/ before nginx starts.
#
#   1. builds the basic-auth file from the environment
#   2. picks a certificate — the real Let's Encrypt one if it exists, otherwise a
#      self-signed placeholder so nginx can boot and answer the ACME challenge
#   3. renders conf.d/default.conf from the template
#   4. backgrounds a reload loop so renewed certificates get picked up

set -eu

SERVER_NAME="${SAFM_SERVER_NAME:-}"
UPSTREAM="${SAFM_UPSTREAM:-app:80}"
AUTH_USER="${SAFM_AUTH_USER:-}"
AUTH_PASSWORD="${SAFM_AUTH_PASSWORD:-}"
AUTH_REALM="${SAFM_AUTH_REALM:-SAFM Demo}"

LE_DIR="/etc/letsencrypt/live/${SERVER_NAME}"
SELF_DIR="/etc/nginx/ssl"
HTPASSWD="/etc/nginx/safm.htpasswd"

log()  { printf '\033[0;36m[nginx]\033[0m %s\n' "$*"; }
die()  { printf '\033[0;31m[nginx] %s\033[0m\n' "$*" >&2; exit 1; }

[ -n "$SERVER_NAME" ] || die "SAFM_SERVER_NAME is not set (expected e.g. facturation.cfpss.ma)"

# ── 1. Basic auth ─────────────────────────────────────────────────────────────
# Deliberately fails rather than falling back to a default: a guessable password
# on a public demo is worse than a container that refuses to start.
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

# ── 2. Certificate ────────────────────────────────────────────────────────────
if [ -s "${LE_DIR}/fullchain.pem" ] && [ -s "${LE_DIR}/privkey.pem" ]; then
    SSL_CERT="${LE_DIR}/fullchain.pem"
    SSL_KEY="${LE_DIR}/privkey.pem"
    log "using the Let's Encrypt certificate for ${SERVER_NAME}"
else
    SSL_CERT="${SELF_DIR}/selfsigned.crt"
    SSL_KEY="${SELF_DIR}/selfsigned.key"

    if [ ! -s "$SSL_CERT" ]; then
        log "no certificate yet — generating a self-signed placeholder"
        mkdir -p "$SELF_DIR"
        openssl req -x509 -nodes -newkey rsa:2048 -days 3650 \
            -keyout "$SSL_KEY" -out "$SSL_CERT" \
            -subj "/CN=${SERVER_NAME}" >/dev/null 2>&1 \
            || die "openssl failed to generate the placeholder certificate"
        chmod 600 "$SSL_KEY"
    fi

    log "-------------------------------------------------------------------"
    log "  Serving a SELF-SIGNED certificate - browsers will show a warning."
    log "  Issue the real one (note --entrypoint), then restart this container:"
    log ""
    log "    docker compose -f docker-compose.yml -f docker-compose.prod.yml \\"
    log "      run --rm --entrypoint certbot certbot certonly \\"
    log "        --webroot -w /var/www/certbot -d ${SERVER_NAME} \\"
    log "        --email you@cfpss.ma --agree-tos --no-eff-email"
    log ""
    log "    docker compose -f docker-compose.yml -f docker-compose.prod.yml \\"
    log "      restart nginx"
    log "-------------------------------------------------------------------"
fi

# ── 3. Render the config ──────────────────────────────────────────────────────
# sed with __TOKEN__ placeholders rather than envsubst, so nginx's own $variables
# ($host, $remote_addr, $proxy_add_x_forwarded_for, ...) survive untouched.
sed \
    -e "s|__SERVER_NAME__|${SERVER_NAME}|g" \
    -e "s|__UPSTREAM__|${UPSTREAM}|g" \
    -e "s|__SSL_CERT__|${SSL_CERT}|g" \
    -e "s|__SSL_KEY__|${SSL_KEY}|g" \
    -e "s|__AUTH_REALM__|${AUTH_REALM}|g" \
    /etc/nginx/safm.conf.template > /etc/nginx/conf.d/default.conf

log "serving ${SERVER_NAME} -> ${UPSTREAM}"

# ── 4. Pick up renewed certificates ───────────────────────────────────────────
# certbot renews into /etc/letsencrypt in its own container and cannot signal
# this one, so reload on a timer. A reload is cheap and drops no connections.
( while :; do sleep 12h; nginx -s reload 2>/dev/null || true; done ) &
