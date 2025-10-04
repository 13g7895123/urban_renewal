FROM php:8.2-cli

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
    default-mysql-client \
    libzip-dev \
    zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd intl zip

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

# Start CodeIgniter development server
CMD ["php", "spark", "serve", "--host", "0.0.0.0", "--port", "8000"]