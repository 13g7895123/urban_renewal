#!/bin/bash

# Urban Renewal API Connection Test Script
# 測試前端到後端的連接

echo "=========================================="
echo "🧪 Urban Renewal API Connection Test"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test results
PASS=0
FAIL=0

# Function to test API endpoint
test_api() {
    local name=$1
    local url=$2

    echo -n "Testing ${name}... "

    response=$(curl -s -w "\n%{http_code}" "${url}" 2>&1)
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    if [ "$http_code" = "200" ]; then
        echo -e "${GREEN}✓ PASS${NC} (HTTP $http_code)"
        echo "  URL: ${url}"
        echo "  Response preview: $(echo "$body" | head -c 100)..."
        PASS=$((PASS + 1))
    else
        echo -e "${RED}✗ FAIL${NC} (HTTP ${http_code:-000})"
        echo "  URL: ${url}"
        echo "  Error: ${body:-No response}"
        FAIL=$((FAIL + 1))
    fi
    echo ""
}

echo -e "${BLUE}=== Testing Backend API (Direct) ===${NC}"
echo ""

test_api "Urban Renewals List" "http://localhost:9228/api/urban-renewals"
test_api "Counties List" "http://localhost:9228/api/locations/counties"
test_api "Health Check" "http://localhost:9228/"

echo ""
echo -e "${BLUE}=== Testing Frontend (Web Interface) ===${NC}"
echo ""

test_api "Frontend Home" "http://localhost:4357/"
test_api "Frontend Test Page" "http://localhost:4357/test-api"

echo ""
echo -e "${BLUE}=== Testing Database Services ===${NC}"
echo ""

test_api "phpMyAdmin" "http://localhost:9428/"

echo ""
echo "=========================================="
echo "📊 Test Summary"
echo "=========================================="
echo -e "${GREEN}Passed: ${PASS}${NC}"
echo -e "${RED}Failed: ${FAIL}${NC}"
echo ""

if [ $FAIL -eq 0 ]; then
    echo -e "${GREEN}✓ All tests passed!${NC}"
    echo ""
    echo "🎉 Your Urban Renewal system is working correctly!"
    echo ""
    echo "Next steps:"
    echo "  1. Open browser: http://localhost:4357/test-api"
    echo "  2. Click '測試 API 連接' button"
    echo "  3. Check browser console (F12) for detailed logs"
    exit 0
else
    echo -e "${RED}✗ Some tests failed${NC}"
    echo ""
    echo "Please check:"
    echo "  1. Are all containers running? (docker ps)"
    echo "  2. Check logs: docker logs urban_renewal-frontend-1"
    echo "  3. Check logs: docker logs urban_renewal-backend-1"
    exit 1
fi
