#!/bin/sh
set -e

if [ ! -f vendor/autoload.php ]; then
    composer install --prefer-dist --no-interaction
fi

if [ ! -d node_modules ]; then
    npm ci
fi

printf '%s\n' "${VITE_DEV_SERVER_URL:-http://localhost:5173}" > public/hot

exec npm run dev -- --host 0.0.0.0
