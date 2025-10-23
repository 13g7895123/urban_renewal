#!/bin/bash

# Urban Renewal Backend Services Startup Script
# This script starts only the backend services (backend, database, phpmyadmin)
# Run the frontend separately using start-frontend.sh

echo "🚀 Starting Urban Renewal Backend Services..."
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker and try again."
    exit 1
fi

# Check if .env.local exists
if [ ! -f ".env.local" ]; then
    echo "❌ .env.local file not found. Please create it first."
    exit 1
fi

# Load environment variables from .env.local
set -a
source .env.local
set +a

echo "🧹 Cleaning up any existing backend containers..."
# Stop and remove any existing containers to avoid conflicts
if command -v docker &> /dev/null && docker compose version &> /dev/null; then
    docker compose -f docker-compose.local.yml --env-file .env.local down backend mariadb_dev phpmyadmin_dev 2>/dev/null || true
elif command -v docker-compose &> /dev/null; then
    docker-compose -f docker-compose.local.yml --env-file .env.local down backend mariadb_dev phpmyadmin_dev 2>/dev/null || true
fi

echo ""
echo "📦 Starting backend services..."
echo "   Backend API: http://localhost:${BACKEND_PORT:-9228}"
echo "   Database: localhost:${DB_PORT:-3306}"
echo "   phpMyAdmin: http://localhost:${PHPMYADMIN_PORT:-3003}"
echo ""
echo "💡 Run './start-frontend.sh' in another terminal to start the frontend"
echo ""

# Start only the backend services (this will also start their dependencies)
if command -v docker &> /dev/null && docker compose version &> /dev/null; then
    docker compose -f docker-compose.local.yml --env-file .env.local up --build backend mariadb_dev phpmyadmin_dev
elif command -v docker-compose &> /dev/null; then
    docker-compose -f docker-compose.local.yml --env-file .env.local up --build backend mariadb_dev phpmyadmin_dev
else
    echo "❌ Neither 'docker compose' nor 'docker-compose' is available. Please install Docker Compose."
    exit 1
fi

echo ""
echo "🛑 Backend services stopped."
