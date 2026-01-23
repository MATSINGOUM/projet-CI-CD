#!/bin/sh
set -e

echo "Running migrations..."
php artisan migrate --force

echo "Starting Laravel..."
exec php artisan serve --host=0.0.0.0 --port=9000
