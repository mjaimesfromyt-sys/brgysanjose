@echo off
REM Hostinger Deployment Script for Laravel Barangay App (Windows version for local prep)
REM Run this locally to prepare, then upload files OR run the .sh version on Hostinger SSH

echo 🚀 Preparing for Hostinger deployment...

echo 📦 Installing Composer dependencies...
composer install --optimize-autoloader --no-dev

echo 🎨 Building frontend assets...
npm install
npm run build

echo 🧹 Clearing local caches...
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo ✅ Local preparation complete!
echo.
echo 📋 Files ready for upload. Next steps:
echo 1. Upload project to Hostinger (git clone or FTP)
echo 2. Run deploy-hostinger.sh on Hostinger via SSH
echo 3. Configure .env on server
echo 4. Set Document Root to 'public' in hPanel
pause