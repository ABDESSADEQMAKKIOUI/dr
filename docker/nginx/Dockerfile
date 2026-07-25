# syntax=docker/dockerfile:1
#
# Public edge for the SAFM SaaS platform. Terminates TLS for three host
# patterns — the apex, admin.<root> (the operator console) and *.<root> (one
# server block per tenant, each with its own certificate) — enforces HTTP basic
# auth on the first two, and reverse-proxies everything to the Apache app
# container.

FROM nginx:1.27-alpine

# openssl      — self-signed bootstrap certificate, so nginx can start and serve
#                the ACME challenge before a real certificate exists. It also
#                stays in use permanently as the wildcard fallback for tenants
#                whose certificate has not been issued yet.
# apache2-utils — htpasswd, to build the basic-auth file from env vars at boot
RUN apk add --no-cache openssl apache2-utils

COPY conf/safm.conf.template   /etc/nginx/safm.conf.template
COPY conf/tenant.conf.template /etc/nginx/tenant.conf.template

# Executable, so the stock nginx entrypoint runs it as a subprocess rather than
# sourcing it — otherwise `set -e` here would abort the entrypoint itself.
COPY --chmod=0755 bootstrap.sh /docker-entrypoint.d/10-safm-bootstrap.sh
