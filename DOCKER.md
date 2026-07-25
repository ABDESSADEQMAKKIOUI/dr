# Running the SAFM demo with Docker

A one-command demo of the SAFM ERP: MySQL + Apache/PHP 8.4, migrated, seeded with
French demo data, and past the install wizard before the first request lands.

```bash
docker compose up -d --build
```

Then open **http://localhost:8088/login**

| Field | Value |
|---|---|
| Email | `admin@admin.com` |
| Password | `password` |

First boot takes 2–4 minutes (composer install during the build, then ~102
migrations and two seeders). Watch it with `docker compose logs -f app` — the
entrypoint prints `provisioning complete` when the app is ready.

---

## What you get

Two containers, nothing else:

| Service | Image | Purpose |
|---|---|---|
| `app` | built from `Dockerfile` | Apache 2.4 + PHP 8.4 (mod_php), serving `public/` |
| `db` | `mysql:8.0` | utf8mb4, named volume `db_data` |

Seeded content: 15 products (one low-stock, one out-of-stock), 7 customers,
5 suppliers, 8 purchases, 15 sales, 13 invoices and 14 expenses, all dated
relative to *now* so the dashboard charts have something to draw.

---

## Common tasks

Change the published port:

```bash
APP_PORT=9000 docker compose up -d
```

Follow the logs:

```bash
docker compose logs -f app
```

Open a shell / run artisan:

```bash
docker compose exec app php artisan about
```

Reset the demo to a pristine state (drops the database *and* uploaded files):

```bash
docker compose down -v && docker compose up -d
```

Start with an empty instance — roles, permissions, settings and the admin
account, but no demo catalogue:

```bash
DEMO_SEED=false docker compose up -d --force-recreate app
```

---

## Configuration

Everything is driven from the `app` service's `environment:` block in
`docker-compose.yml`. The entrypoint rewrites `/var/www/html/.env` from those
variables on every boot, so **edit compose, not `.env`** — a hand-edited `.env`
inside the container is overwritten on restart.

| Variable | Default | Notes |
|---|---|---|
| `APP_PORT` | `8088` | Host port. 8080 is left alone because it is commonly taken. |
| `APP_KEY` | *(generated)* | Generated on first boot and persisted to `storage/app/.appkey` on the volume, so sessions survive a recreate. Pin it in compose if you prefer. |
| `APP_DEBUG` | `false` | Set to `true` to skip config/view caching and show stack traces. |
| `DEMO_SEED` | `true` | `false` skips `DemoDataSeeder`. |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | `safm` / `safm` / `secret` | Demo credentials. Change both services together. |
| `APP_URL` | `http://localhost:$APP_PORT` | Must match the origin you actually browse to — see below. |

Showing the demo to someone else on the network? `APP_URL` is baked into the
cached config and used to build `Storage::url()`, so set it to the origin they
will use:

```bash
APP_URL=http://192.168.1.20:8088 docker compose up -d --force-recreate app
```

Page assets use `asset()` and follow the request host either way; it is expense
attachments and employee photos that would otherwise point at `localhost`.

---

## Why the setup looks the way it does

These are not stylistic choices — each one works around something specific in
this codebase.

**PHP 8.4, not 8.2.** `composer.json` says `">=8.2"` and the install wizard
checks for 8.2, but both are stale: `composer.lock` pins
`"platform": {"php": ">=8.4"}` and several non-dev Symfony v8 packages
hard-require 8.4. `composer install` fails outright on 8.2 or 8.3.

**MySQL, not SQLite** — even though `config/database.php` defaults to `sqlite`.
The app is structurally MySQL-shaped:
`2026_04_14_210400_add_counted_data_to_inventory_counts.php` issues
`ALTER TABLE ... MODIFY COLUMN ... ENUM(...)`, `DemoDataSeeder` uses
`SET FOREIGN_KEY_CHECKS` and `TRUNCATE`, and `DashboardService` uses
`DATE_FORMAT()`. `DB_CONNECTION=mysql` is set explicitly for exactly this reason.

**`SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`.** The
config defaults and `.env.example` all say `database`, but this repository has
**no migration** for `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches` or
`failed_jobs`. Leaving the defaults makes the very first request fatal with
*"Base table or view not found: sessions"*. There is also no
`password_reset_tokens` table, so password reset cannot work regardless — keep
`/forgot-password` out of any demo script.

**`FILESYSTEM_DISK=public`.** Uploads are written to the `public` disk
explicitly, but several views call `Storage::url()` on the *default* disk. They
only agree when the default is `public`.

**No npm build.** This is a laravel-mix (webpack) project and the compiled
output — `public/css/app.css`, `public/js/app.js`, `public/js/zxing.js` and
`public/mix-manifest.json` — is committed. Every `mix()` key referenced in a
Blade layout resolves against the committed manifest, so Node is not installed at
all. (Rebuilding is actually riskier: `postcss.config.js` and
`tailwind.config.js` use `export default` while `package.json` has no
`"type": "module"`.)

**The install wizard is bypassed by a marker file.** `CheckInstalled` middleware
redirects every non-`/install` request until `storage/app/.installed` exists —
there is no env var for it. The entrypoint writes that file **last**, after
migrate and seed have succeeded, because the reverse order has its own failure
mode: with the marker present but the `languages` table unseeded,
`LocaleMiddleware` → `LanguageService::getDefault()` calls `firstOrFail()` and
*every* page 404s.

**`config:cache` and `view:cache` but never `route:cache`.** `routes/web.php`
registers `Route::get('docs', fn() => view(...))`, and route caching aborts on a
Closure route. Config caching is safe here — the app makes zero `env()` calls
outside `config/`.

