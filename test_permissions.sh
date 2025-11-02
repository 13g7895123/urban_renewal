#!/bin/bash

echo "=== 測試企業管理者權限 ==="
echo ""

# 1. 登入取得 token (使用實際存在的帳號)
echo "1. 登入..."
LOGIN_RESPONSE=$(curl -s -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test_manager","password":"password123"}')

echo "Login Response: $LOGIN_RESPONSE"
echo ""

# 提取 token
TOKEN=$(echo $LOGIN_RESPONSE | grep -oP '"token":"[^"]+' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "❌ 登入失敗或無法取得 token"
    exit 1
fi

echo "✅ Token 取得成功: ${TOKEN:0:50}..."
echo ""

# 2. 取得用戶資訊
echo "2. 取得用戶資訊 (/api/auth/me)..."
ME_RESPONSE=$(curl -s -X GET http://localhost:9228/api/auth/me \
  -H "Authorization: Bearer $TOKEN")

echo "$ME_RESPONSE" | grep -E "id|role|is_company_manager|urban_renewal_id" || echo "$ME_RESPONSE"
echo ""

# 3. 測試 /api/urban-renewals
echo "3. 測試 /api/urban-renewals..."
RENEWALS_RESPONSE=$(curl -s -X GET http://localhost:9228/api/urban-renewals \
  -H "Authorization: Bearer $TOKEN")

echo "$RENEWALS_RESPONSE" | head -c 500
echo ""
echo ""

# 4. 測試 /api/urban-renewals/6/property-owners
echo "4. 測試 /api/urban-renewals/6/property-owners..."
OWNERS_RESPONSE=$(curl -s -X GET http://localhost:9228/api/urban-renewals/6/property-owners \
  -H "Authorization: Bearer $TOKEN")

echo "$OWNERS_RESPONSE" | head -c 500
echo ""

# 檢查最後10筆 debug log
echo ""
echo "5. 檢查 JWT Debug Logs..."
docker exec urban_renewal_backend_dev php spark db:table jwt_debug_logs --limit 3 --order-by "created_at DESC" 2>&1 | tail -20
