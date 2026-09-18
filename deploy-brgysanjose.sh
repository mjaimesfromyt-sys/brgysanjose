#!/bin/bash
# Customized Hostinger Deployment for brgysanjose.site
# Run this on Hostinger via SSH after connecting

set -e

echo "🚀 Deploying Barangay San Jose App to brgysanjose.site"

# 1. Clone repository
echo "📥 Cloning repository..."
cd /home/u370190562/domains/brgysanjose.site
git clone https://github.com/mjaimesfromyt-sys/brgysanjose.git public_html
cd public_html

# 2. Install PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

# 3. Install & build frontend assets
echo "🎨 Building frontend assets..."
npm install
npm run build

# 4. Create production .env
echo "⚙️  Creating production .env..."
cat > .env << 'ENVEOF'
APP_NAME="Barangay San Jose"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://brgysanjose.site

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u370190562_brgysanjose
DB_USERNAME=u370190562_Brgysanjose1
DB_PASSWORD=Brgysanjose1!

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="your@email.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

PAYMONGO_SECRET_KEY=your_live_secret_key
PAYMONGO_PUBLIC_KEY=your_live_public_key
PAYMONGO_TRANSACTION_FEE=20
ENVEOF

# 5. Generate app key
php artisan key:generate

# 6. Run migrations & optimize
echo "🗄️  Running migrations..."
php artisan migrate --force

echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# 7. Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache

# 8. Clear any residual caches
php artisan config:clear
php artisan cache:clear

echo ""
echo "✅ Deployment complete!"
echo ""
echo "📋 IMPORTANT - You MUST edit these in .env:"
echo "  1. MAIL_USERNAME / MAIL_PASSWORD / MAIL_FROM_ADDRESS"
echo "  2. PAYMONGO_SECRET_KEY / PAYMONGO_PUBLIC_KEY (live keys)"
echo ""
echo "Then run: php artisan config:cache"
echo ""
echo "🌐 Configure in hPanel:"
echo "  - Document Root: public_html/public"
echo "  - Cron: * * * * * cd /home/u370190562/domains/brgysanjose.site/public_html && php artisan schedule:run"
echo "  - Process Manager: php artisan queue:work --sleep=3 --tries=3 --timeout=90"