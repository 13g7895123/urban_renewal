#!/bin/bash

# 獲取新的 token
echo "獲取 token..."
RESPONSE=$(curl -s -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}')

# 嘗試多種方式提取 token
TOKEN=$(echo "$RESPONSE" | tr -d '\n' | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
  # 使用另一種方法
  TOKEN=$(echo "$RESPONSE" | sed -n 's/.*"token":"\([^"]*\)".*/\1/p' | tr -d '\n')
fi

echo "Token 長度: ${#TOKEN}"

echo ""
echo "=== 測試 1: 匯入錯誤的排除類型 ==="
echo ""

curl -s -X POST "http://localhost:8000/api/urban-renewals/6/property-owners/import" \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@/tmp/test_invalid_exclusion.xlsx"

echo ""
echo ""
echo "=== 測試 2: 匯入正確的排除類型 ==="
echo ""

curl -s -X POST "http://localhost:8000/api/urban-renewals/6/property-owners/import" \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@/tmp/test_valid_exclusion.xlsx"

echo ""
