# Deploying the demo to facturation.cfpss.ma

Target: an Ubuntu VPS whose DNS A record for `facturation.cfpss.ma` already
points at it.

Result: nginx on 80/443 with a Let's Encrypt certificate, the public SAFM
landing page on the apex, the operator console on `admin.<root>`, and one ERP
per tenant on `<slug>.<root>`.

None of those three is behind HTTP basic auth by default. Each has its own
switch — see [Basic auth at the edge](#basic-auth-at-the-edge) — and the shared
credentials exist so you can flip one on, not because anything needs them today.

Every command below runs **on the VPS**.

---

## 0. Before you start

Ports 80 and 443 must be free and reachable from the internet — Let's Encrypt
validates over port 80.

```bash
sudo ss -lntp | grep -E ':(80|443)\s'
```

If Apache or an existing nginx is already bound there, stop it first
(`sudo systemctl disable --now apache2` / `nginx`), otherwise the stack cannot
bind and certbot cannot validate.

Confirm the DNS actually resolves to this machine:

```bash
dig +short facturation.cfpss.ma
curl -s ifconfig.me
```

Those two must print the same address.

---

## 1. Install Docker

```bash
sudo apt-get update
sudo apt-get install -y ca-certificates curl git
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
  | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

Let your user run docker without sudo (log out and back in afterwards):

```bash
sudo usermod -aG docker "$USER"
```

---

## 2. Get the code

```bash
git clone -b docker-demo https://github.com/ABDESSADEQMAKKIOUI/dr.git /opt/safm
cd /opt/safm
```

---

## 3. Configure

```bash
cp .env.prod.example .env
```

Edit `.env` and set, at minimum:

| Key | Notes |
|---|---|
| `SAFM_SERVER_NAME` | `facturation.cfpss.ma` — already correct in the example |
| `SAFM_AUTH_USER` | Whoever should get in |
| `SAFM_AUTH_PASSWORD` | **Long and random.** nginx refuses to start if empty |
| `DB_PASSWORD` | Change from the demo default |
| `DB_ROOT_PASSWORD` | Change from the demo default |

Generate strong values on the server so they never travel anywhere:

```bash
openssl rand -base64 24    # run once per secret
```

`.env` is gitignored and stays on the server.

### Basic auth at the edge

Three independent switches, all `false` by default — nothing is gated out of the
box. `SAFM_AUTH_USER` / `SAFM_AUTH_PASSWORD` are still mandatory: nginx refuses
to start without them, so that turning any switch on is a one-line change that
takes effect on the next restart.

| Key | Default | Gates | Turn it on when |
|---|---|---|---|
| `SAFM_APEX_BASIC_AUTH` | `false` | the apex, `facturation.cfpss.ma` | staging, or production before the landing page is ready to be seen |
| `SAFM_ADMIN_BASIC_AUTH` | `false` | the operator console, `admin.<root>` | you want a second lock in front of a console that already has its own login, per-ability authorisation and a 5/minute login throttle |
| `SAFM_TENANT_BASIC_AUTH` | `false` | every tenant, `*.<root>` | staging only — **never** in production. A paying customer must not be handed a shared password to reach their own ERP |

`SAFM_APEX_BASIC_AUTH` matters most, because the apex is the **public landing
page**: what SAFM is, the plans read from the `plans` table, and the "request a
demo" form that files a lead straight into the operator console. A password
prompt in front of it hides the page from exactly the people it was written for.

That switch also drives the apex's `X-Robots-Tag`:

| `SAFM_APEX_BASIC_AUTH` | Apex | `X-Robots-Tag` on the apex |
|---|---|---|
| `false` (default) | open to anyone | `index, follow` — search engines welcome |
| `true` | credential prompt | `noindex, nofollow` |

`admin.<root>` and the tenant subdomains send `noindex, nofollow`
**unconditionally**, whatever these are set to. Neither is ever meant to be
crawled, and no switch can change that.

> Turning `SAFM_APEX_BASIC_AUTH` or `SAFM_ADMIN_BASIC_AUTH` **on** also needs the
> value forwarded to the nginx container. `docker-compose.prod.yml` passes an
> explicit list of `SAFM_*` variables to that service; add
> `SAFM_APEX_BASIC_AUTH: ${SAFM_APEX_BASIC_AUTH:-false}` under the nginx
> service's `environment:` before relying on it. Leaving both at the default
> needs no change — `bootstrap.sh` already defaults them to `false`.

---

## 4. First start

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

The first run builds the image, then runs ~102 migrations and two seeders.
Give it 3–5 minutes. Follow it:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f app
```

Wait for `provisioning complete`. nginx will not start until the app container
reports healthy, which is deliberate.

At this point the site is live on a **self-signed** certificate — browsers will
warn. That is expected; it exists so nginx can boot and answer the ACME
challenge in the next step.

---

## 5. Issue the real certificate

nginx must already be up. Note `--entrypoint`, which is what stops this from
inheriting the renewal loop:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml \
  run --rm --entrypoint certbot certbot certonly \
    --webroot -w /var/www/certbot \
    -d facturation.cfpss.ma \
    --email you@cfpss.ma --agree-tos --no-eff-email
```

Then restart nginx so it picks up the new path:

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml restart nginx
```

Verify:

```bash
# 200 — the public landing page. A 401 here means SAFM_APEX_BASIC_AUTH is on.
curl -sI https://facturation.cfpss.ma/ | head -1

# index, follow — the apex is the one host that invites crawlers.
curl -sI https://facturation.cfpss.ma/ | grep -i x-robots-tag

# noindex, nofollow — unconditionally, on the console and on every tenant.
curl -sI https://admin.facturation.cfpss.ma/ | grep -i x-robots-tag

echo | openssl s_client -connect facturation.cfpss.ma:443 -servername facturation.cfpss.ma 2>/dev/null \
  | openssl x509 -noout -issuer -dates
```

The issuer should say Let's Encrypt. Renewal is automatic — the `certbot`
service checks twice a day and nginx reloads on its own timer.

> If issuance fails, it is almost always port 80 not being reachable from the
> internet. Check the firewall (`sudo ufw allow 80,443/tcp`) and that the A
> record resolves to this host.

---

## 6. Log in

https://facturation.cfpss.ma opens the **landing page**. No prompt, no login —
that is the point. It is what a prospect sees, and the demo form at the bottom
files a lead into the operator console.

The things you actually log into:

| URL | What it is |
|---|---|
| `https://admin.facturation.cfpss.ma/login` | operator console — the account from `PLATFORM_ADMIN_EMAIL`, or the password printed once in the app container log |
| `https://<slug>.facturation.cfpss.ma` | a provisioned tenant's ERP — `admin@admin.com` / `password` on a demo-seeded tenant |

If the apex prompts for a password instead of showing the landing page, you have
`SAFM_APEX_BASIC_AUTH=true`.

**Change that seeded tenant password immediately** — Settings → My Profile. It
is a default published in this repository.

---

## Operating it

```bash
cd /opt/safm
C="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

$C ps                     # status
$C logs -f app            # application logs
$C logs -f nginx          # access + TLS logs
$C restart app            # restart the app only
$C down                   # stop everything (keeps data)
$C up -d --build          # after a git pull
```

Reset the demo data back to a clean state (**destroys the database and
uploads**):

```bash
$C down -v && $C up -d
```

Change the basic-auth password, or flip any of the three
`SAFM_*_BASIC_AUTH` switches: edit `.env`, then
`$C up -d --force-recreate nginx`. `bootstrap.sh` re-renders every vhost on boot
and logs one line per host telling you which way each landed:

```bash
$C logs nginx | grep -E 'apex|operator console|tenant subdomains'
```

---

## Before you show it to anyone

The apex is now open to the internet and asks search engines to index it, so
this list matters more than it used to. Worth doing first:

- [ ] Open `https://facturation.cfpss.ma` in a private window and read it as a stranger would — it is the first thing a prospect and a crawler will see
- [ ] Submit the demo form once and confirm the lead lands in the operator console
- [ ] If the landing page is not ready to be public yet, set `SAFM_APEX_BASIC_AUTH=true` — that restores both the credential prompt and `noindex`
- [ ] Change the seeded `admin@admin.com` password on every demo-seeded tenant
- [ ] Confirm `DB_PASSWORD` and `DB_ROOT_PASSWORD` are not the example defaults
- [ ] `sudo ufw allow 22,80,443/tcp && sudo ufw enable` — nothing else needs to be open. The app container binds only to `127.0.0.1:8088` and the database is not published at all
- [ ] Read the "Known broken areas" table in `DOCKER.md` and avoid those screens: everything under `/api/*` returns 500, as do ~26 web routes. Password reset does not work (no `password_reset_tokens` table)
- [ ] Delete `run_setup.php` and `public/run_setup.php` from the repo on `main`. They are excluded from this image, but on `main` they are an unauthenticated endpoint that prints the Laravel log and shells out
