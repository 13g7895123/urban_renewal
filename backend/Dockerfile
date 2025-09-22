FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    icu-dev \
    mysql-client \
    bash

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create .env file and set environment
RUN if [ ! -f .env ]; then cp env .env; fi && \
    sed -i "s/# CI_ENVIRONMENT = production/CI_ENVIRONMENT = development/" .env || echo "CI_ENVIRONMENT = development" >> .env

# Install dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/writable && \
    chmod -R 777 /var/www/html/writable

EXPOSE 8000

CMD ["php-fpm"]