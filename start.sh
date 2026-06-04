#!/bin/bash
# ──────────────────────────────────────────────────────────────────────────────
# Railway startup script untuk Laravel 10
# Dijalankan setiap kali container dimulai.
# ──────────────────────────────────────────────────────────────────────────────
set -e

echo "╔══════════════════════════════════════╗"
echo "║       KantinGO API — Starting        ║"
echo "╚══════════════════════════════════════╝"

# 1. Jalankan migrasi (--force agar tidak ditanya di environment production)
echo "📦 Running database migrations..."
php artisan migrate --force

# 2. Storage symlink (untuk file uploads)
echo "🔗 Creating storage link..."
php artisan storage:link 2>/dev/null || true

# 3. Cache config, route, dan view menggunakan env vars yang sudah tersedia
echo "⚡ Caching config, routes & views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Ready! Starting server on 0.0.0.0:${PORT:-8000}..."

# 4. Jalankan server
# php artisan serve cukup untuk student project.
# Untuk production serius → pertimbangkan nginx + php-fpm.
php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
