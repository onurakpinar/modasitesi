#!/bin/sh
set -e

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
    echo "HATA: APP_KEY tanımlı değil. Coolify ortam değişkenlerine ekleyin." >&2
    exit 1
fi

if [ "${APP_DEBUG:-false}" = "true" ]; then
    echo "UYARI: APP_DEBUG=true — üretimde false olmalı." >&2
fi

mkdir -p storage/framework/{cache,sessions,views} storage/app/public bootstrap/cache

if [ ! -L public/storage ]; then
    php artisan storage:link --force
fi

php artisan package:discover --ansi
php artisan optimize

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

php-fpm -D
exec nginx -g 'daemon off;'
