# syntax=docker/dockerfile:1
#
# Public edge for the SAFM demo: terminates TLS for facturation.cfpss.ma,
# enforces HTTP basic auth, and reverse-proxies to the Apache app container.

FROM nginx:1.27-alpine

# openssl      — self-signed bootstrap certificate, so nginx can start and serve
#                the ACME challenge before a real certificate exists
# apache2-utils — htpasswd, to build the basic-auth file from env vars at boot
RUN apk add --no-cache openssl apache2-utils

COPY conf/safm.conf.template /etc/nginx/safm.conf.template

# Executable, so the stock nginx entrypoint runs it as a subprocess rather than
# sourcing it — otherwise `set -e` here would abort the entrypoint itself.
COPY --chmod=0755 bootstrap.sh /docker-entrypoint.d/10-safm-bootstrap.sh
