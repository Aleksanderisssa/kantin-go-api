#!/bin/bash
set -e

echo "=== KantinGO API startup ==="

# 1. Clear semua cache stale agar perubahan config/database langsung aktif
#    Ini penting karena Railway sering menjalankan cached config lama
echo "[1/4] Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# 2. Jalankan migration terbaru secara otomatis setiap deploy
#    --force wajib untuk environment production (tidak ada prompt interaktif)
echo "[2/4] Running migrations..."
php artisan migrate --force

# 3. Cache config & routes agar performa lebih baik di production
echo "[3/4] Caching config & routes..."
php artisan config:cache
php artisan route:cache

# 4. Jalankan PHP built-in server
echo "[4/4] Starting server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
