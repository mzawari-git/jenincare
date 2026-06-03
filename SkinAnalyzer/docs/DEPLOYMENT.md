# SkinAnalyzer Deployment Guide

## Overview

This document covers the complete deployment pipeline for the Jenin Care SkinAnalyzer platform across all environments: Laravel backend on Hostinger, Android APK to Google Play, and the Vue.js admin panel.

---

## 1. Hostinger Laravel Setup

### Prerequisites

- Hostinger shared/business hosting with SSH access
- PHP 8.2 or higher
- MySQL 8.0 database
- SSL certificate (Let's Encrypt or custom)

### PHP Configuration

Ensure these PHP extensions are enabled in Hostinger's PHP selector:

```
mbstring, dom, fileinfo, mysql, zip, bcmath, gd, exif, curl, openssl, pdo, pdo_mysql, tokenizer, xml, ctype, json
```

Recommended `php.ini` overrides:

```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 20M
post_max_size = 20M
max_input_vars = 3000
```

### MySQL Database Setup

1. Create a new MySQL database and user via Hostinger control panel
2. Note the database name, username, password, and host

### File Deployment

The `.github/workflows/laravel-deploy.yml` workflow handles automated deployment:

1. Tests pass on CI
2. Production Composer dependencies installed
3. Archive created (excluding dev/test files)
4. Deployed via SFTP using `lftp`

For manual deployment:

```bash
# Clone repository on server or upload files
cd /path/to/hosting/public_html

# Install dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Set up environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force

# Create storage symlink
php artisan storage:link

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Cron Jobs

Add these cron entries in Hostinger's cron manager:

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Queue Worker

If using database queue driver, configure a supervisor or cron-based worker:

```
* * * * * cd /path/to/project && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

---

## 2. Environment Configuration

Copy `backend/.env.example` to `.env` and configure:

### Required Settings

```env
APP_NAME="Jenin Care — SkinAnalyzer"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://jenincare.shop

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<your_database>
DB_USERNAME=<your_username>
DB_PASSWORD=<your_password>

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=database

SANCTUM_STATEFUL_DOMAINS=jenincare.shop
SESSION_DOMAIN=.jenincare.shop
```

### AI Provider Configuration

```env
# Yimei AI (Structured Engine)
YIMEI_API_KEY=<your_key>
YIMEI_API_URL=https://api.yimei.ai/v1
YIMEI_MODEL=skin-analysis-v3

# OpenAI (Generative Engine)
OPENAI_API_KEY=<your_key>
OPENAI_API_URL=https://api.openai.com/v1
OPENAI_MODEL=gpt-4-vision-preview

# Anthropic Claude (Generative Engine)
CLAUDE_API_KEY=<your_key>
CLAUDE_API_URL=https://api.anthropic.com/v1
CLAUDE_MODEL=claude-3-opus-20240229

# Google Gemini (Hybrid Engine)
GEMINI_API_KEY=<your_key>
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta
GEMINI_MODEL=gemini-pro-vision

# Haut.AI (Structured Engine)
HAUTAI_API_KEY=<your_key>
HAUTAI_API_URL=https://api.haut.ai/v1

# Perfect Corp (Hybrid Engine)
PERFECTCORP_API_KEY=<your_key>
PERFECTCORP_API_SECRET=<your_secret>
PERFECTCORP_API_URL=https://api.perfectcorp.com/v2

# Skinive (Structured Engine)
SKINIVE_API_KEY=<your_key>
SKINIVE_API_URL=https://api.skinive.com/v1

# White-label Configuration
SKIN_ANALYZER_WHITE_LABEL_NAME="Jenin Care"
SKIN_ANALYZER_PRIMARY_COLOR=#7C3AED
SKIN_ANALYZER_LOGO_URL=https://jenincare.shop/images/logo.png
SKIN_ANALYZER_SUPPORT_EMAIL=support@jenincare.shop
SKIN_ANALYZER_WEBSITE_URL=https://jenincare.shop
```

---

## 3. Database Migration Steps

All migrations are timestamped for orderly execution:

```bash
# Check migration status
php artisan migrate:status

# Run all pending migrations
php artisan migrate --force

# In production, never use migrate:refresh or migrate:fresh

# If rolling back is needed (be careful)
php artisan migrate:rollback --step=1
```

### Migration Order

| Migration | Table | Purpose |
|-----------|-------|---------|
| 000001 | `ai_providers` | AI engine registry |
| 000002 | `skin_analyses` | Core analysis storage |
| 000003 | `skin_analysis_pins` | Unlock PIN codes |
| 000004 | `skin_analysis_products` | Product recommendations |
| 000000 | `products` | E-commerce product catalog |

---

## 4. Admin User Seeding

The `DatabaseSeeder` creates the default admin account:

```bash
php artisan db:seed --class=DatabaseSeeder --force
```

**Default Admin Credentials:**
- Email: `admin@jenincare.shop`
- Password: Change immediately after first login

**To change admin password after deployment:**

```bash
php artisan tinker

> $admin = \App\Models\User::where('email', 'admin@jenincare.shop')->first();
> $admin->password = \Illuminate\Support\Facades\Hash::make('new_secure_password');
> $admin->save();
```

**To create additional admin users:**

```bash
php artisan tinker

> \App\Models\User::create([
    'name' => 'Admin Name',
    'email' => 'admin2@jenincare.shop',
    'password' => \Illuminate\Support\Facades\Hash::make('password'),
    'is_admin' => true,
]);
```

### AI Provider Seeding

```bash
php artisan db:seed --class=AIProviderSeeder --force
```

This creates all 7 providers — only the Native Engine is active by default.

---

## 5. Android APK Signing and Google Play Publishing

### Keystore Generation

Generate a release keystore (do this once, keep it secure):

```bash
keytool -genkey -v \
  -keystore jenincare-release.keystore \
  -alias jenincare \
  -keyalg RSA \
  -keysize 2048 \
  -validity 10000 \
  -storepass <secure_password> \
  -keypass <secure_password> \
  -dname "CN=Jenin Care, OU=Engineering, O=Jenin Care, L=Jenin, S=Jenin, C=PS"
```

### GitHub Secrets Configuration

Add these secrets in GitHub repository settings (Settings → Secrets and variables → Actions):

| Secret | Description |
|--------|-------------|
| `KEYSTORE_BASE64` | `base64 -w0 jenincare-release.keystore` |
| `KEYSTORE_PASSWORD` | Keystore password |
| `KEY_ALIAS` | Key alias (e.g., `jenincare`) |
| `KEY_PASSWORD` | Key password |

### Build Process

The CI/CD pipeline (`android-build.yml`) automates:
1. Checkout code
2. Setup JDK 17
3. Lint check
4. Unit tests
5. Build debug APK (all branches)
6. Build release APK (main branch only, signed)

### Google Play Console Setup

1. Create developer account at [play.google.com/console](https://play.google.com/console)
2. Create app with package name `com.jenincare.skinanalyzer`
3. Complete store listing (Arabic primary, English secondary):
   - Short description
   - Full description
   - Screenshots (phone + tablet)
   - Feature graphic
   - Privacy policy URL
   - Content rating questionnaire
4. Upload first release APK from GitHub Artifacts
5. Rollout to internal/alpha track first
6. Test with registered tester accounts
7. Promote to production

### Version Management

```kotlin
// Update in android-app/app/build.gradle.kts
versionCode = 1   // Increment with each release
versionName = "1.0.0"  // Semantic versioning
```

---

## 6. Admin Panel Build and Deployment

### Build

```bash
cd admin-panel

# Install dependencies
npm ci

# Build for production
npm run build
```

Output goes to `admin-panel/dist/`.

### Deploy to Hostinger

Upload the `dist/` contents to a subdomain or directory on Hostinger:

```bash
# Option A: Serve as subdomain (admin.jenincare.shop)
# Upload dist/ contents to admin subdomain root

# Option B: Serve under main domain
# Upload dist/ contents to public_html/admin/

# Ensure .htaccess for SPA routing
```

### .htaccess for SPA Routing

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.html [L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "DENY"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

### Environment Variables for Production

Create `.env` on the server or configure via hosting panel:

```env
VITE_API_BASE_URL=https://jenincare.shop/api/v1
VITE_WS_URL=wss://jenincare.shop
VITE_APP_TITLE=Jenin Care — SkinAnalyzer
VITE_APP_LOCALE=ar
VITE_ENABLE_REALTIME_NOTIFICATIONS=true
VITE_ENABLE_DARK_MODE=true
```

---

## 7. White-Label Configuration

The white-label system allows dynamic branding without app updates.

### Via Admin Panel

Navigate to **Settings → White Label** and configure:

- **Application Name**: Displayed in app and emails
- **Primary Color**: Hex color used for buttons, headers
- **Logo URL**: Full URL to logo image
- **Support Email**: Contact for customer support
- **Support Phone**: Phone number for support
- **Website URL**: Company website link

### API Endpoint

The white-label config is exposed via API and consumed by the Android app on startup:

```
GET /api/v1/config/white-label
```

This ensures app branding updates without Play Store submissions.

---

## 8. SSL Certificate Setup

### Let's Encrypt (Automatic — Hostinger)

Most Hostinger plans include auto-SSL. Enable it via:

1. Hostinger control panel → Websites → Manage
2. SSL → Install → Let's Encrypt
3. Enable auto-renewal

### Custom SSL Certificate

1. Purchase or obtain SSL certificate
2. Hostinger control panel → Websites → SSL
3. Select "Import SSL"
4. Paste certificate, private key, and CA bundle

### Force HTTPS

The Laravel app includes HTTPS enforcement middleware. Verify in `AppServiceProvider.php`:

```php
if (app()->environment('production')) {
    URL::forceScheme('https');
}
```

### SSL Health Check

```bash
# Verify SSL configuration
curl -I https://jenincare.shop
curl -I https://api.jenincare.shop

# Check certificate expiry
echo | openssl s_client -servername jenincare.shop -connect jenincare.shop:443 2>/dev/null | openssl x509 -noout -dates
```

---

## Post-Deployment Checklist

- [ ] `.env` configured with production values
- [ ] `APP_DEBUG=false` confirmed
- [ ] `APP_KEY` generated
- [ ] Database migrations completed
- [ ] Storage symlink created
- [ ] Config/route/view cached
- [ ] Queue worker running
- [ ] Cron scheduler configured
- [ ] SSL certificate active
- [ ] Admin user password changed
- [ ] API keys configured for AI providers
- [ ] Admin panel built and deployed
- [ ] Google Play listing published
- [ ] Privacy policy page live
