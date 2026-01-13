#!/bin/bash
# Ultimate Database Permission Fix
# 終極資料庫權限修復方案

set -e

echo "========================================="
echo "終極資料庫權限修復"
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

echo "選擇修復方案："
echo ""
echo "1. 🔄 重啟資料庫容器 (快速，不影響資料)"
echo "2. 🛠️  進入容器直接修復 mysql.user 表"
echo "3. 🔓 使用 skip-grant-tables 模式修復 (最強力)"
echo "4. 🗑️  完全重建資料庫 (警告：會清除所有資料)"
echo "5. ❌ 取消"
echo ""
read -p "請選擇 (1-5): " choice

case $choice in
    1)
        echo ""
        echo "🔄 方案 1: 重啟資料庫容器..."
        echo "⏳ 停止容器..."
        docker stop urban_renewal_db_prod
        
        echo "⏳ 啟動容器..."
        docker start urban_renewal_db_prod
        
        echo "⏳ 等待資料庫就緒..."
        sleep 15
        
        echo "🔍 測試連接..."
        if docker exec urban_renewal_db_prod mariadb-admin -uroot -p"${DB_ROOT_PASSWORD}" ping 2>/dev/null; then
            echo "✅ 資料庫重啟成功！"
            
            # 嘗試修復權限
            docker exec urban_renewal_db_prod mariadb -uroot -p"${DB_ROOT_PASSWORD}" -h 127.0.0.1 <<EOF
DROP USER IF EXISTS 'root'@'%';
CREATE USER 'root'@'%' IDENTIFIED BY '${DB_ROOT_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;

DROP USER IF EXISTS '${DB_USERNAME}'@'%';
CREATE USER '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_DATABASE}.* TO '${DB_USERNAME}'@'%';

FLUSH PRIVILEGES;
SELECT User, Host FROM mysql.user WHERE User IN ('root', '${DB_USERNAME}');
EOF
            echo "✅ 權限修復完成！"
        else
            echo "❌ 重啟後仍無法連接"
        fi
        ;;
        
    2)
        echo ""
        echo "🛠️  方案 2: 直接修復 mysql.user 表..."
        
        # 創建 SQL 修復腳本
        cat > /tmp/fix_mysql_user.sql <<EOF
USE mysql;

-- 刪除舊用戶
DELETE FROM user WHERE User='root' AND Host='%';
DELETE FROM user WHERE User='${DB_USERNAME}' AND Host='%';

-- 插入新用戶 (直接操作 mysql.user 表)
INSERT INTO user (Host, User, Password, Select_priv, Insert_priv, Update_priv, Delete_priv, 
    Create_priv, Drop_priv, Reload_priv, Shutdown_priv, Process_priv, File_priv, 
    Grant_priv, References_priv, Index_priv, Alter_priv, Show_db_priv, Super_priv,
    Create_tmp_table_priv, Lock_tables_priv, Execute_priv, Repl_slave_priv, 
    Repl_client_priv, Create_view_priv, Show_view_priv, Create_routine_priv,
    Alter_routine_priv, Create_user_priv, Event_priv, Trigger_priv)
VALUES 
('%', 'root', PASSWORD('${DB_ROOT_PASSWORD}'), 'Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y','Y'),
('%', '${DB_USERNAME}', PASSWORD('${DB_PASSWORD}'), 'Y','Y','Y','Y','Y','Y','N','N','N','N','N','N','Y','Y','Y','N','Y','Y','Y','N','N','Y','Y','N','N','N','N','N');

-- 刷新權限
FLUSH PRIVILEGES;

