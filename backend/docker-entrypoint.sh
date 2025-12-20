#!/bin/bash
set -e

# Ensure writable directory permissions (for development with volume mounts)
echo "Setting writable directory permissions..."
mkdir -p /var/www/html/writable/cache
mkdir -p /var/www/html/writable/logs
mkdir -p /var/www/html/writable/session
mkdir -p /var/www/html/writable/uploads
mkdir -p /var/www/html/writable/debugbar
chmod -R 777 /var/www/html/writable

# Update .env file with environment variables if they're set
if [ -f .env ]; then
    echo "Updating .env file with environment variables..."
    echo "  DB_HOST=${DB_HOST}"
    echo "  DB_DATABASE=${DB_DATABASE}"
    echo "  DB_USERNAME=${DB_USERNAME}"
    echo "  DB_PASSWORD=******"
    
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
    
    echo "Updated .env database settings:"
    grep "database.default" .env | head -5
fi

# ============================================
# Check and install composer dependencies
# ============================================
check_dependencies() {
    echo "Checking composer dependencies..."
    
    # List of critical packages that must exist
    REQUIRED_PACKAGES=(
        "phpoffice/phpword"
        "phpoffice/phpspreadsheet"
        "firebase/php-jwt"
        "codeigniter4/framework"
    )
    
    MISSING_PACKAGES=()
    
    for package in "${REQUIRED_PACKAGES[@]}"; do
        # Convert package name to directory path (e.g., phpoffice/phpword -> vendor/phpoffice/phpword)
        PACKAGE_DIR="vendor/${package}"
        if [ ! -d "$PACKAGE_DIR" ]; then
            echo "  ⚠️  Missing package: $package"
            MISSING_PACKAGES+=("$package")
        else
            echo "  ✓ Package found: $package"
        fi
    done
    
    # If any packages are missing, run composer install
    if [ ${#MISSING_PACKAGES[@]} -gt 0 ]; then
        echo ""
        echo "⚠️  Missing ${#MISSING_PACKAGES[@]} required package(s). Running composer install..."
        composer install --no-interaction --optimize-autoloader
        
        # Verify installation was successful
        for package in "${MISSING_PACKAGES[@]}"; do
            PACKAGE_DIR="vendor/${package}"
            if [ ! -d "$PACKAGE_DIR" ]; then
                echo "❌ CRITICAL ERROR: Failed to install $package"
                echo "   Please check composer.json and try running 'composer install' manually."
                exit 1
            fi
        done
        echo "✓ All dependencies installed successfully."
    else
        echo "✓ All required packages are installed."
    fi
    
    # Verify autoloader is valid
    if [ ! -f "vendor/autoload.php" ]; then
        echo "❌ CRITICAL ERROR: vendor/autoload.php not found"
        echo "   Running composer dump-autoload..."
        composer dump-autoload --optimize
    fi
    
    echo ""
}

check_dependencies


echo "Waiting for database to be ready..."
# Wait for database to be ready
until php -r "new PDO('mysql:host=${DB_HOST};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" &> /dev/null; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "Database is ready!"

# Run migrations
echo "Running database migrations..."
php spark migrate --all || echo "Migration completed (may have warnings)"

# Check if location data exists
echo "Checking location data..."
LOCATION_COUNT=$(php -r "
try {
    \$db = new PDO('mysql:host=${DB_HOST};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');
    \$result = \$db->query('SELECT COUNT(*) as count FROM counties');
    if (\$result) {
        \$row = \$result->fetch(PDO::FETCH_ASSOC);
        echo \$row['count'];
    } else {
        echo '0';
    }
} catch (Exception \$e) {
    echo '0';
}
" 2>/dev/null || echo "0")

if [ "$LOCATION_COUNT" -eq "0" ] 2>/dev/null; then
    echo "No location data found. Running location seeder..."
    php spark db:seed OfficialTaiwanLocationSeeder || echo "Location seeder completed (may have warnings)"
else
    echo "Location data already exists (${LOCATION_COUNT} counties). Skipping seeder."
fi

# Check if user data exists
echo "Checking user data..."
USER_COUNT=$(php -r "
try {
    \$db = new PDO('mysql:host=${DB_HOST};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');
    \$result = \$db->query('SELECT COUNT(*) as count FROM users');
    if (\$result) {
        \$row = \$result->fetch(PDO::FETCH_ASSOC);
        echo \$row['count'];
    } else {
        echo '0';
    }
} catch (Exception \$e) {
    echo '0';
}
" 2>/dev/null || echo "0")

if [ "$USER_COUNT" -eq "0" ] 2>/dev/null; then
    echo "No users found. Running user seeder..."
    php spark db:seed UserSeeder || echo "User seeder completed (may have warnings)"
else
    echo "Users already exist (${USER_COUNT} users). Skipping user seeder."
fi

echo "Database initialization complete!"

# Start the application
exec "$@"
