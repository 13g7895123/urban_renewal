#!/bin/bash

echo "Starting Urban Renewal Production Environment..."

# Check if .env.production exists
if [ ! -f .env.production ]; then
    echo "Error: .env.production file not found!"
    exit 1
fi

# Load environment variables from .env.production
export $(grep -v '^#' .env.production | xargs)

echo "Starting containers (without rebuilding)..."

# Try docker compose (newer version) first, fallback to docker-compose (older version)
if command -v docker &> /dev/null && docker compose version &> /dev/null; then
    docker compose -f docker-compose.prod.yml --env-file .env.production up -d
elif command -v docker-compose &> /dev/null; then
    docker-compose -f docker-compose.prod.yml --env-file .env.production up -d
else
    echo "Error: Neither 'docker compose' nor 'docker-compose' command found."
    echo "Please install Docker Compose first."
    exit 1
fi

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Production environment started successfully!"
    echo ""
    echo "Services are available at:"
    echo "  Frontend:    http://localhost:${FRONTEND_PORT}"
    echo "  Backend:     http://localhost:${BACKEND_PORT}"
    echo "  Database:    localhost:${DB_PORT}"
    echo "  phpMyAdmin:  http://localhost:${PHPMYADMIN_PORT}"
    echo ""
    echo "To view logs: docker compose -f docker-compose.prod.yml logs -f"
    echo "To stop: ./stop-production.sh"
else
    echo "Error: Failed to start production environment"
    exit 1
fi
