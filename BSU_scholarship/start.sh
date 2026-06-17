#!/bin/sh

echo "Running migrations..."
php artisan migrate --force
php artisan db:seed --force

echo "Clearing cache..."
php artisan config:clear
php artisan cache:clear

echo "Starting Laravel..."
php artisan serve --host=0.0.0.0 --port=10000