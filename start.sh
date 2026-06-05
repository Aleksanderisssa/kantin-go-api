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

# 3. Cache config agar performa lebih baik di production
#    CATATAN: route:cache TIDAK dijalankan karena routes/api.php
#    menggunakan closure (Route::get('/health', function(){...}))
#    yang tidak bisa di-serialize. Jalankan route:cache hanya
#    setelah semua closure diganti ke Controller method.
echo "[3/4] Caching config..."
php artisan config:cache

# 4. Jalankan PHP built-in server
echo "[4/4] Starting server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
