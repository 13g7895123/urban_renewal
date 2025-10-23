#!/bin/bash

# Urban Renewal Local Development Environment Stop Script
# This script stops the local development environment

echo "🛑 Stopping Urban Renewal Local Development Environment..."

# Check if .env.local exists
if [ ! -f ".env.local" ]; then
    echo "⚠️  Warning: .env.local file not found."
fi

# Try docker compose (newer version) first, fallback to docker-compose (older version)
if command -v docker &> /dev/null && docker compose version &> /dev/null; then
    docker compose -f docker-compose.local.yml down --remove-orphans
elif command -v docker-compose &> /dev/null; then
    docker-compose -f docker-compose.local.yml down --remove-orphans
else
    echo "❌ Neither 'docker compose' nor 'docker-compose' command found."
    echo "Please install Docker Compose first."
    exit 1
fi

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Development environment stopped successfully!"
else
    echo "❌ Failed to stop development environment"
    exit 1
fi
