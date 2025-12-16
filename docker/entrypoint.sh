#!/bin/bash

set -e

php artisan config:clear 2>/dev/null || true

# Install dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Generate key if not set
php artisan key:generate --force

# Run migrations
echo "Waiting for database..."
while ! mysqladmin ping -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" --ssl=0 --silent 2>/dev/null; do
    echo "Database not ready, waiting..."
    sleep 3
done
echo "Database connected!"

php artisan migrate --force

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the main process
exec "$@"
