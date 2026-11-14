# Dockerfile - PHP + Apache con soporte para PostgreSQL
FROM php:8.2-apache

# Extensiones: pdo_pgsql (PostgreSQL) y otras comunes
RUN apt-get update && apt-get install -y --fix-missing \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    && apt-get clean \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Document root (docker-compose lo sobrescribe en tiempo de ejecución)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/src

# Apache lee ${APACHE_DOCUMENT_ROOT} de la variable de entorno al arrancar
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html