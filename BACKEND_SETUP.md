# 🚀 Guide de Setup Backend Laravel

## Prérequis

- PHP 8.2+
- Composer
- MySQL 8.0+ / PostgreSQL
- Node.js 18+ (pour assets si nécessaire)
- Git

---

## 📋 Installation Étape par Étape

### 1. Installation des Dépendances PHP

```bash
cd c:\Users\Asus\Desktop\call\facturation

# Installer les dépendances Composer
composer install
```

### 2. Configuration de l'Environnement

```bash
# Copier le fichier .env
copy .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### 3. Configuration de la Base de Données

Ouvrir `.env` et configurer :

```env
APP_NAME="ERP SaaS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_saas
DB_USERNAME=root
DB_PASSWORD=

# Cache (optionnel - Redis)
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file

# Mail (configurer selon votre SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Créer la Base de Données

**Option A: Ligne de commande MySQL**
```bash
mysql -u root -p
CREATE DATABASE erp_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

**Option B: phpMyAdmin**
- Ouvrir phpMyAdmin
- Créer nouvelle base de données `erp_saas`
- Collation: `utf8mb4_unicode_ci`

### 5. Exécuter les Migrations

```bash
# Exécuter toutes les migrations (créer tables)
php artisan migrate

# Si vous voulez recommencer à zéro
php artisan migrate:fresh
```

### 6. Exécuter les Seeders

```bash
# Executer tous les seeders (rôles, permissions, admin, données)
php artisan db:seed

# OU seeder spécifique
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=SettingsSeeder
php artisan db:seed --class=AdminUserSeeder
```

**Compte Admin par défaut:**
- Email: `admin@example.com`
- Mot de passe: `password`

### 7. Créer le Lien Symbolique Storage

```bash
php artisan storage:link
```

### 8. Lancer le Serveur de Développement

```bash
php artisan serve

# Le backend sera disponible sur: http://localhost:8000
```

---

## 🔐 Configuration Sanctum (API Authentication)

Le projet utilise Laravel Sanctum pour l'authentification API.

```bash
# Publier la configuration Sanctum (si pas déjà fait)
php artisan vendor:publish --provider="Laravel\Sanctum\ServiceProvider"

# Ajouter dans .env
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SESSION_DOMAIN=localhost
```

---

## 📁 Structure des Routes API

Toutes les routes API sont dans le dossier `routes/`:

```
routes/
├── api.php              # Routes API de base
├── dashboard.php        # Routes Dashboard
├── products.php         # Routes Produits
├── stock.php           # Routes Stock
├── purchases.php       # Routes Achats
├── sales.php           # Routes Ventes
├── payments.php        # Routes Paiements
├── expenses.php        # Routes Dépenses
├── reports.php         # Routes Rapports
├── accounting.php      # Routes Comptabilité
├── hrm.php             # Routes RH
├── crm.php             # Routes CRM
├── saas.php            # Routes Multi-tenancy
├── i18n.php            # Routes Multi-langue
├── settings.php        # Routes Paramètres
└── system.php          # Routes Système
```

---

## 🧪 Tester l'API

### Option 1: Postman/Insomnia

**1. Login**
```http
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

**Réponse:**
```json
{
  "user": { ... },
  "token": "1|abc123..."
}
```

**2. Utiliser le token**
```http
GET http://localhost:8000/api/dashboard
Authorization: Bearer 1|abc123...
```

### Option 2: cURL

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin@example.com\",\"password\":\"password\"}"

# Utiliser le token
curl -X GET http://localhost:8000/api/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 🗄️ Gestion de la Base de Données

### Réinitialiser la BDD

```bash
# Supprimer toutes les tables et recréer
php artisan migrate:fresh

# Avec seeders
php artisan migrate:fresh --seed
```

### Créer des Données de Test

```bash
# Option 1: Utiliser les factories dans tinker
php artisan tinker

>>> \App\Models\Product::factory()->count(50)->create();
>>> \App\Models\Customer::factory()->count(30)->create();
```

```bash
# Option 2: Créer un seeder personnalisé
php artisan make:seeder DemoDataSeeder
# Puis éditer database/seeders/DemoDataSeeder.php
# Puis exécuter:
php artisan db:seed --class=DemoDataSeeder
```

---

## 🔧 Commandes Artisan Utiles

```bash
# Voir toutes les routes
php artisan route:list

# Voir routes API seulement
php artisan route:list --path=api

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimiser pour production
php artisan optimize
php artisan config:cache
php artisan route:cache

# Créer un controller
php artisan make:controller ProductController

# Créer un model avec migration
php artisan make:model Product -m

# Créer un seeder
php artisan make:seeder ProductSeeder
```

---

## 🌐 Enregistrer les Routes dans `api.php`

Ajouter dans `routes/api.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'ERP SaaS API',
        'version' => '1.0.0',
        'status' => 'running'
    ]);
});

// Charger toutes les routes de modules
require __DIR__ . '/dashboard.php';
require __DIR__ . '/products.php';
require __DIR__ . '/stock.php';
require __DIR__ . '/purchases.php';
require __DIR__ . '/sales.php';
require __DIR__ . '/payments.php';
require __DIR__ . '/expenses.php';
require __DIR__ . '/reports.php';
require __DIR__ . '/accounting.php';
require __DIR__ . '/hrm.php';
require __DIR__ . '/crm.php';
require __DIR__ . '/saas.php';
require __DIR__ . '/i18n.php';
require __DIR__ . '/settings.php';
require __DIR__ . '/system.php';
```

---

## 🐛 Dépannage

### Erreur: Class not found

```bash
composer dump-autoload
```

### Erreur: Permission denied (storage/logs)

```bash
# Windows (PowerShell en admin)
icacls storage /grant Everyone:F /T
icacls bootstrap/cache /grant Everyone:F /T

# OU créer les dossiers manuellement
mkdir storage\logs
mkdir storage\framework\cache
mkdir storage\framework\sessions
mkdir storage\framework\views
```

### Erreur: SQLSTATE connection refused

- Vérifier que MySQL est démarré
- Vérifier les credentials dans `.env`
- Vérifier le port (3306 par défaut)

### CORS errors depuis le frontend

Installer Laravel CORS:
```bash
# Déjà inclus dans Laravel 11
# Configurer dans config/cors.php
```

---

## 📚 Documentation API

### Générer avec Scribe (Optionnel)

```bash
composer require --dev knuckleswtf/scribe

php artisan vendor:publish --tag=scribe-config

php artisan scribe:generate
```

Documentation sera disponible sur: `http://localhost:8000/docs`

---

## ✅ Checklist de Vérification

- [ ] PHP 8.2+ installé
- [ ] Composer installé
- [ ] MySQL/PostgreSQL démarré
- [ ] `composer install` exécuté
- [ ] `.env` configuré
- [ ] Base de données créée
- [ ] `php artisan key:generate` exécuté
- [ ] `php artisan migrate` exécuté
- [ ] `php artisan db:seed` exécuté
- [ ] `php artisan storage:link` exécuté
- [ ] `php artisan serve` fonctionne
- [ ] Compte admin créé
- [ ] API accessible via Postman

---

## 🚀 Démarrage Rapide (TL;DR)

```bash
# 1. Installation
composer install

# 2. Configuration
copy .env.example .env
php artisan key:generate

# 3. Configurer .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# 4. Base de données
php artisan migrate --seed

# 5. Lancer
php artisan serve
```

**API:** http://localhost:8000/api
**Admin:** admin@example.com / password

---

**Backend Ready ! 🎉**
