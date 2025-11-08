#!/bin/bash

echo "========================================="
echo "測試企業管理者 API"
echo "========================================="
echo ""

# 配置
BACKEND_URL="http://localhost:4002"

# 1. 登入取得 token
echo "1. 登入取得 token..."
LOGIN_RESPONSE=$(curl -s -X POST "${BACKEND_URL}/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "admin123"
  }')

echo "登入回應: $LOGIN_RESPONSE"
TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o 'eyJ[A-Za-z0-9._-]*' | head -1)

if [ -z "$TOKEN" ]; then
  echo "❌ 無法取得 token，登入失敗"
  echo "完整回應: $LOGIN_RESPONSE"
  exit 1
fi

echo "✓ Token 已取得: ${TOKEN:0:20}..."
echo ""

# 2. 測試 company-managers API
echo "2. 測試企業管理者 API..."
echo "GET ${BACKEND_URL}/api/urban-renewals/company-managers"
echo "Authorization: Bearer ${TOKEN:0:20}..."
echo ""

RESPONSE=$(curl -s -X GET "${BACKEND_URL}/api/urban-renewals/company-managers" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -w "\nHTTP_STATUS:%{http_code}")

echo "API 回應:"
echo "$RESPONSE"
echo ""

# 3. 檢查資料庫中的企業管理者
echo "3. 檢查資料庫中的企業管理者..."
docker exec urban_renewal_db_dev mariadb -u root -purban_renewal_pass -D urban_renewal -e "
SELECT id, username, full_name, email, is_company_manager, is_active
FROM users
WHERE is_company_manager = 1 AND is_active = 1;
"

echo ""
echo "========================================="
echo "測試完成"
echo "========================================="
