#!/bin/sh
set -e

# Configure database connection from DATABASE_URL if provided (Neon PostgreSQL)
if [ -n "$DATABASE_URL" ]; then
  export DB_CONNECTION=pgsql
  # Parse DATABASE_URL: postgresql://user:pass@host/db?sslmode=require
  export DB_URL="$DATABASE_URL"
fi

# SQLite fallback for local dev only
if [ "$DB_CONNECTION" = "sqlite" ]; then
  mkdir -p database
  touch database/database.sqlite
fi

php artisan config:clear
php artisan migrate --force

if [ "$SEED_DB" = "true" ]; then
  php artisan db:seed --force 2>/dev/null || echo "Seed skipped (data already exists)"
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
