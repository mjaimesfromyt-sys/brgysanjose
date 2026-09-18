#!/bin/bash
# Hostinger Deployment Script for Laravel Barangay App
# Run this on Hostinger via SSH after cloning your repository

set -e  # Exit on error

echo "🚀 Starting deployment..."

# 1. Install PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

# 2. Install & build frontend assets
echo "🎨 Building frontend assets..."
npm install
npm run build

# 3. Environment setup
if [ ! -f .env ]; then
    echo "⚙️  Creating .env from example..."
    cp .env.example .env
    php artisan key:generate
    echo "✏️  IMPORTANT: Edit .env with your production settings before continuing!"
    echo "   Required: APP_URL, DB_*, MAIL_*, PAYMONGO_*"
    read -p "Press Enter after editing .env to continue..."
fi

# 4. Database & optimization
echo "🗄️  Running migrations..."
php artisan migrate --force

echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# 5. Permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache
# Note: chown may need sudo or specific user - adjust for your Hostinger setup
# chown -R www-data:www-data storage bootstrap/cache

# 6. Clear any cached config from local dev
php artisan config:clear
php artisan cache:clear

echo ""
echo "✅ Deployment complete!"
echo ""
echo "📋 Post-deployment checklist:"
echo "  1. Set Document Root to 'public' in Hostinger hPanel"
echo "  2. Add cron job: * * * * * cd $(pwd) && php artisan schedule:run"
echo "  3. Setup queue worker via Process Manager (php artisan queue:work)"
echo "  4. Test your site at your domain"
echo "  5. Run 'php artisan queue:failed' to check for any failed jobs"