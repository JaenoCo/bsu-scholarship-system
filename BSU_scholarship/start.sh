#!/bin/sh

echo "Starting Laravel..."

# Run migrations safely
php artisan migrate --force

# OPTIONAL: seed ONLY if needed (comment after first deploy)
# php artisan db:seed --force

# Clear caches
php artisan config:clear
php artisan cache:clear

echo "Starting server..."
php artisan serve --host=0.0.0.0 --port=10000