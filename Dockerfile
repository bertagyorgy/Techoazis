FROM php:8.2-apache

# Szükséges kiterjesztések telepítése a PHP-hoz
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql

# Apache beállítása
RUN a2enmod rewrite
WORKDIR /var/www/html