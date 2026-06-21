# Production image: multi-stage build (frontend + PHP dependencies + runtime)
# Build from project root:
#   docker build -f docker/prod/Dockerfile -t truyen-audio .

# --- Stage 1: Build frontend assets ---
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json .npmrc ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources

RUN npm run build

# --- Stage 2: Install PHP dependencies ---
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# --- Stage 3: Production runtime ---
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && rm -rf /var/cache/apk/*

COPY docker/shared/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/prod/php/php.prod.ini /usr/local/etc/php/conf.d/100-prod.ini

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/prod/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/prod/supervisord.conf /etc/supervisord.conf
COPY docker/prod/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -f http://127.0.0.1/ || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
