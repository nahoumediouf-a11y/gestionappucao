#!/bin/bash
set -e
echo "=== SIGE UCAO — Vercel Build ==="

# Install Composer if not available
if ! command -v composer &>/dev/null; then
  echo "Downloading Composer..."
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php composer-setup.php --quiet
  rm composer-setup.php
  mv composer.phar /usr/local/bin/composer 2>/dev/null || export PATH="$PATH:$(pwd)"
  # If move failed, use local composer.phar
  if ! command -v composer &>/dev/null; then
    alias composer='php composer.phar'
    PHP_COMPOSER="php composer.phar"
  else
    PHP_COMPOSER="composer"
  fi
else
  PHP_COMPOSER="composer"
fi

# Install PHP dependencies
$PHP_COMPOSER install --no-dev --optimize-autoloader --no-interaction

# Storage symlink
php artisan storage:link 2>/dev/null || true

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
  php artisan key:generate --force
fi

# Skip DB operations if no DB configured (SQLite default won't work on Vercel)
if [ -n "$DATABASE_URL" ] || [ -n "$DB_HOST" ]; then
  php artisan migrate --force
  php artisan db:seed --force --class=DatabaseSeeder 2>/dev/null || echo "Seed skipped"
fi

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Build complete ==="
