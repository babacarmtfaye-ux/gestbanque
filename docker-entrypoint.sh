#!/bin/sh

# Attendre la DB PostgreSQL
echo "Waiting for database to be ready..."
until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME"; do
  echo "Database is unavailable - sleeping"
  sleep 1
done

echo "Database is up - executing migrations"
php artisan config:cache
php artisan route:cache
php artisan migrate --force

echo "Starting Laravel..."
exec "$@"

