#!/bin/bash

# Urban Renewal Local NPM Development Script
# This script starts the frontend with npm run dev on a custom port
# Make sure backend is running via Docker or separately

echo "🚀 Starting Urban Renewal Frontend (npm run dev)..."
echo ""

# Check if .env.local exists
if [ ! -f ".env.local" ]; then
    echo "❌ .env.local file not found. Creating default configuration..."
    cat > .env.local << 'EOF'
# Local Development Environment Configuration
# PORT Configuration (using uncommon ports for local development to avoid conflicts)
FRONTEND_PORT=4357
BACKEND_PORT=9228
DB_PORT=9328
PHPMYADMIN_PORT=9428

# Database Configuration (local development database)
DB_HOST=mariadb
DB_DATABASE=urban_renewal
DB_USERNAME=root
DB_PASSWORD=urban_renewal_pass

# Frontend Environment
NODE_ENV=development
BACKEND_API_URL=http://localhost:9228/api
BACKEND_URL=http://localhost:9228

# Backend Path Configuration
BACKEND_CONTEXT_PATH=./backend
BACKEND_VOLUME_PATH=./backend

# Local Development Specific Settings
NUXT_PUBLIC_API_BASE_URL=http://localhost:9228/api
NUXT_PUBLIC_BACKEND_URL=http://localhost:9228
NUXT_PUBLIC_BACKEND_HOST=localhost
NUXT_PUBLIC_BACKEND_PORT=9228
EOF
fi

# Load environment variables from .env.local
set -a
source .env.local
set +a

# Set default port if not specified
FRONTEND_PORT=${FRONTEND_PORT:-4357}

# Check if port is in use and kill the process if needed
echo "🔍 Checking port $FRONTEND_PORT availability..."
PORT_PID=$(lsof -ti :$FRONTEND_PORT 2>/dev/null)
if [ ! -z "$PORT_PID" ]; then
    echo "⚠️  Port $FRONTEND_PORT is in use by process $PORT_PID"
    echo "🔧 Killing process on port $FRONTEND_PORT..."
    kill -9 $PORT_PID 2>/dev/null
    sleep 1
    echo "✅ Port $FRONTEND_PORT is now available"
else
    echo "✅ Port $FRONTEND_PORT is available"
fi

echo ""
echo "📦 Frontend Configuration:"
echo "   Port: $FRONTEND_PORT"
echo "   Backend API: ${BACKEND_API_URL:-http://localhost:9228/api}"
echo ""

# Check if frontend directory exists
if [ ! -d "frontend" ]; then
    echo "❌ frontend directory not found. Please run this script from the project root."
    exit 1
fi

# Navigate to frontend directory
cd frontend

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "📦 Installing frontend dependencies..."
    npm install
    if [ $? -ne 0 ]; then
        echo "❌ Failed to install dependencies."
        exit 1
    fi
fi

echo ""
echo "🎨 Starting frontend development server..."
echo "   Access at: http://localhost:$FRONTEND_PORT"
echo ""
echo "   Press Ctrl+C to stop"
echo ""

# Export environment variables for npm/nuxt to use
export FRONTEND_PORT
export NUXT_PUBLIC_API_BASE_URL="${BACKEND_API_URL:-http://localhost:9228/api}"
export NUXT_PUBLIC_BACKEND_URL="${BACKEND_URL:-http://localhost:9228}"
export NUXT_PUBLIC_BACKEND_HOST="${BACKEND_HOST:-localhost}"
export NUXT_PUBLIC_BACKEND_PORT="${BACKEND_PORT:-9228}"

# Start frontend with custom port
npm run dev

echo ""
echo "🛑 Frontend development server stopped."
