#!/usr/bin/env bash
#
# Run this on the Hostinger server after every hPanel "Deploy" pull.
#
# The hPanel Git integration only performs a git pull -- it does not run
# migrations or rebuild Laravel's caches, so the freshly pulled config/ and
# routes/ would otherwise be ignored in favour of the stale compiled cache.
#
# Usage (SSH):        bash deploy/post-deploy.sh
# Usage (cron job):   /usr/bin/bash /home/uXXXXXXX/public_html/deploy/post-deploy.sh
#
# Override the PHP binary if the account's default CLI version is older than
# the one the app needs:  PHP_BIN=/opt/alt/php83/usr/bin/php bash deploy/post-deploy.sh

set -euo pipefail

PHP_BIN="${PHP_BIN:-php}"

cd "$(dirname "${BASH_SOURCE[0]}")/.."
APP_ROOT="$(pwd)"

echo "==> Deploying from ${APP_ROOT}"

if [ ! -f .env ]; then
    echo "ERROR: no .env found in ${APP_ROOT}." >&2
    echo "Copy .env.hostinger.example to .env and fill it in before deploying." >&2
    exit 1
fi

if [ ! -d vendor ]; then
    echo "ERROR: no vendor/ directory found." >&2
    echo "The deploy branch should ship vendor/ prebuilt. Check that hPanel is" >&2
    echo "tracking the deploy branch and not main." >&2
    exit 1
fi

PHP_VERSION="$("${PHP_BIN}" -r 'echo PHP_VERSION;')"
echo "==> Using ${PHP_BIN} (PHP ${PHP_VERSION})"

# Take the site down so residents never hit a half-migrated schema. The trap
# guarantees it comes back up even if a migration aborts partway through.
echo "==> Enabling maintenance mode"
"${PHP_BIN}" artisan down --render="errors::503" --retry=60 || true
trap '"${PHP_BIN}" artisan up || true' EXIT

# Clear first: a cached config from the previous release points at the old
# .env values, and artisan migrate would read those instead of the new ones.
echo "==> Clearing stale caches"
"${PHP_BIN}" artisan optimize:clear

echo "==> Running migrations"
"${PHP_BIN}" artisan migrate --force

echo "==> Rebuilding caches (config, events, routes, views)"
"${PHP_BIN}" artisan optimize

# Queued jobs (receipt e-mails) run under the old code until the workers are
# told to pick up the new release.
echo "==> Signalling queue workers to restart"
"${PHP_BIN}" artisan queue:restart

echo "==> Bringing the site back up"
"${PHP_BIN}" artisan up
trap - EXIT

echo "==> Deploy complete"
