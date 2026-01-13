#!/bin/bash
# Alternative Database Permission Fix
# 使用 mariadb-admin 和替代方法修復權限

set -e

echo "========================================="
echo "資料庫權限修復 (方案 2)"
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

echo "🔍 檢查資料庫狀態..."
docker exec urban_renewal_db_prod mariadb-admin -uroot -p"${DB_ROOT_PASSWORD}" ping 2>/dev/null && echo "✅ 資料庫運行正常" || echo "⚠️  資料庫可能有問題"
echo ""

echo "🔧 方法 1: 使用 TCP/IP 連接 (而非 socket)..."
if docker exec urban_renewal_db_prod mariadb -uroot -p"${DB_ROOT_PASSWORD}" -h 127.0.0.1 -e "SELECT 1;" 2>/dev/null; then
    echo "✅ TCP/IP 連接成功！使用此方法修復權限..."
    
    docker exec urban_renewal_db_prod mariadb -uroot -p"${DB_ROOT_PASSWORD}" -h 127.0.0.1 <<EOF
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
    echo "✅ 權限修復完成！"
    exit 0
fi

echo "❌ TCP/IP 連接失敗"
echo ""
echo "🔧 方法 2: 使用現有的 urban_user 帳號修復..."
if docker exec urban_renewal_db_prod mariadb -u"${DB_USERNAME}" -p"${DB_PASSWORD}" -e "SELECT 1;" 2>/dev/null; then
    echo "✅ urban_user 連接成功！"
    echo "⚠️  使用此帳號無法修復 root 權限，但可以確認資料庫正常"
    
    docker exec urban_renewal_db_prod mariadb -u"${DB_USERNAME}" -p"${DB_PASSWORD}" <<EOF
SHOW DATABASES;
EOF
    
    echo ""
    echo "📋 phpMyAdmin 登入資訊："
    echo "  用戶名: ${DB_USERNAME}"
    echo "  密碼: ${DB_PASSWORD}"
    echo ""
    echo "⚠️  注意：root 帳號可能需要重建容器才能修復"
    exit 0
fi

echo "❌ urban_user 連接也失敗"
echo ""
echo "🔧 方法 3: 重建資料庫容器（保留資料）..."
echo "⚠️  警告：這將重啟資料庫容器，可能造成短暫服務中斷"
echo ""
read -p "是否繼續？(y/N) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🔄 重建資料庫容器..."
    
    # 使用 docker-compose 重建
    if command -v docker-compose &> /dev/null; then
        docker-compose -f docker-compose.prod.yml --env-file .env.production restart mariadb
    else
        docker compose -f docker-compose.prod.yml --env-file .env.production restart mariadb
    fi
    
    echo "⏳ 等待資料庫啟動..."
    sleep 10
    
    echo "🔍 檢查新容器狀態..."
    docker exec urban_renewal_db_prod mariadb-admin -uroot -p"${DB_ROOT_PASSWORD}" ping
    
    echo ""
    echo "✅ 資料庫容器已重啟"
    echo "現在請重新執行 ./fix-db-permissions.sh"
else
    echo "❌ 已取消"
fi
