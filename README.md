# SAFM — Système de Gestion ERP

**SAFM** is a full-featured ERP panel built with Laravel 11. It covers sales, purchases, invoicing, inventory, accounting, HR, CRM, POS, and more — in English, French, and Arabic (RTL).

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | 8.4 or higher |
| MySQL / MariaDB | 8.0+ / 10.6+ |
| Composer | 2.x |
| Node.js + npm | 18+ |
| Web server | Apache 2.4+ or Nginx |
| PHP Extensions | PDO, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, fileinfo, gd or imagick |

---

## Installation Steps

### 1. Upload the Files

Upload the entire project folder to your server (e.g. `/var/www/safm` or inside your hosting `public_html`).

> **Shared hosting:** place the `public` folder contents inside `public_html` and move everything else one level up.

---

### 2. Create the Database

Log in to phpMyAdmin or your MySQL client and run:

```sql
CREATE DATABASE safm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'safm_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON safm.* TO 'safm_user'@'localhost';
FLUSH PRIVILEGES;
```

---

### 3. Configure the Environment

Copy the example environment file:

```bash
cp .env.example .env
```

Then open `.env` and fill in your values:

```env
APP_NAME="SAFM"
APP_URL=http://yourdomain.com
APP_DEBUG=false

DB_DATABASE=safm
DB_USERNAME=safm_user
DB_PASSWORD=your_strong_password
```

---

### 4. Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

---

### 5. Generate Application Key

```bash
php artisan key:generate
```

---

### 6. Install & Build Frontend Assets

```bash
npm install
npm run build
```

---

### 7. Run Migrations & Seed the Database

> **Skip this step if you are using the web installer** (see step 13 — the wizard runs migrations automatically).

```bash
php artisan migrate --force
php artisan db:seed --force
```

> This creates all tables and inserts default data (roles, permissions, settings, default admin user).

---

### 8. Create Storage Symlink

```bash
php artisan storage:link
```

---

### 9. Set Folder Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

On Windows / cPanel, ensure `storage/` and `bootstrap/cache/` are writable.

---

### 10. Configure the Web Server

**Apache — add to `.htaccess` or VirtualHost:**

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/safm/public

    <Directory /var/www/safm/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx:**

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/safm/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

### 11. Set Up the Scheduler (Cron)

Add this cron job for automated backups, recurring invoices, etc.:

```cron
* * * * * cd /var/www/safm && php artisan schedule:run >> /dev/null 2>&1
```

On cPanel → Cron Jobs → add the line above.

---

### 12. Queue Worker (Optional — recommended for email)

```bash
php artisan queue:work --daemon --tries=3 &
```

For production with Supervisor:

```ini
[program:safm-worker]
command=php /var/www/safm/artisan queue:work --tries=3
autostart=true
autorestart=true
user=www-data
```

---

### 13. Web Installer (Recommended for first install)

Instead of running Artisan commands manually, visit:

```
http://yourdomain.com/install
```

The wizard will guide you through:
1. **Requirements check** — verifies PHP version and extensions
2. **Database** — enter your MySQL credentials; connection is tested before continuing
3. **Migrations** — runs `migrate --force` and `db:seed --force` automatically
4. **Admin account** — set your admin name, email, and password
5. **Company info** — company name, currency, timezone
6. **Done** — redirected to login

> After the wizard completes, a `.installed` marker file is created in `storage/app/`. Visiting `/install` again will redirect to the app.

---

### 14. First Login (manual install — no wizard)

After seeding, log in with the default admin credentials:

| Field | Value |
|---|---|
| Email | `admin@admin.com` |
| Password | `password` |

> **Change the password immediately** after first login via Settings → My Profile.

---

### 14. Post-Install Configuration

Go to **Settings** in the panel and configure:

1. **General** — company name, logo, address, timezone
2. **Invoice** — prefix, number format, color theme, footer text
3. **Currency** — symbol, position (before/after)
4. **Language** — default language (EN / FR / AR)
5. **SMTP** — configure email sending for invoices and notifications
6. **Tax** — set default tax rate and tax number
7. **Backup** — enable automatic daily backup schedule

---

## Updating SAFM

```bash
git pull                          # or upload new files
composer install --no-dev -o
php artisan migrate --force
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Production Optimization

Run these after every deployment:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
```

To clear all caches:

```bash
php artisan optimize:clear
```

---

## Troubleshooting

| Problem | Solution |
|---|---|
| White blank page | Set `APP_DEBUG=true` temporarily, check `storage/logs/laravel.log` |
| 500 error | Check folder permissions on `storage/` and `bootstrap/cache/` |
| Images not showing | Run `php artisan storage:link` |
| Login redirect loop | Clear browser cookies, run `php artisan config:cache` |
| Migrations fail | Check DB credentials in `.env`, ensure DB exists |
| CSS/JS not loading | Run `npm run build`, check `public/build/` exists |

---

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.4)
- **Frontend:** Tailwind CSS + Alpine.js + Chart.js
- **Database:** MySQL 8
- **Queue:** Laravel Database Queue
- **PDF:** Laravel built-in DomPDF
- **Barcode:** picqer/php-barcode-generator

---

## License

SAFM is proprietary software. All rights reserved.
# dr
