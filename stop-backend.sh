#!/bin/bash

# Urban Renewal Backend Services Stop Script
# This script stops the backend services (backend, database, phpmyadmin)

echo "🛑 Stopping Urban Renewal Backend Services..."
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running."
    exit 1
fi

# Check if .env.local exists
if [ ! -f ".env.local" ]; then
    echo "⚠️  .env.local file not found. Trying to stop services anyway..."
fi

# Stop the backend services
if command -v docker &> /dev/null && docker compose version &> /dev/null; then
    if [ -f ".env.local" ]; then
        docker compose -f docker-compose.local.yml --env-file .env.local down backend mariadb_dev phpmyadmin_dev
    else
        docker compose -f docker-compose.local.yml down backend mariadb_dev phpmyadmin_dev
    fi
elif command -v docker-compose &> /dev/null; then
    if [ -f ".env.local" ]; then
        docker-compose -f docker-compose.local.yml --env-file .env.local down backend mariadb_dev phpmyadmin_dev
    else
        docker-compose -f docker-compose.local.yml down backend mariadb_dev phpmyadmin_dev
    fi
else
    echo "❌ Neither 'docker compose' nor 'docker-compose' is available."
    exit 1
fi

echo ""
echo "✅ Backend services stopped successfully."
