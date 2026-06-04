#!/bin/bash
echo "======================================"
echo " KantinGO API Starting on Railway"
echo " PORT=${PORT:-8000}"
echo " PHP=$(php -r 'echo PHP_VERSION;')"
echo "======================================"
echo "APP_ENV=${APP_ENV}"
echo "DB_HOST=${DB_HOST}"
echo "APP_KEY set: $([ -n "$APP_KEY" ] && echo YES || echo NO)"

# 1. Clear cache lama
echo "[1/3] Clearing caches..."
php artisan config:clear 2>&1 || true
php artisan route:clear  2>&1 || true
php artisan view:clear   2>&1 || true

# 2. Migrate database
echo "[2/3] Running migrations..."
php artisan migrate --force 2>&1 || echo "WARNING: Migration failed, continuing..."

# 3. Start server pakai php -S server.php
#    Lebih ringan dan reliable dari 'php artisan serve'
echo "[3/3] Starting PHP server on 0.0.0.0:${PORT:-8000}..."
exec php -S 0.0.0.0:${PORT:-8000} server.php
