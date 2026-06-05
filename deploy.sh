#!/bin/bash
# SkinAnalyzer Deployment Script
# Usage: bash deploy.sh [branch]

set -e

BRANCH="${1:-main}"
APP_DIR="$(cd "$(dirname "$0")" && pwd)"
LOG_FILE="${APP_DIR}/storage/logs/deploy.log"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Starting deployment of branch: $BRANCH" | tee -a "$LOG_FILE"

# 1. Enter maintenance mode
echo "--> Entering maintenance mode..."
php artisan down --retry=30

# 2. Pull latest code
echo "--> Pulling latest code from $BRANCH..."
git fetch origin
git reset --hard "origin/$BRANCH"

# 3. Install PHP dependencies
echo "--> Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Install & build frontend (if package.json exists)
if [ -f "package.json" ]; then
    echo "--> Installing NPM dependencies..."
    npm ci --production
    echo "--> Building assets..."
    npm run build
fi

# 5. Run migrations
echo "--> Running database migrations..."
php artisan migrate --force

# 6. Cache configurations (NOT route:cache - web.php has Closures)
echo "--> Caching configurations..."
php artisan config:cache
php artisan view:cache
php artisan event:cache

# 7. Restart queue workers
echo "--> Restarting queue workers..."
php artisan queue:restart

# 8. Exit maintenance mode
echo "--> Exiting maintenance mode..."
php artisan up

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deployment completed successfully!" | tee -a "$LOG_FILE"
