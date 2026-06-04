#!/bin/bash
# ──────────────────────────────────────────────────────────────────────────────
# Railway startup script — KantinGO API (Laravel 10)
# ──────────────────────────────────────────────────────────────────────────────

echo "╔══════════════════════════════════════════╗"
echo "║     KantinGO API — Railway Start         ║"
echo "╚══════════════════════════════════════════╝"
echo "PORT  = ${PORT:-8000}"
echo "PHP   = $(php -r 'echo PHP_VERSION;')"

# ── 1. Clear old caches (wajib sebelum cache ulang) ──────────────────────────
echo ""
echo "🧹 Clearing old caches..."
php artisan config:clear  2>&1 || true
php artisan route:clear   2>&1 || true
php artisan view:clear    2>&1 || true
php artisan cache:clear   2>&1 || true

# ── 2. Cache ulang dengan env vars yang sudah tersedia ───────────────────────
echo ""
echo "⚡ Caching config, routes & views..."
php artisan config:cache  2>&1 || echo "⚠️  config:cache failed (check APP_KEY)"
php artisan route:cache   2>&1 || echo "⚠️  route:cache failed"
php artisan view:cache    2>&1 || echo "⚠️  view:cache failed"

# ── 3. Database migration ─────────────────────────────────────────────────────
echo ""
echo "🗄️ Running database migrations..."
if php artisan migrate --force 2>&1; then
    echo "✅ Migration successful"
else
    echo "❌ Migration failed — check DB_HOST / DB_PASSWORD in Railway Variables"
    echo "   App will still start, but may not function correctly."
fi

# ── 4. Storage symlink ────────────────────────────────────────────────────────
echo ""
echo "🔗 Creating storage link..."
php artisan storage:link 2>/dev/null || true

# ── 5. Start server ───────────────────────────────────────────────────────────
echo ""
echo "🚀 Starting PHP server on 0.0.0.0:${PORT:-8000} ..."
echo "   Public URL: ${APP_URL:-'(APP_URL not set)'}"
echo ""

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
