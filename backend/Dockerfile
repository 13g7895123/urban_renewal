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
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Install Composer dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Set proper permissions for writable directory
RUN chown -R www-data:www-data /var/www/html/writable

# Configure Apache to point to the public directory
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Copy env file if not exists
RUN if [ ! -f .env ]; then cp env .env; fi

# Set environment to development
RUN sed -i 's/# CI_ENVIRONMENT = production/CI_ENVIRONMENT = development/' .env || echo "CI_ENVIRONMENT = development" >> .env

# Update base URL in .env (will be overridden by docker-compose environment)
RUN sed -i "s|# app.baseURL = ''|app.baseURL = 'http://localhost:3001'|" .env || echo "app.baseURL = 'http://localhost:3001'" >> .env

EXPOSE 80

CMD ["apache2-foreground"]