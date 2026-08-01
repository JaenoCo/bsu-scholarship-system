#!/bin/sh
set -e

printf "Starting Laravel container entrypoint...\n"

if [ -f "/var/www/html/.env" ]; then
    printf "Using .env configuration\n"
else
    printf "WARNING: .env not found, ensure env vars are provided by Render.\n"
fi

printf "Running database migrations...\n"
php artisan migrate --force

printf "Caching configuration and routes...\n"
php artisan config:cache --force
php artisan route:cache --force
php artisan view:cache --force

printf "Starting supervisor...\n"
if [ "$#" -gt 0 ]; then
    exec "$@"
else
    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf
fi