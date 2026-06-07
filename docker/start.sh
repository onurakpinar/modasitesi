#!/bin/sh
set -e

cd /var/www/html

log() {
    echo "[modapusula-start] $*" >&2
}

if [ -z "${APP_KEY:-}" ]; then
    log "HATA: APP_KEY tanımlı değil."
    log "Coolify → Environment Variables → APP_KEY ekleyin (php artisan key:generate --show)"
    exit 1
fi

if [ "${APP_DEBUG:-false}" = "true" ]; then
    log "UYARI: APP_DEBUG=true — üretimde false olmalı."
fi

log "Storage dizinleri hazırlanıyor..."
mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    bootstrap/cache \
    database

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    if [ ! -f "$DB_FILE" ]; then
        log "SQLite veritabanı dosyası oluşturuluyor: $DB_FILE"
        mkdir -p "$(dirname "$DB_FILE")"
        touch "$DB_FILE"
    fi
fi

if [ ! -L public/storage ]; then
    php artisan storage:link --force
fi

log "Paket keşfi..."
php artisan package:discover --ansi

chown -R www-data:www-data storage bootstrap/cache database 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache database 2>/dev/null || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    log "Migration çalıştırılıyor..."
    php artisan migrate --force
fi

if [ "${RUN_DEMO_SEED:-false}" = "true" ]; then
    log "Demo içerik (taslak) yükleniyor..."
    php artisan site:ensure-content --demo --force --no-ansi || log "UYARI: site:ensure-content başarısız."
else
    php artisan site:ensure-content --force --no-ansi || log "UYARI: site:ensure-content başarısız."
fi

chown -R www-data:www-data storage bootstrap/cache database
chmod -R ug+rwx storage bootstrap/cache database

log "Önbellek optimize ediliyor..."
if ! php artisan optimize; then
    log "UYARI: optimize başarısız; temiz önbellekle devam ediliyor."
    php artisan optimize:clear || true
fi

log "php-fpm başlatılıyor..."
php-fpm -D

log "nginx yapılandırması test ediliyor..."
nginx -t

log "nginx başlatılıyor (0.0.0.0:8080)..."
exec nginx -g 'daemon off;'
