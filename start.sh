#!/bin/bash
# KantinGO API — Minimal Railway startup script
echo "=== KantinGO API Starting ==="
echo "PORT=${PORT:-8000}"
echo "PHP=$(php -r 'echo PHP_VERSION;')"

# JANGAN config:cache di sini — bisa buat cache rusak jika env vars belum ada.
# Railway menyuntikkan env vars langsung, Laravel baca otomatis tanpa cache.

# Hanya clear cache lama
php artisan config:clear  2>&1 || true
php artisan route:clear   2>&1 || true
php artisan view:clear    2>&1 || true

# Database migration (non-fatal — server tetap start meski migration gagal)
echo "Running migrations..."
php artisan migrate --force 2>&1 || echo "WARNING: Migration failed - check DB variables in Railway"

echo "=== Starting server on 0.0.0.0:${PORT:-8000} ==="
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
