#!/bin/sh
set -e

printf "Starting Laravel container entrypoint...\n"

# Clear old cached files that might break component loading
php artisan config:clear
php artisan route:clear
php artisan view:clear

printf "Running package discovery...\n"
php artisan package:discover --ansi
php artisan storage:link || true

printf "Running database migrations & seeders...\n"
php artisan migrate --force
php artisan db:seed --force

printf "Caching configuration and routes...\n"
php artisan config:cache 
php artisan route:cache 
php artisan view:cache 

printf "Starting supervisor...\n"
if [ "$#" -gt 0 ]; then
    exec "$@"
else
    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisor.conf
fi