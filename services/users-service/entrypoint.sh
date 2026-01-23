#!/bin/sh
set -e

echo "Preparing SQLite database..."

chmod 666 database/database.sqlite

php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=9000