**Seeding is guarded by a database check, not just the marker.** The seeders are
not idempotent (`Setting::create()` on a unique key, `User::create()` on a unique
email), so a second run against a populated database throws. The entrypoint
counts rows in `users` instead, which stays correct if you wipe the db volume but
keep the storage volume.

**`mysqldump` is told not to use TLS.** On Debian, `default-mysql-client` is
MariaDB's client — `/usr/bin/mysqldump` is a symlink to `mariadb-dump`. MariaDB
11.x negotiates TLS by default and verifies the certificate chain, while
`mysql:8.0` auto-generates a self-signed CA, so the connection is rejected with
*"TLS/SSL error: self-signed certificate in certificate chain"* before any data
is written. `BackupService` runs `exec()` and never checks the exit code, so the
app would zip a 0-byte dump and report success. The Dockerfile drops
`[client] ssl=0` into `/etc/mysql/conf.d/`, which fixes it with no source change.

**The healthcheck probes `/login`, not `/up`.** Laravel registers its `/up`
health route *outside* the `web` group, so it carries no middleware: it never
touches `CheckInstalled`, `LocaleMiddleware`, the session or the database, and
returns 200 from a static Blade file even with the database stopped. `/login`
goes through the real stack. The probe also requires `storage/app/.installed` and
passes `--max-redirs 0`, so a container still redirecting everything to
`/install` reports unhealthy instead of green.

**Seeding is gated per phase, not by one table.** `users` alone would be the
wrong signal — `AdminUserSeeder` is 4th of 6, so an interruption right after it
would latch the gate to "seeded" with the demo dataset still missing, and
`restart: unless-stopped` would make that permanent. The base gate checks
`users`, `settings`, `languages`, `units` and `sms_templates`; the demo gate
checks `products`, `customers`, `sales` and `invoices`. Gating them separately is
also what makes `DEMO_SEED` reversible — flipping it back to `true` on an
existing instance actually seeds.

**Three files are deleted from the image.** `run_setup.php` and
`public/run_setup.php` sit under the document root, so they bypass Laravel
routing and the `CheckInstalled` gate entirely; unauthenticated, they print the
last 30 lines of `storage/logs/laravel.log` and `shell_exec()` `artisan migrate`.
`error_log` / `public/error_log` leak stack traces and absolute paths from the
previous live host. `composer.phar` is 3.3 MB of dead weight. They are excluded
via `.dockerignore` and removed again in the Dockerfile. **They are still present
in the git tree** — worth deleting there too.

---

## Known broken areas (pre-existing, not caused by Docker)

These are defects in the application itself. They are listed so a demo can route
around them; fixing them was out of scope for the Dockerization.

| Area | Symptom | Cause |
|---|---|---|
| Everything under `/api/*` | HTTP 500 | `routes/api.php` uses `auth:sanctum`, but `laravel/sanctum` is in neither `require` nor `require-dev`, and `config/auth.php` defines no `sanctum` guard. |
| `/tenants`, `/api/saas/*` | HTTP 500 | `App\Models\Tenant` does not exist; the `role` and `tenant.scope` middleware aliases are never registered. |
| Sales list totals show `0.00 DH` | Wrong data on screen | `resources/views/sales/index.blade.php:171` reads `$sale->total` / `$sale->paid`; the columns are `total_amount` / `paid_amount` and `Sale` has no accessor. The stored values are correct — invoices, which read the right columns, display fine. |
| `/reports/sales-by-payment-method` | HTTP 500 | Queries a `payment_method` column that no migration creates. |
| `/reports/expenses-by-warehouse` | HTTP 500 | Queries `expenses.warehouse_id`, which no migration creates. |
| `/combos` | HTTP 500 | Queries `combo_products.deleted_at`; the table has no soft-delete column. |
| `/crm/*/create` | HTTP 500 | Views reference `crm.*.store` routes that are never registered. |
| `/system/error-logs`, `/system/modules`, `/stock/opening-import` | HTTP 500 | Their controllers build `$breadcrumbs` entries without a `url` key; `layouts/app.blade.php:42` requires it. |
| `/products/export`, `/customers/export`, `/suppliers/export` | HTTP 404 | `Route::resource(...)` registers `{model}` before the literal `export` route, so `export` is captured as an ID. |
| `/roles/{id}`, `/stock/warehouses/{id}` | HTTP 500 | `roles.show` / `stock.warehouses.show` views do not exist. |
| `/products/brands/{id}`, `/products/categories/{id}`, `/products/units/{id}`, `/payments/{id}/edit` | HTTP 500 | `Route::resource` registers actions the controllers do not implement. |
| `/register`, `/forgot-password` | Broken | `register` writes `name` / `username`, which are not columns on `users`; password reset has no `password_reset_tokens` table. |
| `/system/cache/stats` | HTTP 500 | `CacheService` calls `Redis::keys()`; there is no Redis in the stack and no redis client installed. |
| `/settings/backup/download` with no `filename` | HTTP 500 | `SettingController::downloadBackup()` resolves `realpath($dir.'/'.null)` to the backups **directory**, which passes its own containment guard, then hands a directory to `response()->download()`. It needs an `is_file()` check. Only reachable by hand or by a route crawler — the UI always passes a filename. |
| Backups (if the exit code ever matters again) | Silent failure | `BackupService.php:59` calls `exec()` without checking the exit status, which is why the empty-dump problem was invisible. The Docker image works around the cause, but the missing check is worth fixing. |

The core demo journey — login, dashboard, products, customers, sales, invoices,
purchases, expenses, stock alerts and reports — works.

One cosmetic note: the committed CSS bundle predates
`resources/views/stock/inventory/index.blade.php`, so `xl:grid-cols-6` is missing
from it and that one KPI row renders 3-up instead of 6-up above 1280px. No error.
