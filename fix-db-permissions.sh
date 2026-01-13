#!/bin/bash
# Fix Database Permissions for phpMyAdmin
# 修復資料庫權限，讓 phpMyAdmin 可以正常登入

set -e

echo "========================================="
echo "修復資料庫權限 - Fix DB Permissions"
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

echo "📊 環境變數載入完成"
echo "  - DB_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}"
echo "  - DB_USERNAME: ${DB_USERNAME}"
echo "  - DB_PASSWORD: ${DB_PASSWORD}"
echo ""

# 檢查容器是否運行
if ! docker ps | grep -q "urban_renewal_db_prod"; then
    echo "❌ 錯誤：MariaDB 容器未運行"
    echo "請先啟動容器：./start-prod.sh"
    exit 1
fi

echo "🔧 修復資料庫權限..."
echo ""

# 執行權限修復 SQL
docker exec urban_renewal_db_prod mysql -uroot -p"${DB_ROOT_PASSWORD}" <<EOF
-- 修復 root 用戶權限
DROP USER IF EXISTS 'root'@'%';
CREATE USER 'root'@'%' IDENTIFIED BY '${DB_ROOT_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;

-- 修復 urban_user 權限
DROP USER IF EXISTS '${DB_USERNAME}'@'%';
CREATE USER '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_DATABASE}.* TO '${DB_USERNAME}'@'%';

-- 刷新權限
FLUSH PRIVILEGES;

-- 顯示當前用戶
SELECT User, Host FROM mysql.user WHERE User IN ('root', '${DB_USERNAME}');
EOF

echo ""
echo "✅ 資料庫權限修復完成！"
echo ""
echo "📋 現在可以使用以下帳號登入 phpMyAdmin："
echo ""
echo "  方法 1 - Root 帳號："
echo "    用戶名: root"
echo "    密碼: ${DB_ROOT_PASSWORD}"
echo ""
echo "  方法 2 - 應用帳號："
echo "    用戶名: ${DB_USERNAME}"
echo "    密碼: ${DB_PASSWORD}"
echo ""
echo "🌐 phpMyAdmin 位址："
echo "  https://urban-renewal.mercylife.cc/pma"
echo ""
