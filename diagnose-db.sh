#!/bin/bash
# Comprehensive Database & phpMyAdmin Diagnosis
# 全面診斷資料庫和 phpMyAdmin 問題

set -e

echo "========================================="
echo "🔍 全面系統診斷"
echo "========================================="
echo ""

# 載入環境變數
if [ ! -f .env.production ]; then
    echo "❌ 錯誤：找不到 .env.production 檔案"
    exit 1
fi

set -a
while IFS='=' read -r key value || [ -n "$key" ]; do
    [[ -z "$key" || "$key" =~ ^[[:space:]]*# ]] && continue
    key=$(echo "$key" | xargs)
    value=$(echo "$value" | xargs)
    value="${value%\"}"
    value="${value#\"}"
    value="${value%\'}"
    value="${value#\'}"
    if [ -n "$key" ]; then
        export "$key=$value"
    fi
done < .env.production
set +a

echo "📊 步驟 1: 檢查容器狀態"
echo "========================================="
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep -E "urban_renewal|NAMES"
echo ""

echo "📊 步驟 2: 檢查網路連接"
echo "========================================="
echo "檢查 phpMyAdmin 是否能連接到 MariaDB..."

# 測試 phpMyAdmin 容器內的網路
if docker ps | grep -q "phpmyadmin"; then
    PMA_CONTAINER=$(docker ps --filter "ancestor=phpmyadmin:5.2" --format "{{.Names}}" | head -1)
    if [ -n "$PMA_CONTAINER" ]; then
        echo "phpMyAdmin 容器: $PMA_CONTAINER"
        
        echo "測試從 phpMyAdmin 容器 ping MariaDB..."
        docker exec $PMA_CONTAINER ping -c 2 mariadb 2>/dev/null && echo "✅ 網路連接正常" || echo "❌ 無法 ping 到 mariadb"
        
        echo ""
        echo "檢查 phpMyAdmin 環境變數..."
        docker exec $PMA_CONTAINER printenv | grep PMA
    else
        echo "❌ 找不到 phpMyAdmin 容器"
    fi
else
    echo "❌ phpMyAdmin 容器未運行"
fi

echo ""
echo "📊 步驟 3: 檢查 MariaDB 監聽端口"
echo "========================================="
echo "檢查 MariaDB 是否在監聽 3306..."
docker exec urban_renewal_db_prod netstat -tlnp 2>/dev/null | grep 3306 || docker exec urban_renewal_db_prod ss -tlnp | grep 3306
echo ""

echo "📊 步驟 4: 測試資料庫連接（多種方式）"
echo "========================================="

# 測試 1: localhost
echo "測試 1: 使用 localhost..."
if docker exec urban_renewal_db_prod mariadb -uroot -p"${DB_ROOT_PASSWORD}" -e "SELECT 'localhost connection OK' as result;" 2>&1 | grep -q "localhost connection OK"; then
    echo "✅ localhost 連接成功"
else
    echo "❌ localhost 連接失敗"
fi

# 測試 2: 127.0.0.1
echo "測試 2: 使用 127.0.0.1..."
if docker exec urban_renewal_db_prod mariadb -uroot -p"${DB_ROOT_PASSWORD}" -h 127.0.0.1 -e "SELECT '127.0.0.1 connection OK' as result;" 2>&1 | grep -q "127.0.0.1 connection OK"; then
    echo "✅ 127.0.0.1 連接成功"
else
    echo "❌ 127.0.0.1 連接失敗"
fi

# 測試 3: 容器名稱
echo "測試 3: 使用容器名稱 (mariadb)..."
if docker exec urban_renewal_db_prod mariadb -uroot -p"${DB_ROOT_PASSWORD}" -h mariadb -e "SELECT 'mariadb hostname connection OK' as result;" 2>&1 | grep -q "mariadb hostname connection OK"; then
    echo "✅ mariadb hostname 連接成功"
else
    echo "❌ mariadb hostname 連接失敗"
fi

# 測試 4: urban_user
echo "測試 4: 使用 urban_user 帳號..."
if docker exec urban_renewal_db_prod mariadb -u"${DB_USERNAME}" -p"${DB_PASSWORD}" -e "SELECT 'urban_user connection OK' as result;" 2>&1 | grep -q "urban_user connection OK"; then
    echo "✅ urban_user 連接成功"
else
    echo "❌ urban_user 連接失敗"
fi

echo ""
echo "📊 步驟 5: 檢查用戶權限表"
echo "========================================="
docker exec urban_renewal_db_prod mariadb -uroot -p"${DB_ROOT_PASSWORD}" -e "SELECT User, Host, plugin, authentication_string FROM mysql.user WHERE User IN ('root', '${DB_USERNAME}');" 2>/dev/null || echo "❌ 無法查詢用戶表"

echo ""
echo "📊 步驟 6: 檢查 phpMyAdmin 日誌"
echo "========================================="
if [ -n "$PMA_CONTAINER" ]; then
    echo "最近的 phpMyAdmin 日誌："
    docker logs $PMA_CONTAINER --tail 30
else
    echo "❌ 找不到 phpMyAdmin 容器"
fi

echo ""
echo "📊 步驟 7: 測試從 phpMyAdmin 容器連接 MariaDB"
echo "========================================="
if [ -n "$PMA_CONTAINER" ]; then
    echo "嘗試從 phpMyAdmin 容器內部連接 MariaDB..."
    
    # 安裝 mariadb-client（如果沒有）
    docker exec $PMA_CONTAINER bash -c "command -v mysql" 2>/dev/null || \
        docker exec $PMA_CONTAINER bash -c "apt-get update && apt-get install -y mariadb-client" 2>/dev/null || \
        echo "⚠️ 無法安裝 mariadb-client"
    
    # 測試連接
    if docker exec $PMA_CONTAINER bash -c "mysql -h mariadb -uroot -p${DB_ROOT_PASSWORD} -e 'SELECT 1;'" 2>&1 | grep -q "ERROR"; then
        echo "❌ 從 phpMyAdmin 容器無法連接到 MariaDB"
        echo "錯誤詳情："
        docker exec $PMA_CONTAINER bash -c "mysql -h mariadb -uroot -p${DB_ROOT_PASSWORD} -e 'SELECT 1;'" 2>&1 | tail -5
    else
        echo "✅ 從 phpMyAdmin 容器可以連接到 MariaDB"
    fi
fi

echo ""
echo "========================================="
echo "📋 診斷完成！請查看上述輸出"
echo "========================================="
echo ""

echo "💡 根據診斷結果的建議修復方案："
echo ""
echo "如果步驟 4 中 urban_user 連接成功："
echo "  → 使用 urban_user 登入 phpMyAdmin"
echo "  → 用戶名: ${DB_USERNAME}"
echo "  → 密碼: ${DB_PASSWORD}"
echo ""
echo "如果步驟 2 顯示網路連接失敗："
echo "  → 執行: docker network connect urban_renewal_urban_renewal_network $PMA_CONTAINER"
echo ""
echo "如果步驟 6 phpMyAdmin 日誌顯示錯誤："
echo "  → 檢查日誌中的具體錯誤訊息"
echo ""
echo "如果步驟 7 連接失敗："
echo "  → 問題可能是 MariaDB 的 bind-address 設定"
echo "  → 需要修改 MariaDB 配置允許遠端連接"
echo ""
