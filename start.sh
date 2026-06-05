#!/bin/bash

# PENTING: Jangan pakai set -e di sini.
# Kalau satu artisan command gagal (misal config:cache karena env belum siap),
# server tetap harus jalan. Pakai || true agar error tidak membunuh proses.

echo "=== KantinGO API startup ==="

php artisan config:clear  || true
php artisan route:clear   || true
php artisan cache:clear   || true

php artisan migrate --force || true

echo "=== Starting server on port ${PORT:-8000} ==="
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
