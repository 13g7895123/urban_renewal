#!/bin/bash

echo "========================================="
echo "測試共有部分對應建物 API"
echo "========================================="
echo ""

# 配置
BACKEND_URL="http://localhost:9228"
URBAN_RENEWAL_ID="6"

# 1. 登入取得 token (系統管理員)
echo "1. 系統管理員登入..."
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

# 2. 測試 all-buildings API
echo "2. 測試共有部分對應建物 API..."
echo "GET ${BACKEND_URL}/api/urban-renewals/${URBAN_RENEWAL_ID}/property-owners/all-buildings"
echo "Authorization: Bearer ${TOKEN:0:20}..."
echo ""

RESPONSE=$(curl -s -X GET "${BACKEND_URL}/api/urban-renewals/${URBAN_RENEWAL_ID}/property-owners/all-buildings" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -w "\nHTTP_STATUS:%{http_code}")

echo "API 回應:"
echo "$RESPONSE"
echo ""

# 提取 HTTP 狀態碼
HTTP_STATUS=$(echo "$RESPONSE" | grep "HTTP_STATUS" | cut -d: -f2)
echo "HTTP 狀態碼: $HTTP_STATUS"
echo ""

# 3. 驗證回應
if [[ "$RESPONSE" == *"success"* ]]; then
  echo "✓ API 成功返回資料"

  # 提取建物數量
  BUILDING_COUNT=$(echo "$RESPONSE" | grep -o '"total_buildings":[0-9]*' | cut -d: -f2)
  echo "✓ 找到 $BUILDING_COUNT 個建物"
  echo ""

  # 顯示前幾個建物的詳細資訊
  echo "建物資料樣本 (格式化):"
  echo "$RESPONSE" | grep -o '"id":[^,}]*' | head -5
else
  echo "❌ API 返回錯誤"
fi

echo ""
echo "========================================="
echo "測試完成"
echo "========================================="
