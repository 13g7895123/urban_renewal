#!/bin/bash
# Simple MariaDB Password Reset
# 簡單的 MariaDB 密碼重置

set -e

echo "========================================="
echo "🔐 MariaDB 簡易密碼重置"
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

echo "目前設定的密碼："
echo "  DB_ROOT_PASSWORD = ${DB_ROOT_PASSWORD}"
echo "  DB_PASSWORD = ${DB_PASSWORD}"
echo ""

# 建立 SQL 腳本
cat > /tmp/reset_password.sql <<EOF
-- 重置密碼
FLUSH PRIVILEGES;

-- 刪除並重建 root 用戶
DROP USER IF EXISTS 'root'@'%';
DROP USER IF EXISTS 'root'@'localhost';

CREATE USER 'root'@'%' IDENTIFIED BY '${DB_ROOT_PASSWORD}';
CREATE USER 'root'@'localhost' IDENTIFIED BY '${DB_ROOT_PASSWORD}';

GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;

-- 重建 urban_user
DROP USER IF EXISTS '${DB_USERNAME}'@'%';
DROP USER IF EXISTS '${DB_USERNAME}'@'localhost';

CREATE USER '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
CREATE USER '${DB_USERNAME}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';

GRANT ALL PRIVILEGES ON ${DB_DATABASE}.* TO '${DB_USERNAME}'@'%';
GRANT ALL PRIVILEGES ON ${DB_DATABASE}.* TO '${DB_USERNAME}'@'localhost';

FLUSH PRIVILEGES;

SELECT 'Password reset completed!' as Status;
EOF

echo "📝 SQL 腳本已建立"
echo ""
echo "選擇執行方式："
echo ""
echo "1. 🚀 停止容器 → 掛載 init script → 重啟 (最安全)"
echo "2. 🔧 Docker exec 直接執行 (快速但可能失敗)"
echo "3. ❌ 取消"
echo ""
read -p "請選擇 (1-3): " choice

case $choice in
    1)
        echo ""
        echo "方法 1: 使用 init script..."
        
        # 停止容器
        echo "步驟 1: 停止容器..."
        docker stop urban_renewal_db_prod
        
        # 複製 SQL 到 volume
        echo "步驟 2: 準備初始化腳本..."
        docker run --rm \
            -v urban_renewal_db_data:/var/lib/mysql \
            -v /tmp/reset_password.sql:/docker-entrypoint-initdb.d/reset.sql:ro \
            --entrypoint bash \
            mariadb:11.4 \
            -c "cp /docker-entrypoint-initdb.d/reset.sql /var/lib/mysql/reset.sql"
        
        # 啟動容器並執行腳本
        echo "步驟 3: 啟動容器..."
        docker start urban_renewal_db_prod
        sleep 8
        
        echo "步驟 4: 執行密碼重置..."
        docker exec urban_renewal_db_prod bash -c "mariadb < /var/lib/mysql/reset.sql" 2>&1 || {
            echo "⚠️  直接執行失敗，嘗試 skip-grant-tables..."
            
            # 建立配置文件
            docker exec urban_renewal_db_prod bash -c "echo '[mysqld]
skip-grant-tables
skip-networking=0' > /etc/mysql/conf.d/skip-grant.cnf"
            
            # 重啟
            docker restart urban_renewal_db_prod
            sleep 8
            
            # 執行重置
            docker exec urban_renewal_db_prod mariadb < /tmp/reset_password.sql
            
            # 移除配置
            docker exec urban_renewal_db_prod rm /etc/mysql/conf.d/skip-grant.cnf
            
            # 再次重啟
            docker restart urban_renewal_db_prod
            sleep 8
        }
        
        # 清理
        docker exec urban_renewal_db_prod rm -f /var/lib/mysql/reset.sql 2>/dev/null || true
        ;;
        
    2)
        echo ""
        echo "方法 2: 直接執行..."
        
        # 嘗試多種方式執行
        echo "嘗試執行 SQL..."
        
        # 方式 1: 透過檔案
        docker cp /tmp/reset_password.sql urban_renewal_db_prod:/tmp/
        
        if docker exec urban_renewal_db_prod mariadb < /tmp/reset_password.sql 2>&1 | grep -q "completed"; then
            echo "✅ 密碼重置成功（方式 1）"
        else
            echo "方式 1 失敗，嘗試方式 2..."
            
            # 方式 2: 一行一行執行
            docker exec urban_renewal_db_prod mariadb <<EOF
FLUSH PRIVILEGES;
ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_ROOT_PASSWORD}';
ALTER USER 'root'@'%' IDENTIFIED BY '${DB_ROOT_PASSWORD}';
FLUSH PRIVILEGES;
EOF
        fi
        ;;
        
    3)
        echo "❌ 已取消"
        rm /tmp/reset_password.sql
        exit 0
        ;;
        
    *)
        echo "❌ 無效的選擇"
        rm /tmp/reset_password.sql
        exit 1
        ;;
esac

# 測試連接
echo ""
echo "步驟 5: 測試新密碼..."
sleep 3

if docker exec urban_renewal_db_prod mariadb -uroot -p"${DB_ROOT_PASSWORD}" -e "SELECT 'Success!' as result;" 2>&1 | grep -q "Success"; then
    echo ""
    echo "✅✅✅ 密碼重置成功！✅✅✅"
    echo ""
    echo "phpMyAdmin 登入資訊："
    echo "  用戶名: root"
    echo "  密碼: ${DB_ROOT_PASSWORD}"
    echo ""
    echo "或使用："
    echo "  用戶名: ${DB_USERNAME}"
    echo "  密碼: ${DB_PASSWORD}"
    echo ""
    echo "phpMyAdmin 網址: https://urban-renewal.mercylife.cc/pma"
else
    echo ""
    echo "⚠️  測試失敗，但可能已部分成功"
    echo "請手動測試登入"
fi

# 清理
rm /tmp/reset_password.sql

echo ""
echo "========================================="
echo "✅ 操作完成！"
echo "========================================="
