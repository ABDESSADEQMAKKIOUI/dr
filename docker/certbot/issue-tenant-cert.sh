#!/bin/sh
#
# Per-tenant HTTP-01 certificate issuance for the SAFM SaaS edge.
#
# ONE script, TWO modes, decided by whether a `certbot` binary is on PATH:
#
#   ISSUE MODE   (certbot present — the `certbot` compose service, or a manual
#                 `docker compose run`): talks to Let's Encrypt directly.
#   REQUEST MODE (no certbot — the `app` container, where TenantProvisioner
#                 step 14 calls this script): drops a request file into the
#                 shared volume and returns immediately. The certbot service
#                 drains it. The app container has no ACME client, no port 80
#                 and no /etc/letsencrypt, so it cannot possibly issue anything
#                 itself.
#
# USAGE
#   issue-tenant-cert.sh <slug|host> [<slug|host> ...]
#   issue-tenant-cert.sh --watch      # certbot service main loop: drain + renew
#   issue-tenant-cert.sh --drain      # process pending requests once and exit
#
# A bare slug ("acme") is expanded to "acme.$ROOT_DOMAIN". A value that already
# contains a dot is used verbatim.
#
# EXIT CODES — the caller MUST treat every non-zero code as non-fatal.
# TenantProvisioner step 14 runs AFTER the tenant is live; a tenant with no
# certificate is still reachable over the wildcard fallback vhost (self-signed,
# browser warning) and the operator can retry issuance from the panel.
#   0  certificate present (issued now, or already on disk)
#   2  request queued for the certbot service
#   3  skipped on purpose (issuance disabled, no request channel, or the
#      per-host attempt guard tripped)
#   1  issuance was attempted and failed
#  64  usage error
#
# RATE LIMITS ARE THE REASON THIS SCRIPT IS SO DEFENSIVE.
# Let's Encrypt allows 50 certificates per registered domain per week and 5
# duplicate certificates per week. facturation.cfpss.ma is ONE registered
# domain, so every tenant subdomain draws from the same 50/week bucket. A retry
# loop would burn the whole week's budget in minutes and lock issuance out for
# every tenant, existing ones included. Therefore:
#   - an existing certificate short-circuits before certbot is even executed;
#   - every attempt is counted in a state file and capped (SAFM_CERT_MAX_ATTEMPTS);
#   - attempts are spaced by SAFM_CERT_RETRY_COOLDOWN;
#   - a failed request is moved to <host>.failed and is NEVER retried
#     automatically — clearing it is a deliberate operator action;
#   - nothing in here loops over a single host.

set -eu

# ── Configuration ─────────────────────────────────────────────────────────────
ROOT_DOMAIN="${TENANCY_ROOT_DOMAIN:-${SAFM_ROOT_DOMAIN:-${SAFM_SERVER_NAME:-}}}"
ISSUANCE="${SAFM_CERT_ISSUANCE:-auto}"
REQUEST_DIR="${SAFM_CERT_REQUEST_DIR:-/var/www/certbot-requests}"
WEBROOT="${SAFM_CERT_WEBROOT:-/var/www/certbot}"
CONFIG_DIR="${SAFM_CERT_CONFIG_DIR:-/etc/letsencrypt}"
STATE_DIR="${CONFIG_DIR}/safm-state"
EMAIL="${SAFM_CERT_EMAIL:-${CERTBOT_EMAIL:-}}"
MAX_ATTEMPTS="${SAFM_CERT_MAX_ATTEMPTS:-3}"
RETRY_COOLDOWN="${SAFM_CERT_RETRY_COOLDOWN:-21600}"   # 6h
DRAIN_INTERVAL="${SAFM_CERT_DRAIN_INTERVAL:-60}"
RENEW_INTERVAL="${SAFM_CERT_RENEW_INTERVAL:-43200}"   # 12h, as before
STAGING="${SAFM_CERT_STAGING:-false}"

log()  { printf '\033[0;36m[certbot]\033[0m %s\n' "$*"; }
warn() { printf '\033[0;33m[certbot] %s\033[0m\n' "$*" >&2; }

now() { date +%s; }

# ── Slug -> fully qualified host ──────────────────────────────────────────────
resolve_host() {
    _value="$1"

    case "$_value" in
        *.*) printf '%s' "$_value"; return 0 ;;
    esac

    if [ -z "$ROOT_DOMAIN" ]; then
        warn "cannot expand the slug '${_value}': set TENANCY_ROOT_DOMAIN (or SAFM_SERVER_NAME)"
        return 1
    fi

    printf '%s.%s' "$_value" "$ROOT_DOMAIN"
}