-- 顯示結果
SELECT User, Host FROM user WHERE User IN ('root', '${DB_USERNAME}');
EOF
        
        # 複製到容器並執行
        docker cp /tmp/fix_mysql_user.sql urban_renewal_db_prod:/tmp/
        
        echo "⚠️  警告：此方法直接修改 mysql.user 表"
        read -p "確定要繼續嗎？(y/N) " -n 1 -r
        echo
        
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            docker exec urban_renewal_db_prod mariadb -uroot -p"${DB_ROOT_PASSWORD}" < /tmp/fix_mysql_user.sql
            echo "✅ 修復完成！"
        else
            echo "❌ 已取消"
        fi
        
        rm /tmp/fix_mysql_user.sql
        ;;
        
    3)
        echo ""
        echo "🔓 方案 3: 使用 skip-grant-tables 模式..."
        echo "⚠️  警告：此方法需要重啟容器且暫時關閉權限檢查"
        read -p "確定要繼續嗎？(y/N) " -n 1 -r
        echo
        
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            echo "⏳ 停止容器..."
            docker stop urban_renewal_db_prod
            
            echo "🔓 以 skip-grant-tables 模式啟動..."
            docker run -d --rm \
                --name urban_renewal_db_temp \
                --network urban_renewal_urban_renewal_network \
                -e MYSQL_ROOT_PASSWORD="${DB_ROOT_PASSWORD}" \
                -e MYSQL_DATABASE="${DB_DATABASE}" \
                -e MYSQL_USER="${DB_USERNAME}" \
                -e MYSQL_PASSWORD="${DB_PASSWORD}" \
                -v mariadb_prod_data:/var/lib/mysql \
                mariadb:11.4 \
                --skip-grant-tables
            
            sleep 10
            
            echo "🔧 修復權限..."
            docker exec urban_renewal_db_temp mariadb <<EOF
FLUSH PRIVILEGES;

DROP USER IF EXISTS 'root'@'%';
CREATE USER 'root'@'%' IDENTIFIED BY '${DB_ROOT_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;

DROP USER IF EXISTS '${DB_USERNAME}'@'%';
CREATE USER '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_DATABASE}.* TO '${DB_USERNAME}'@'%';

FLUSH PRIVILEGES;
EOF
            
            echo "🔄 停止臨時容器並恢復正常容器..."
            docker stop urban_renewal_db_temp
            docker start urban_renewal_db_prod
            
            sleep 10
            echo "✅ 修復完成！"
        else
            echo "❌ 已取消"
        fi
        ;;
        
    4)
        echo ""
        echo "🗑️  方案 4: 完全重建資料庫容器..."
        echo "⚠️  警告：這將刪除所有資料！"
        echo "⚠️  警告：請確保已備份重要資料！"
        echo ""
        read -p "確定要繼續嗎？請輸入 'DELETE ALL DATA' 確認: " confirm
        
        if [ "$confirm" = "DELETE ALL DATA" ]; then
            echo "🗑️  刪除舊容器和資料..."
            
            if command -v docker-compose &> /dev/null; then
                COMPOSE_CMD="docker-compose"
            else
                COMPOSE_CMD="docker compose"
            fi
            
            $COMPOSE_CMD -f docker-compose.prod.yml --env-file .env.production down -v mariadb
            
            echo "🔄 重建資料庫容器..."
            $COMPOSE_CMD -f docker-compose.prod.yml --env-file .env.production up -d mariadb
            
            echo "⏳ 等待資料庫啟動..."
            sleep 30
            
            echo "🗄️  執行 Migration..."
            $COMPOSE_CMD -f docker-compose.prod.yml --env-file .env.production exec backend php spark migrate --all
            
            echo "✅ 資料庫重建完成！"
        else
            echo "❌ 已取消"
        fi
        ;;
        
    5)
        echo "❌ 已取消"
        exit 0
        ;;
        
    *)
        echo "❌ 無效的選擇"
        exit 1
        ;;
esac

echo ""
echo "========================================="
echo "修復完成！現在可以嘗試登入 phpMyAdmin"
echo "========================================="
echo ""
echo "📋 登入資訊："
echo "  URL: https://urban-renewal.mercylife.cc/pma"
echo "  用戶名: root 或 ${DB_USERNAME}"
echo "  密碼: ${DB_ROOT_PASSWORD} 或 ${DB_PASSWORD}"
echo ""
