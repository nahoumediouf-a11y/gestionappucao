#!/bin/bash
set -e
echo "=== SIGE UCAO — Vercel Build ==="

# Install PHP dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
  php artisan key:generate --force
fi

# Run migrations
php artisan migrate --force

# Seed if fresh DB (optional, check if users exist)
php artisan db:seed --force --class=DatabaseSeeder 2>/dev/null || echo "Seed skipped (data already exists)"

# Clear and cache config for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Build complete ==="
