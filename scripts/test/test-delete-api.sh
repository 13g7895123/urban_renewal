#!/bin/bash

echo "========================================="
echo "測試刪除 Property Owner API"
echo "========================================="
echo ""

# 配置
BACKEND_URL="http://localhost:9228"

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

# 2. 建立測試資料
echo "2. 建立測試用的 property owner..."
docker exec urban_renewal_db_dev mariadb -u root -purban_renewal_pass -D urban_renewal -e "
INSERT INTO property_owners (urban_renewal_id, owner_code, name, id_number, created_at, updated_at)
VALUES (6, 'TEST_API_DELETE', '測試API刪除', 'Z777777777', NOW(), NOW());

SELECT LAST_INSERT_ID() as test_id;
" > /tmp/test_id.txt

TEST_ID=$(tail -1 /tmp/test_id.txt | tr -d '\r\n')
echo "✓ 測試資料已建立，ID: $TEST_ID"
echo ""

# 3. 驗證資料存在
echo "3. 驗證資料已建立..."
docker exec urban_renewal_db_dev mariadb -u root -purban_renewal_pass -D urban_renewal -e "
SELECT id, owner_code, name FROM property_owners WHERE id = $TEST_ID;
"
echo ""

# 4. 執行刪除 API
echo "4. 執行刪除 API..."
echo "DELETE ${BACKEND_URL}/api/property-owners/${TEST_ID}"
echo "Authorization: Bearer ${TOKEN:0:20}..."
echo ""

DELETE_RESPONSE=$(curl -s -X DELETE "${BACKEND_URL}/api/property-owners/${TEST_ID}" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -w "\nHTTP_STATUS:%{http_code}")

echo "刪除回應:"
echo "$DELETE_RESPONSE"
echo ""

# 5. 檢查刪除結果
echo "5. 檢查刪除結果..."
REMAINING=$(docker exec urban_renewal_db_dev mariadb -u root -purban_renewal_pass -D urban_renewal -e "
SELECT COUNT(*) as count FROM property_owners WHERE id = $TEST_ID;
" | tail -1)

if [ "$REMAINING" = "0" ]; then
  echo "✅ 測試成功！資料已被刪除"
else
  echo "❌ 測試失敗！資料仍然存在"
  docker exec urban_renewal_db_dev mariadb -u root -purban_renewal_pass -D urban_renewal -e "
  SELECT id, owner_code, name FROM property_owners WHERE id = $TEST_ID;
  "
fi

echo ""
echo "6. 查看後端日誌..."
docker logs urban_renewal_backend_dev --tail 20 2>&1 | grep -i "delete\|error" || echo "沒有相關日誌"

# 清理
rm -f /tmp/test_id.txt

echo ""
echo "========================================="
echo "測試完成"
echo "========================================="
