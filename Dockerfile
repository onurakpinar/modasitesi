# syntax=docker/dockerfile:1

FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

COPY . .
RUN rm -rf bootstrap/cache/*.php \
    && composer dump-autoload --optimize --classmap-authoritative \
    && rm -f .env .env.backup .env.production 2>/dev/null || true \
    && test ! -f .env

FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

FROM php:8.4-fpm-alpine AS app

RUN apk add --no-cache \
        nginx \
        curl \
        sqlite-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        freetype-dev \
        oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_sqlite gd opcache bcmath \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-production.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/start.sh /usr/local/bin/start.sh

COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

RUN chmod +x /usr/local/bin/start.sh \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/app/public bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && test ! -f .env

EXPOSE 8080

# Liveness: uygulama ayağa kalktı mı (DB gerekmez)
HEALTHCHECK --interval=15s --timeout=10s --start-period=10s --retries=5 \
    CMD curl -fsS http://127.0.0.1:8080/up || exit 1

CMD ["/usr/local/bin/start.sh"]
