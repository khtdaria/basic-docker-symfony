FROM php:8.5-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libzip-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install mysqli pdo pdo_mysql bcmath gd zip

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
