#!/bin/bash

echo "=========================================="
echo "Urban Renewal - Token 傳遞測試"
echo "=========================================="
echo ""

# 測試 1: 登入並取得 token
echo "【步驟 1】測試登入 API..."
echo ""

# 使用已知的測試帳號
LOGIN_RESPONSE=$(curl -s -X POST http://localhost:9228/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "test_user",
    "password": "password123"
  }')

echo "登入回應:"
echo "$LOGIN_RESPONSE" | head -c 300
echo ""
echo ""

# 嘗試提取 token
TOKEN=$(echo "$LOGIN_RESPONSE" | grep -oP '"token":"[^"]+' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "❌ 無法取得 token，嘗試其他測試帳號..."
    echo ""
    
    # 查詢資料庫中的企業管理者帳號
    echo "【查詢資料庫】尋找企業管理者帳號..."
    docker exec urban_renewal_backend_dev php -r "
    require '/var/www/html/vendor/autoload.php';
    \$app = \Config\Services::codeigniter();
    \$app->initialize();
    \$db = \Config\Database::connect();
    \$query = \$db->query('SELECT username, email, is_company_manager, urban_renewal_id FROM users WHERE is_company_manager = 1 LIMIT 3');
    \$results = \$query->getResultArray();
    if (empty(\$results)) {
        echo 'No company managers found' . PHP_EOL;
    } else {
        foreach (\$results as \$row) {
            echo 'Username: ' . \$row['username'] . ' | Email: ' . (\$row['email'] ?? 'N/A') . ' | Urban Renewal ID: ' . (\$row['urban_renewal_id'] ?? 'NULL') . PHP_EOL;
        }
    }
    " 2>&1 | grep -v "Deprecated\|Warning" | grep -E "Username|No company"
    
    echo ""
    echo "ℹ️  請手動登入前端介面測試："
    echo "   1. 瀏覽器開啟: http://localhost:4001/login"
    echo "   2. 使用上述任一帳號登入（密碼通常是 password 或 password123）"
    echo "   3. 打開 F12 開發者工具 > Console"
    echo "   4. 執行: sessionStorage.getItem('auth')"
    echo "   5. 查看是否有 token 欄位"
    echo ""
    exit 1
fi

echo "✅ Token 取得成功: ${TOKEN:0:50}..."
echo ""

# 測試 2: 使用 token 呼叫 /api/auth/me
echo "【步驟 2】驗證 token 有效性 (/api/auth/me)..."
echo ""

ME_RESPONSE=$(curl -s -X GET http://localhost:9228/api/auth/me \
  -H "Authorization: Bearer $TOKEN")

echo "$ME_RESPONSE" | head -c 500
echo ""
echo ""

# 提取 user_id 和 urban_renewal_id
USER_ID=$(echo "$ME_RESPONSE" | grep -oP '"id":\s*\d+' | head -1 | grep -oP '\d+')
URBAN_RENEWAL_ID=$(echo "$ME_RESPONSE" | grep -oP '"urban_renewal_id":\s*\d+' | grep -oP '\d+')
IS_COMPANY_MANAGER=$(echo "$ME_RESPONSE" | grep -oP '"is_company_manager":\s*\d+' | grep -oP '\d+')

echo "用戶資訊:"
echo "  - User ID: $USER_ID"
echo "  - Urban Renewal ID: $URBAN_RENEWAL_ID"
echo "  - Is Company Manager: $IS_COMPANY_MANAGER"
echo ""

# 測試 3: 測試 /api/urban-renewals
echo "【步驟 3】測試更新會列表 API (/api/urban-renewals)..."
echo ""

RENEWALS_RESPONSE=$(curl -s -X GET http://localhost:9228/api/urban-renewals \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")

# 檢查是否有錯誤
if echo "$RENEWALS_RESPONSE" | grep -q "未授權\|error"; then
    echo "❌ API 返回錯誤:"
    echo "$RENEWALS_RESPONSE" | head -c 300
    echo ""
else
    echo "✅ API 調用成功"
    echo "$RENEWALS_RESPONSE" | head -c 300
    echo ""
fi
echo ""

# 測試 4: 如果有 urban_renewal_id，測試 property-owners API
if [ ! -z "$URBAN_RENEWAL_ID" ]; then
    echo "【步驟 4】測試所有權人 API (/api/urban-renewals/$URBAN_RENEWAL_ID/property-owners)..."
    echo ""
    
    OWNERS_RESPONSE=$(curl -s -X GET "http://localhost:9228/api/urban-renewals/$URBAN_RENEWAL_ID/property-owners" \
      -H "Authorization: Bearer $TOKEN" \
      -H "Content-Type: application/json")
    
    if echo "$OWNERS_RESPONSE" | grep -q "未授權\|error\|權限不足"; then
        echo "❌ API 返回錯誤:"
        echo "$OWNERS_RESPONSE" | head -c 300
    else
        echo "✅ API 調用成功"
        echo "$OWNERS_RESPONSE" | head -c 300
    fi
    echo ""
fi

# 檢查後端日誌
echo "【步驟 5】檢查後端最新日誌..."
echo ""
docker exec urban_renewal_backend_dev tail -20 /var/www/html/writable/logs/log-$(date +%Y-%m-%d).log 2>&1 | \
  grep -E "JWTAuthFilter|PropertyOwnerController|DEBUG" | tail -10

echo ""
echo "=========================================="
echo "測試完成"
echo "=========================================="