# A hostname, not an arbitrary string: this value ends up in a filename, in an
# nginx server_name and on a certbot command line.
host_is_sane() {
    case "$1" in
        *[!a-zA-Z0-9.-]*) return 1 ;;
        .*|-*|*.|*-)      return 1 ;;
        *.*)              return 0 ;;
        *)                return 1 ;;
    esac
}

certificate_exists() {
    [ -s "${CONFIG_DIR}/live/$1/fullchain.pem" ] && [ -s "${CONFIG_DIR}/live/$1/privkey.pem" ]
}

# ── Attempt guard ─────────────────────────────────────────────────────────────
# "<count> <epoch of last attempt>" in ${STATE_DIR}/<host>.attempts. Removed on
# success. Kept on failure so a crash-looping provisioner cannot hammer the ACME
# API: once MAX_ATTEMPTS is reached the host is frozen until an operator deletes
# the file.
attempt_guard_allows() {
    _host="$1"
    _file="${STATE_DIR}/${_host}.attempts"

    [ -f "$_file" ] || return 0

    _count=0
    _last=0
    read -r _count _last < "$_file" 2>/dev/null || true
    [ -n "$_count" ] || _count=0
    [ -n "$_last" ] || _last=0

    if [ "$_count" -ge "$MAX_ATTEMPTS" ]; then
        warn "${_host}: ${_count} failed issuance attempts already recorded — refusing to call Let's Encrypt again."
        warn "${_host}: fix the cause (DNS A record? port 80 reachable?) then remove ${_file} to re-enable."
        return 1
    fi

    _elapsed=$(( $(now) - _last ))
    if [ "$_elapsed" -lt "$RETRY_COOLDOWN" ]; then
        warn "${_host}: last attempt was ${_elapsed}s ago, cooldown is ${RETRY_COOLDOWN}s — skipping."
        return 1
    fi

    return 0
}

record_attempt() {
    _host="$1"
    _file="${STATE_DIR}/${_host}.attempts"

    mkdir -p "$STATE_DIR"

    _count=0
    if [ -f "$_file" ]; then
        read -r _count _ < "$_file" 2>/dev/null || _count=0
        [ -n "$_count" ] || _count=0
    fi

    printf '%s %s\n' "$(( _count + 1 ))" "$(now)" > "$_file"
}

clear_attempts() {
    rm -f "${STATE_DIR}/$1.attempts"
}

# ── ISSUE MODE ────────────────────────────────────────────────────────────────
issue_certificate() {
    _host="$1"

    if certificate_exists "$_host"; then
        log "${_host}: certificate already on disk — nothing to do"
        clear_attempts "$_host"
        return 0
    fi

    if [ -z "$EMAIL" ]; then
        warn "${_host}: SAFM_CERT_EMAIL is not set — cannot register with Let's Encrypt"
        return 3
    fi

    attempt_guard_allows "$_host" || return 3

    mkdir -p "$WEBROOT"
    record_attempt "$_host"

    set -- certonly \
        --webroot -w "$WEBROOT" \
        --config-dir "$CONFIG_DIR" \
        --cert-name "$_host" \
        -d "$_host" \
        --email "$EMAIL" \
        --agree-tos \
        --no-eff-email \
        --non-interactive \
        --keep-until-expiring \
        --quiet

    # --cert-name pins the directory to /etc/letsencrypt/live/<host>/ instead of
    # letting certbot invent <host>-0001 on a re-issue. bootstrap.sh derives the
    # nginx server_name from that directory name, so the two MUST agree.
    #
    # --keep-until-expiring makes certbot itself a no-op when a valid cert is
    # already there; the disk check above is the cheap fast path, this is the
    # backstop for the -0001 case.
    if [ "$STAGING" = "true" ]; then
        set -- "$@" --staging
    fi

    log "${_host}: requesting an HTTP-01 certificate from Let's Encrypt"
    if certbot "$@"; then
        log "${_host}: certificate issued"
        clear_attempts "$_host"
        return 0
    fi

    warn "${_host}: certbot failed. The tenant stays reachable over the wildcard"
    warn "${_host}: fallback vhost (self-signed). Retry from the operator panel."
    return 1
}

