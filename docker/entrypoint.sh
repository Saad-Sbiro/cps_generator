#!/usr/bin/env sh
set -e

cd /var/www

if [ ! -f .env ]; then
  cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi

if [ -n "${DB_HOST}" ] && [ -n "${DB_PORT}" ]; then
  echo "Waiting for database at ${DB_HOST}:${DB_PORT}..."
  until nc -z "${DB_HOST}" "${DB_PORT}"; do
    sleep 1
  done
fi

php artisan optimize:clear || true

if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
  php artisan migrate --force
fi

if [ "${AUTO_SEED:-false}" = "true" ]; then
  php artisan db:seed --force
fi

exec "$@"
