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
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/writable

EXPOSE 80

# Entry script to handle composer install, migration and start PHP server
RUN echo '#!/bin/bash\n\
cd /var/www/html\n\
if [ ! -f .env ]; then cp env .env; fi\n\
sed -i "s/# CI_ENVIRONMENT = production/CI_ENVIRONMENT = development/" .env || echo "CI_ENVIRONMENT = development" >> .env\n\
if [ ! -d vendor ]; then composer install --no-interaction --no-dev --optimize-autoloader; fi\n\
echo "Waiting for database connection..."\n\
until mysql -h${DB_HOST} -u${DB_USERNAME} -p${DB_PASSWORD} -e "SELECT 1" ${DB_DATABASE} > /dev/null 2>&1; do\n\
  echo "Database not ready, waiting 5 seconds..."\n\
  sleep 5\n\
done\n\
echo "Database connected, running migrations..."\n\
php spark migrate\n\
echo "Migrations completed successfully"\n\
chmod -R 777 /var/www/html/writable\n\
echo "Starting PHP development server..."\n\
php spark serve --host=0.0.0.0 --port=80' > /entrypoint.sh && chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]