#!/bin/bash
#
# Runs once, from /docker-entrypoint-initdb.d/, when the MySQL data directory is
# first initialised.
#
# THREE IDENTITIES, and the separation between them is the last line of defence
# if application-layer tenancy is ever bypassed. Application code decides which
# schema a request reads; MySQL decides what is reachable at all.
#
#   1. ${MYSQL_USER} ("safm")   the ERP.
#        DML only, on `safm\_%`.* — every tenant schema, nothing else.
#        NO CREATE / DROP / ALTER: schema changes go through root (see 3).
#        CANNOT see the control plane at all: safmctl_platform is outside the
#        `safm\_%` pattern, so an SQL injection anywhere in the 83-model ERP
#        cannot read operator password hashes or rewrite subscriptions.
#
#   2. ${PLATFORM_DB_USER} ("safmctl")   the operator console.
#        DML only, on `safmctl\_%`.* — the control plane, nothing else.
#        Cannot read or write a single customer's business data.
#
#   3. root                     provisioning only.
#        Reached exclusively through the 'platform_admin' and 'tenant_admin'
#        connections, for CREATE DATABASE / DROP DATABASE and for running
#        migrations. Never used to serve a request.
#
# An earlier version of this file granted the ERP user ALL PRIVILEGES on
# `safm\_%`.* and claimed that stopped it creating databases. That was wrong on
# both counts: the pattern also matched the control plane (safm_platform), and
# database-level CREATE within a matching pattern does permit CREATE DATABASE.
# The ERP user could read operator hashes and drop any tenant. Hence this split.

set -euo pipefail

: "${MYSQL_USER:?}"
: "${MYSQL_ROOT_PASSWORD:?}"

PLATFORM_DB_USER="${PLATFORM_DB_USERNAME:-safmctl}"
PLATFORM_DB_PASS="${PLATFORM_DB_PASSWORD:?PLATFORM_DB_PASSWORD is required}"

# Everything the ERP legitimately does at runtime. CREATE TEMPORARY TABLES is
# needed by some report queries; EXECUTE and SHOW VIEW keep stored routines and
# views working. Deliberately absent: CREATE, DROP, ALTER, INDEX, REFERENCES,
# GRANT OPTION.
DML="SELECT, INSERT, UPDATE, DELETE, EXECUTE, SHOW VIEW, CREATE TEMPORARY TABLES, LOCK TABLES"

echo "[safm-init] ERP user '${MYSQL_USER}': ${DML} on \`safm\\_%\`.*"
echo "[safm-init] control-plane user '${PLATFORM_DB_USER}': ${DML} on \`safmctl\\_%\`.*"

mysql --protocol=socket -uroot -p"${MYSQL_ROOT_PASSWORD}" <<-SQL
	-- The bootstrap grant the image created gave ALL PRIVILEGES on the single
	-- MYSQL_DATABASE. Nothing in SaaS mode uses that schema, and leaving DDL
	-- lying around undermines the split above.
	REVOKE ALL PRIVILEGES ON \`${MYSQL_DATABASE}\`.* FROM '${MYSQL_USER}'@'%';
	GRANT ${DML} ON \`${MYSQL_DATABASE}\`.* TO '${MYSQL_USER}'@'%';

	-- 1. The ERP: every tenant schema, DML only.
	GRANT ${DML} ON \`safm\_%\`.* TO '${MYSQL_USER}'@'%';

	-- 2. The operator console: the control plane, DML only.
	CREATE USER IF NOT EXISTS '${PLATFORM_DB_USER}'@'%' IDENTIFIED BY '${PLATFORM_DB_PASS}';
	GRANT ${DML} ON \`safmctl\_%\`.* TO '${PLATFORM_DB_USER}'@'%';

	FLUSH PRIVILEGES;
SQL

echo "[safm-init] done"
