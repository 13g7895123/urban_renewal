#!/bin/sh
set -e

# Install composer dependencies if not already installed
if [ ! -d "/var/www/html/backend/vendor" ]; then
    echo "Installing composer dependencies..."
    cd /var/www/html/backend
    composer install --no-interaction --no-dev --optimize-autoloader
fi

# Execute the main command (crond)
exec "$@"
