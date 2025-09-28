#!/bin/bash

# Urban Renewal Local Development Environment Startup Script
# This script starts the local development environment using docker-compose.local.yml

echo "🚀 Starting Urban Renewal Local Development Environment..."
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

echo "📦 Building and starting containers..."
echo "   Frontend: http://localhost:${FRONTEND_PORT:-3001}"
echo "   Backend: http://localhost:${BACKEND_PORT:-3002}"
echo "   Database: localhost:${DB_PORT:-3306}"
echo "   phpMyAdmin: http://localhost:${PHPMYADMIN_PORT:-3003}"
echo ""

# Start the development environment
# Try docker compose first (newer version), fallback to docker-compose (older version)
if command -v docker &> /dev/null && docker compose version &> /dev/null; then
    docker compose -f docker-compose.local.yml --env-file .env.local up --build
elif command -v docker-compose &> /dev/null; then
    docker-compose -f docker-compose.local.yml --env-file .env.local up --build
else
    echo "❌ Neither 'docker compose' nor 'docker-compose' is available. Please install Docker Compose."
    exit 1
fi

echo ""
echo "🛑 Development environment stopped."