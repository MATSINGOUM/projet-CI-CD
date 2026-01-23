#!/bin/sh
set -e

echo "Waiting for PostgreSQL..."

until php -r "
try {
    new PDO(
        'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    echo 'Database is ready.' . PHP_EOL;
} catch (Exception \$e) {
    exit(1);
}
"; do
  sleep 2
done

echo "Running migrations..."
php artisan migrate --force

echo "Starting Laravel..."
exec php artisan serve --host=0.0.0.0 --port=9000
