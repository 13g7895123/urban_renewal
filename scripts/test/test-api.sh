#!/bin/bash

# 登入並取得 token
LOGIN_RESPONSE=$(curl -s -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}')

TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
  echo "Failed to get token"
  exit 1
fi

echo "Token obtained successfully"

# 取得企業使用者資料
echo "Fetching enterprise users..."
curl -X GET "http://localhost:9228/api/users?user_type=enterprise&urban_renewal_id=1&per_page=100" \
  -H "Authorization: Bearer $TOKEN" \
  | python3 -m json.tool