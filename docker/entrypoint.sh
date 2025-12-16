#!/bin/sh

# Install dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Generate key if not set
php artisan key:generate --force

# Run migrations
php artisan migrate --force

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the main process
exec "$@"

