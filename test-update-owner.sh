#!/bin/bash

# 測試更新所有權人 API
# 測試 ID: 10, Urban Renewal ID: 6

echo "Testing Property Owner Update API..."
echo "===================================="
echo ""

# 先獲取 token
echo "1. Getting authentication token..."
TOKEN_RESPONSE=$(curl -s -X POST http://localhost:4002/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "admin123"
  }')

TOKEN=$(echo $TOKEN_RESPONSE | jq -r '.data.token')

if [ "$TOKEN" = "null" ] || [ -z "$TOKEN" ]; then
  echo "Failed to get token"
  echo "Response: $TOKEN_RESPONSE"
  exit 1
fi

echo "Token received: ${TOKEN:0:20}..."
echo ""

# 先獲取所有權人的詳細資訊
echo "2. Getting property owner details (ID: 10)..."
GET_RESPONSE=$(curl -s -X GET http://localhost:4002/api/property-owners/10 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo "GET Response:"
echo "$GET_RESPONSE" | jq '.'
echo ""

# 測試更新所有權人
echo "3. Updating property owner (ID: 10)..."
UPDATE_RESPONSE=$(curl -s -X PUT http://localhost:4002/api/property-owners/10 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "owner_name": "測試所有權人",
    "identity_number": "A123456789",
    "phone1": "0912345678",
    "contact_address": "台北市信義區信義路123號",
    "lands": [],
    "buildings": []
  }')

echo "UPDATE Response:"
echo "$UPDATE_RESPONSE" | jq '.'
echo ""

# 檢查後端日誌
echo "4. Checking backend logs for errors..."
docker logs urban_renewal-backend-1 2>&1 | tail -50 | grep -E "(ERROR|getFirstRow|Critical)" || echo "No errors found in recent logs"
