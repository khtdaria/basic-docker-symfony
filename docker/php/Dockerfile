FROM php:8.5-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    bcmath \
    gd \
    zip \
    intl

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

WORKDIR /var/www/html
