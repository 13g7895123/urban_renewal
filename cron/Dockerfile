FROM php:8.1-cli-alpine

# Install cron
RUN apk add --no-cache dcron

# Copy crontab file
COPY crontab /etc/crontabs/root

# Create log directory
RUN mkdir -p /var/log/cron

# Set working directory
WORKDIR /var/www/html

# Start cron in foreground
CMD ["crond", "-f", "-l", "2"]
