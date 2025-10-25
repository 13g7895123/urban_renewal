#!/bin/bash

# Test login functionality

echo "Testing login functionality..."

# 1. Test backend API directly
echo "1. Testing backend API..."
RESPONSE=$(curl -s http://localhost:9228/api/auth/login -X POST -H "Content-Type: application/json" -d '{"username":"admin","password":"password"}')
if echo "$RESPONSE" | grep -q '"success": *true'; then
    echo "✓ Backend API working"
    TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*' | sed 's/"token":"//')
    echo "✓ Token received: ${TOKEN:0:50}..."
else
    echo "✗ Backend API failed"
    echo "$RESPONSE"
fi

# 2. Test frontend login page accessibility
echo ""
echo "2. Testing frontend login page..."
if curl -s http://localhost:3000/login | grep -q "登入"; then
    echo "✓ Login page accessible"
else
    echo "✗ Login page not accessible"
fi

# 3. Test frontend home page
echo ""
echo "3. Testing frontend home page..."
if curl -s http://localhost:3000 | grep -q "<!DOCTYPE html"; then
    echo "✓ Home page accessible"
else
    echo "✗ Home page not accessible"
fi

echo ""
echo "Test complete!"