#!/bin/bash

echo "測試企業管理者 API"
echo "=================="

# 登入獲取 token
echo "1. 登入..."
LOGIN_RESPONSE=$(curl -s -X POST "http://localhost:9228/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin123"}')

TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"token":"eyJ[^"]*"' | sed 's/"token":"//' | sed 's/"$//')

if [ -z "$TOKEN" ]; then
  echo "❌ 登入失敗"
  echo "$LOGIN_RESPONSE"
  exit 1
fi

echo "✓ Token: ${TOKEN:0:30}..."
echo ""

# 測試 company-managers API
echo "2. 測試 company-managers API..."
RESPONSE=$(curl -s -X GET "http://localhost:9228/api/urban-renewals/company-managers" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

echo "$RESPONSE" | head -50
echo ""

# 計算返回的管理者數量
COUNT=$(echo "$RESPONSE" | grep -o '"id":' | wc -l)
echo "✓ 返回 $COUNT 位企業管理者"
