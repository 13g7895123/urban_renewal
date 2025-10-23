#!/bin/bash

# Urban Renewal Frontend Startup Script
# This script starts the frontend development server locally
# Make sure backend services are running using start-backend.sh

echo "🚀 Starting Urban Renewal Frontend Development Server..."
echo ""

# Check if frontend directory exists
if [ ! -d "frontend" ]; then
    echo "❌ Frontend directory not found."
    exit 1
fi

# Change to frontend directory
cd frontend

# Check if package.json exists
if [ ! -f "package.json" ]; then
    echo "❌ package.json not found in frontend directory."
    exit 1
fi

# Check if node_modules exists, if not install dependencies
if [ ! -d "node_modules" ]; then
    echo "📦 Installing dependencies..."
    npm install
    echo ""
fi

# Load environment variables from parent .env.local if it exists
if [ -f "../.env.local" ]; then
    set -a
    source ../.env.local
    set +a
    echo "✅ Loaded environment variables from .env.local"
    echo ""
fi

# Display info
echo "🌐 Frontend will be available at: http://localhost:3000"
echo "🔗 Backend API: http://localhost:${BACKEND_PORT:-9228}"
echo ""
echo "💡 Make sure backend services are running (use ./start-backend.sh)"
echo "   Press Ctrl+C to stop the frontend server"
echo ""

# Start the development server
npm run dev
