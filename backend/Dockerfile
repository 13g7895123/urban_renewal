FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Configure Apache to point to the public directory
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/writable && \
    chown -R www-data:www-data /var/www/html

EXPOSE 80

# Entry script to handle composer install and permissions on startup
RUN echo '#!/bin/bash\n\
cd /var/www/html\n\
if [ ! -f .env ]; then cp env .env; fi\n\
sed -i "s/# CI_ENVIRONMENT = production/CI_ENVIRONMENT = development/" .env || echo "CI_ENVIRONMENT = development" >> .env\n\
if [ ! -d vendor ]; then composer install --no-interaction --no-dev --optimize-autoloader; fi\n\
chown -R www-data:www-data /var/www/html/writable\n\
apache2-foreground' > /entrypoint.sh && chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]