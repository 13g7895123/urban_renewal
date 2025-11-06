#!/bin/bash

echo "Testing Property Owner Update with Land/Building..."
echo "===================================================="
echo ""

# Get token
TOKEN_RESPONSE=$(curl -s -X POST http://localhost:4002/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin123"}')

TOKEN=$(echo $TOKEN_RESPONSE | python3 -c "import sys, json; print(json.load(sys.stdin).get('data', {}).get('token', ''))" 2>/dev/null)

if [ -z "$TOKEN" ]; then
  echo "Failed to get token"
  exit 1
fi

echo "Token: ${TOKEN:0:20}..."
echo ""

# Test 1: Update with new land
echo "Test 1: Updating property owner with a new land plot..."
UPDATE_RESPONSE=$(curl -s -X PUT http://localhost:4002/api/property-owners/12 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "owner_name": "測試所有權人",
    "identity_number": "A123456789",
    "phone1": "0912345678",
    "contact_address": "台北市信義區信義路123號",
    "lands": [
      {
        "plot_number": "0001",
        "total_area": "100.50",
        "ownership_numerator": "1",
        "ownership_denominator": "2"
      }
    ],
    "buildings": []
  }')

echo "Response:"
echo "$UPDATE_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$UPDATE_RESPONSE"
echo ""

# Check logs for errors
echo "Checking backend logs..."
docker logs urban_renewal_backend_dev 2>&1 | tail -30 | grep -E "(ERROR|CRITICAL|Warning|error)" || echo "No errors in recent logs"
