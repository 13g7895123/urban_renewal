#!/bin/bash
set -e

# Update .env file with environment variables if they're set
if [ -f .env ]; then
    echo "Updating .env file with environment variables..."
    if [ ! -z "${DB_HOST}" ]; then
        sed -i "s/^database\.default\.hostname\s*=.*/database.default.hostname = ${DB_HOST}/" .env
    fi
    if [ ! -z "${DB_DATABASE}" ]; then
        sed -i "s/^database\.default\.database\s*=.*/database.default.database = ${DB_DATABASE}/" .env
    fi
    if [ ! -z "${DB_USERNAME}" ]; then
        sed -i "s/^database\.default\.username\s*=.*/database.default.username = ${DB_USERNAME}/" .env
    fi
    if [ ! -z "${DB_PASSWORD}" ]; then
        sed -i "s/^database\.default\.password\s*=.*/database.default.password = ${DB_PASSWORD}/" .env
    fi
fi

# Install composer dependencies if needed
echo "Checking composer dependencies..."
if [ ! -d "vendor/phpoffice/phpword" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --optimize-autoloader
else
    echo "Composer dependencies already installed."
fi

echo "Waiting for database to be ready..."
# Wait for database to be ready
until php -r "new PDO('mysql:host=${DB_HOST};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" &> /dev/null; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "Database is ready!"

# Run migrations
echo "Running database migrations..."
php spark migrate --all

# Check if location data exists
LOCATION_COUNT=$(php -r "
\$db = new PDO('mysql:host=${DB_HOST};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');
\$result = \$db->query('SELECT COUNT(*) as count FROM counties');
\$row = \$result->fetch(PDO::FETCH_ASSOC);
echo \$row['count'];
")

if [ "$LOCATION_COUNT" -eq "0" ]; then
    echo "No location data found. Running location seeder..."
    php spark db:seed OfficialTaiwanLocationSeeder
else
    echo "Location data already exists (${LOCATION_COUNT} counties). Skipping seeder."
fi

# Check if user data exists
USER_COUNT=$(php -r "
\$db = new PDO('mysql:host=${DB_HOST};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');
\$result = \$db->query('SELECT COUNT(*) as count FROM users');
\$row = \$result->fetch(PDO::FETCH_ASSOC);
echo \$row['count'];
")

if [ "$USER_COUNT" -eq "0" ]; then
    echo "No users found. Running user seeder..."
    php spark db:seed UserSeeder
else
    echo "Users already exist (${USER_COUNT} users). Skipping user seeder."
fi

echo "Database initialization complete!"

# Start the application
exec "$@"
