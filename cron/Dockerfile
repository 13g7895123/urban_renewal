FROM php:8.2-cli-alpine

# Install system dependencies and cron
RUN apk add --no-cache \
    dcron \
    curl \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    mariadb-connector-c-dev

# Install PHP extensions for database connectivity
RUN docker-php-ext-install pdo pdo_mysql mysqli mbstring intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy crontab file
COPY crontab /etc/crontabs/root

# Create log directory
RUN mkdir -p /var/log/cron

# Set working directory
WORKDIR /var/www/html

# Copy and set up entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Start with entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["crond", "-f", "-l", "2"]
