#!/bin/sh

set -e

echo "Waiting for database connection..."

ATTEMPT=0
MAX_ATTEMPTS=30
SLEEP_SECONDS=2

until php artisan migrate:status >/dev/null 2>&1; do
    ATTEMPT=$((ATTEMPT + 1))

    if [ "$ATTEMPT" -ge "$MAX_ATTEMPTS" ]; then
        echo "Database connection could not be established after ${MAX_ATTEMPTS} attempts."
        exit 1
    fi

    echo "Database not ready (attempt ${ATTEMPT}/${MAX_ATTEMPTS}). Retrying in ${SLEEP_SECONDS}s..."
    sleep "${SLEEP_SECONDS}"
done

echo "Database ready. Running migrations and seeders..."

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link || true

echo "Starting Laravel HTTP server..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"

