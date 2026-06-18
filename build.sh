#!/bin/bash
set -e
echo "=== SIGE UCAO — Vercel Build ==="

# Download Composer if not available
if ! command -v composer &>/dev/null; then
  echo "Downloading Composer..."
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer 2>/dev/null \
    || php composer-setup.php --quiet
  rm -f composer-setup.php
fi

# Use composer.phar if global not available
if command -v composer &>/dev/null; then
  COMPOSER="composer"
elif [ -f composer.phar ]; then
  COMPOSER="php composer.phar"
else
  echo "ERROR: composer not found"; exit 1
fi

echo "Using: $($COMPOSER --version)"

# Install PHP dependencies
$COMPOSER install --no-dev --optimize-autoloader --no-interaction

# Run migrations + seed against PostgreSQL (Neon)
if [ -n "$DATABASE_URL" ]; then
  echo "Running migrations..."
  php artisan migrate --force
  php artisan db:seed --force 2>/dev/null || echo "Seed already done or skipped"
fi

# Cache for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Build complete ==="
