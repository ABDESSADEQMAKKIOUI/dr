#!/bin/bash
#
# Runs once, from /docker-entrypoint-initdb.d/, when the MySQL data directory is
# first initialised.
#
# The mysql image creates MYSQL_USER with privileges on MYSQL_DATABASE only. In
# SaaS mode that is not enough: the application also has to reach safm_platform
# and one schema per customer (safm_acme, safm_globex, …), none of which exist
# yet at this point.
#
# The obvious fix — run the app as root — is what this exists to avoid. Root in
# the application means any SQL injection across 83 models becomes server-wide
# compromise. Instead the app user gets ALL PRIVILEGES on the safm_ NAMESPACE:
#
#     `safm\_%`     backslash-escaped, so _ is a literal underscore and only %
#                   is a wildcard. Matches safm_platform and safm_<anything>.
#                   Does NOT match the mysql, sys or performance_schema
#                   databases, and is not *.* — so the app user still cannot
#                   CREATE DATABASE, and cannot read mysql.user.
#
# CREATE DATABASE remains root's job, reached only through the dedicated
# 'platform_admin' connection used by App\Services\Tenancy\TenantDatabaseManager.

set -euo pipefail

: "${MYSQL_USER:?}"
: "${MYSQL_ROOT_PASSWORD:?}"

echo "[safm-init] granting ${MYSQL_USER} ALL PRIVILEGES ON \`safm\\_%\`.*"

mysql --protocol=socket -uroot -p"${MYSQL_ROOT_PASSWORD}" <<-SQL
	GRANT ALL PRIVILEGES ON \`safm\_%\`.* TO '${MYSQL_USER}'@'%';
	FLUSH PRIVILEGES;
SQL

echo "[safm-init] done"
