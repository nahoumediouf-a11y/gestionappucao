#!/bin/sh
set -e

php artisan config:clear
php artisan migrate --force

if [ "$SEED_DB" = "true" ]; then
    php artisan db:seed --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
