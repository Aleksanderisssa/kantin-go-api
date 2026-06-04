#!/bin/bash
echo "======================================"
echo " KantinGO API — Railway Startup"
echo " PORT=${PORT:-8000}"
echo " PHP=$(php -r 'echo PHP_VERSION;')"
echo "======================================"
echo " APP_ENV=${APP_ENV}"
echo " DB_HOST=${DB_HOST}"
echo " APP_KEY set: $([ -n "$APP_KEY" ] && echo YES || echo NO)"
echo "======================================"

# Migrate database
echo "[1/2] Running migrations..."
php artisan migrate --force 2>&1 || echo "WARNING: Migration failed"

echo "[2/2] Starting server..."

# Deteksi environment: FrankenPHP atau plain PHP
if command -v frankenphp &>/dev/null; then
    echo "→ FrankenPHP detected, using frankenphp run"
    exec frankenphp run --config /app/Caddyfile
else
    echo "→ Plain PHP detected, using php -S server.php"
    exec php -S 0.0.0.0:${PORT:-8000} server.php
fi
