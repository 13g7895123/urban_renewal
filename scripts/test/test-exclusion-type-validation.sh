#!/bin/bash

# 測試排除類型驗證功能

API_URL="http://localhost:9228/api"

echo "=== 測試排除類型驗證功能 ==="
echo ""

# 1. 登入獲取 token
echo "1. 登入系統..."
LOGIN_RESPONSE=$(curl -s -X POST "${API_URL}/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "password"
  }')

TOKEN=$(echo $LOGIN_RESPONSE | tr -d '\n' | grep -o '"token":"[^"]*' | sed 's/"token":"//g')

# 檢查是否成功
if echo "$LOGIN_RESPONSE" | grep -q '"success":true'; then
  if [ -z "$TOKEN" ]; then
    echo "❌ Token 解析失敗"
    exit 1
  fi
else
  echo "❌ 登入失敗"
  echo "Response: $LOGIN_RESPONSE"
  exit 1
fi

echo "✅ 登入成功"
echo ""

# 2. 創建測試 Excel 檔案（使用 CSV 格式測試）
echo "2. 創建測試檔案..."

# 創建一個包含錯誤排除類型的測試檔案
cat > /tmp/test_property_owners_invalid.csv << 'EOF'
姓名,身分證字號,電話,地址,土地持份,是否為實施者,排除類型,備註
測試所有權人1,A123456789,0912345678,台北市信義區,1/10,否,錯誤類型,測試用
測試所有權人2,B123456789,0912345679,台北市大安區,2/10,是,無,正常資料
測試所有權人3,C123456789,0912345680,台北市中山區,1/5,否,不明,另一個錯誤
EOF

# 創建一個包含正確排除類型的測試檔案
cat > /tmp/test_property_owners_valid.csv << 'EOF'
姓名,身分證字號,電話,地址,土地持份,是否為實施者,排除類型,備註
測試所有權人4,D123456789,0912345678,台北市信義區,1/10,否,無,測試用
測試所有權人5,E123456789,0912345679,台北市大安區,2/10,是,面積過小,正常資料
測試所有權人6,F123456789,0912345680,台北市中山區,1/5,否,已出售,正常資料
測試所有權人7,G123456789,0912345681,台北市松山區,3/10,否,未聯繫,正常資料
EOF

echo "✅ 測試檔案創建完成"
echo ""

# 3. 測試匯入包含錯誤排除類型的檔案
echo "3. 測試匯入錯誤的排除類型..."
INVALID_RESPONSE=$(curl -s -X POST "${API_URL}/urban-renewals/6/property-owners/import" \
  -H "Authorization: Bearer ${TOKEN}" \
  -F "file=@/tmp/test_property_owners_invalid.csv")

echo "Response: $INVALID_RESPONSE"
echo ""

# 檢查是否包含錯誤訊息
if echo "$INVALID_RESPONSE" | grep -q "排除類型"; then
  echo "✅ 錯誤訊息正確包含「排除類型」"
else
  echo "⚠️  錯誤訊息未包含「排除類型」"
fi

if echo "$INVALID_RESPONSE" | grep -q "無.*面積過小.*已出售.*未聯繫"; then
  echo "✅ 錯誤訊息正確列出有效選項"
else
  echo "⚠️  錯誤訊息未列出有效選項"
fi
echo ""

# 4. 測試匯入正確的排除類型
echo "4. 測試匯入正確的排除類型..."
VALID_RESPONSE=$(curl -s -X POST "${API_URL}/urban-renewals/6/property-owners/import" \
  -H "Authorization: Bearer ${TOKEN}" \
  -F "file=@/tmp/test_property_owners_valid.csv")

echo "Response: $VALID_RESPONSE"
echo ""

# 檢查是否成功
if echo "$VALID_RESPONSE" | grep -q '"success":true'; then
  echo "✅ 正確的排除類型匯入成功"
else
  echo "⚠️  匯入失敗或有問題"
fi

# 清理測試檔案
rm -f /tmp/test_property_owners_invalid.csv /tmp/test_property_owners_valid.csv

echo ""
echo "=== 測試完成 ==="