# ── REQUEST MODE ──────────────────────────────────────────────────────────────
queue_certificate() {
    _host="$1"

    if [ ! -d "$REQUEST_DIR" ]; then
        warn "${_host}: ${REQUEST_DIR} does not exist — TLS issuance is not wired up in this deployment"
        return 3
    fi

    if [ ! -w "$REQUEST_DIR" ]; then
        warn "${_host}: ${REQUEST_DIR} is not writable by $(id -un) — cannot queue the certificate request"
        return 3
    fi

    if [ -f "${REQUEST_DIR}/${_host}.done" ]; then
        log "${_host}: certificate already issued"
        return 0
    fi

    if [ -f "${REQUEST_DIR}/${_host}.request" ]; then
        log "${_host}: a request is already queued — not queueing a second one"
        return 2
    fi

    if [ -f "${REQUEST_DIR}/${_host}.failed" ]; then
        warn "${_host}: a previous issuance failed and was not cleared."
        warn "${_host}: remove ${REQUEST_DIR}/${_host}.failed to allow another attempt."
        return 3
    fi

    printf '%s\n' "$_host" > "${REQUEST_DIR}/${_host}.request"
    log "${_host}: certificate request queued"
    return 2
}

# ── One host, whichever mode applies ──────────────────────────────────────────
process_host() {
    _host="$1"

    if [ "$ISSUANCE" = "off" ]; then
        log "${_host}: SAFM_CERT_ISSUANCE=off — skipping certificate issuance"
        return 3
    fi

    if command -v certbot >/dev/null 2>&1; then
        issue_certificate "$_host"
    else
        queue_certificate "$_host"
    fi
}

# ── Drain the request queue (ISSUE MODE only) ─────────────────────────────────
# Exactly one pass. A request either succeeds (-> .done) or fails (-> .failed)
# and is never picked up again. That is the whole rate-limit defence: the queue
# can only ever shrink.
drain_requests() {
    [ -d "$REQUEST_DIR" ] || return 0

    for _req in "$REQUEST_DIR"/*.request; do
        [ -f "$_req" ] || continue

        _host="$(basename "$_req" .request)"

        if ! host_is_sane "$_host"; then
            warn "ignoring a request with an implausible hostname: ${_host}"
            mv -f "$_req" "${REQUEST_DIR}/${_host}.failed" 2>/dev/null || rm -f "$_req"
            continue
        fi

        _rc=0
        issue_certificate "$_host" || _rc=$?

        if [ "$_rc" -eq 0 ]; then
            mv -f "$_req" "${REQUEST_DIR}/${_host}.done"
        else
            mv -f "$_req" "${REQUEST_DIR}/${_host}.failed"
        fi
    done

    return 0
}

# ── The certbot service main loop ─────────────────────────────────────────────
# Replaces the old inline `while :; do certbot renew; sleep 12h; done`. Renewal
# cadence is unchanged (RENEW_INTERVAL, 12h, first pass immediately on start);
# the queue is drained far more often because a new tenant is waiting on it.
watch_loop() {
    _last_renew=0

    # Both are named volumes shared with nginx and the app. They normally exist
    # already; creating them keeps `certbot renew` from erroring out on the very
    # first pass of a brand new stack.
    mkdir -p "$WEBROOT" "$REQUEST_DIR"

    log "watching ${REQUEST_DIR} every ${DRAIN_INTERVAL}s, renewing every ${RENEW_INTERVAL}s"

    while :; do
        drain_requests || true

        _now="$(now)"
        if [ "$(( _now - _last_renew ))" -ge "$RENEW_INTERVAL" ]; then
            certbot renew --webroot -w "$WEBROOT" --config-dir "$CONFIG_DIR" --quiet || true
            _last_renew="$_now"
        fi

        sleep "$DRAIN_INTERVAL" & wait $! || true
    done
}

# ── Entry point ───────────────────────────────────────────────────────────────
main() {
    if [ "$#" -eq 0 ]; then
        warn "usage: issue-tenant-cert.sh <slug|host> [...] | --watch | --drain"
        exit 64
    fi

    case "$1" in
        --watch)
            command -v certbot >/dev/null 2>&1 \
                || { warn "--watch needs the certbot binary; run this inside the certbot container"; exit 64; }
            trap 'exit 0' INT TERM
            watch_loop
            ;;
        --drain)
            command -v certbot >/dev/null 2>&1 \
                || { warn "--drain needs the certbot binary; run this inside the certbot container"; exit 64; }
            drain_requests
            ;;
        -*)
            warn "unknown option: $1"
            exit 64
            ;;
        *)
            _worst=0
            for _arg in "$@"; do
                _host="$(resolve_host "$_arg")" || { _worst=1; continue; }

                if ! host_is_sane "$_host"; then
                    warn "refusing to act on an implausible hostname: ${_host}"
                    _worst=1
                    continue
                fi

                _rc=0
                process_host "$_host" || _rc=$?
                # 1 (a real failure) outranks 2/3 (queued / skipped).
                if [ "$_rc" -eq 1 ]; then
                    _worst=1
                elif [ "$_rc" -ne 0 ] && [ "$_worst" -eq 0 ]; then
                    _worst="$_rc"
                fi
            done
            exit "$_worst"
            ;;
    esac
}

main "$@"
