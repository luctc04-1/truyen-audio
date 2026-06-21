FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    curl \
    git \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    unzip \
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

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/shared/php/php.ini /usr/local/etc/php/conf.d/99-app.ini

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]
