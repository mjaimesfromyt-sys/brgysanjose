# Hostinger Deployment Checklist - Barangay San Jose App

## Pre-Deployment (Local)
- [ ] Run `deploy-prep.bat` to build assets and clear caches
- [ ] Verify `public/build/manifest.json` exists
- [ ] Commit and push all changes to git repository

## On Hostinger (via SSH)

### 1. Initial Setup
- [ ] Enable SSH Access in Hostinger hPanel → Advanced → SSH Access
- [ ] Connect via SSH: `ssh u123456789@yourdomain.com`
- [ ] Navigate to domain folder: `cd domains/yourdomain.com`

### 2. Clone & Deploy
- [ ] `git clone <your-repo-url> public_html`
- [ ] `cd public_html`
- [ ] Run `bash deploy-hostinger.sh` (uploads the .sh file first via SFTP)

### 3. Configure Environment
- [ ] Copy `.env.production.template` to `.env`
- [ ] Fill in ALL values:
  - [ ] `APP_KEY` (run `php artisan key:generate`)
  - [ ] `APP_URL` (your actual domain with https)
  - [ ] `DB_*` credentials (create MySQL DB in hPanel first)
  - [ ] `MAIL_*` (SMTP settings - Hostinger email or external)
  - [ ] `PAYMONGO_*` keys (live keys for production)
- [ ] Run `php artisan config:cache` after editing .env

### 4. Web Server Config
- [ ] In hPanel → Website → Domain → **Document Root**: Set to `public_html/public`
- [ ] Verify `public/.htaccess` exists (Laravel includes it)

### 5. Permissions
- [ ] `chmod -R 775 storage bootstrap/cache`
- [ ] If needed: `chown -R u123456789:u123456789 storage bootstrap/cache`

### 6. Background Services
- [ ] **Cron Job** (hPanel → Advanced → Cron Jobs):
  ```
  * * * * * cd /home/u123456789/domains/yourdomain.com/public_html && php artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] **Queue Worker** (hPanel → Advanced → Process Manager):
  - Command: `php artisan queue:work --sleep=3 --tries=3 --timeout=90`
  - Or use Supervisor if available

### 7. SSL & Security
- [ ] Enable SSL in hPanel (Let's Encrypt - free)
- [ ] Force HTTPS in `.htaccess` or via hPanel

### 8. Testing
- [ ] Visit your domain - should load without errors
- [ ] Test login/register
- [ ] Test document requests, bookings, rentals
- [ ] Test PayMongo payment flow (use test keys first)
- [ ] Check email notifications work
- [ ] Run `php artisan queue:failed` to check for failed jobs

## Post-Launch
- [ ] Monitor logs: `tail -f storage/logs/laravel.log`
- [ ] Set up uptime monitoring
- [ ] Configure backups (Hostinger auto-backups + DB exports)

## Useful Commands on Server
```bash
# View logs
tail -f storage/logs/laravel.log

# Clear all caches
php artisan optimize:clear

# Rebuild caches after .env changes
php artisan config:cache && php artisan route:cache && php artisan view:cache

# Run migrations
php artisan migrate --force

# Process failed jobs
php artisan queue:retry all

# Check scheduled tasks
php artisan schedule:list
```

## Support Files Created
- `deploy-hostinger.sh` - Run on Hostinger SSH
- `deploy-prep.bat` - Run locally before upload
- `.env.production.template` - Reference for production .env