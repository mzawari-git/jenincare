#!/bin/bash
# ═══════════════════════════════════════════════════════
# JeniCare Production Deploy (Run on jenincare.shop)
# ═══════════════════════════════════════════════════════
# 
# SSH into your server and run:
#   cd public_html
#   bash deploy.sh
# 
# Or set up cron to auto-deploy every 5 minutes:
#   */5 * * * * cd /home/username/public_html && bash deploy.sh >> storage/logs/deploy.log 2>&1
# ═══════════════════════════════════════════════════════

cd "$(dirname "$0")"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deploy started"

git fetch origin 2>&1

LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/master)

if [ "$LOCAL" = "$REMOTE" ]; then
    echo "Already up to date ($LOCAL)"
    exit 0
fi

echo "Updating from $LOCAL to $REMOTE"
git reset --hard origin/master 2>&1

/usr/local/bin/php composer.phar install --no-interaction --prefer-dist --no-dev --optimize-autoloader 2>&1

/usr/local/bin/php artisan migrate --force 2>&1
/usr/local/bin/php artisan config:clear 2>&1
/usr/local/bin/php artisan route:clear 2>&1
/usr/local/bin/php artisan view:clear 2>&1
/usr/local/bin/php artisan cache:clear 2>&1

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deploy complete"
