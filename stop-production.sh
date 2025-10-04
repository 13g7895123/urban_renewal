#!/bin/bash

echo "Stopping Urban Renewal Production Environment..."

# Try docker compose (newer version) first, fallback to docker-compose (older version)
if command -v docker &> /dev/null && docker compose version &> /dev/null; then
    docker compose -f docker-compose.prod.yml down
elif command -v docker-compose &> /dev/null; then
    docker-compose -f docker-compose.prod.yml down
else
    echo "Error: Neither 'docker compose' nor 'docker-compose' command found."
    echo "Please install Docker Compose first."
    exit 1
fi

if [ $? -eq 0 ]; then
    echo ""
    echo "✓ Production environment stopped successfully!"
else
    echo "Error: Failed to stop production environment"
    exit 1
fi
