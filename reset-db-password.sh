#!/bin/bash
# Reset MariaDB Root Password
# 重置 MariaDB Root 密碼

set -e

echo "========================================="
echo "🔐 MariaDB 密碼重置工具"
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

echo "⚠️  檢測到資料庫密碼不匹配問題"
echo ""
echo "目前 .env.production 中的密碼："
echo "  DB_ROOT_PASSWORD = ${DB_ROOT_PASSWORD}"
echo "  DB_PASSWORD = ${DB_PASSWORD}"
echo ""
echo "選擇重置方式："
echo ""
echo "1. 🔧 使用 skip-grant-tables 重置密碼 (推薦)"
echo "2. 🗑️  刪除資料庫 volume 並重新初始化 (會丟失所有資料)"
echo "3. 🔍 嘗試常見密碼並顯示正確的密碼"
echo "4. ❌ 取消"
echo ""
read -p "請選擇 (1-4): " choice

case $choice in
    1)
        echo ""
        echo "🔧 使用 skip-grant-tables 模式重置密碼..."
        echo ""
        
        # 停止容器
        echo "步驟 1: 停止 MariaDB 容器..."
        docker stop urban_renewal_db_prod
        
        # 啟動容器（skip-grant-tables 模式）
        echo "步驟 2: 以 skip-grant-tables 模式啟動..."
        docker run -d \
            --name urban_renewal_db_temp \
            --network urban_renewal_urban_renewal_network \
            -v urban_renewal_db_data:/var/lib/mysql \
            -e MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=yes \
            mariadb:11.4 \
            --skip-grant-tables \
            --skip-networking=0
        
        # 等待啟動
        echo "步驟 3: 等待資料庫啟動..."
        sleep 10
        
        # 重置密碼
        echo "步驟 4: 重置 root 密碼..."
        docker exec urban_renewal_db_temp mariadb -u root <<EOF
FLUSH PRIVILEGES;
ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_ROOT_PASSWORD}';
ALTER USER 'root'@'%' IDENTIFIED BY '${DB_ROOT_PASSWORD}';
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY '${DB_ROOT_PASSWORD}';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
EOF
        
        # 重置 urban_user 密碼
        echo "步驟 5: 重置 urban_user 密碼..."
        docker exec urban_renewal_db_temp mariadb -u root <<EOF
CREATE USER IF NOT EXISTS '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
ALTER USER '${DB_USERNAME}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
GRANT ALL PRIVILEGES ON ${DB_DATABASE}.* TO '${DB_USERNAME}'@'%';
FLUSH PRIVILEGES;
EOF
        
        # 停止臨時容器
        echo "步驟 6: 清理臨時容器..."
        docker stop urban_renewal_db_temp
        docker rm urban_renewal_db_temp
        
        # 啟動正常容器
        echo "步驟 7: 啟動正常的 MariaDB 容器..."
        docker start urban_renewal_db_prod
        
        # 等待啟動
        sleep 5
        
        # 測試連接
        echo "步驟 8: 測試新密碼..."
        if docker exec urban_renewal_db_prod mariadb -uroot -p"${DB_ROOT_PASSWORD}" -e "SELECT 'Password reset successful!' as result;" 2>&1 | grep -q "Password reset successful"; then
            echo ""
            echo "✅ 密碼重置成功！"
            echo ""
            echo "新的登入資訊："
            echo "  Root 用戶: root"
            echo "  Root 密碼: ${DB_ROOT_PASSWORD}"
            echo "  一般用戶: ${DB_USERNAME}"
            echo "  一般密碼: ${DB_PASSWORD}"
        else
            echo "❌ 密碼重置失敗，請查看錯誤訊息"
        fi
        ;;
        
    2)
        echo ""
        echo "⚠️  警告：這將刪除所有資料庫資料！"
        echo ""
        read -p "確定要繼續嗎？輸入 'YES' 確認: " confirm
        
        if [ "$confirm" != "YES" ]; then
            echo "❌ 已取消"
            exit 0
        fi
        
        if command -v docker-compose &> /dev/null; then
            COMPOSE_CMD="docker-compose"
        else
            COMPOSE_CMD="docker compose"
        fi
        
        echo "步驟 1: 停止所有容器..."
        $COMPOSE_CMD -f docker-compose.prod.yml --env-file .env.production down
        
        echo "步驟 2: 刪除資料庫 volume..."
        docker volume rm urban_renewal_db_data || true
        
        echo "步驟 3: 重新啟動容器..."
        $COMPOSE_CMD -f docker-compose.prod.yml --env-file .env.production up -d mariadb
        
        echo "⏳ 等待資料庫初始化..."
        sleep 15
        
        echo "步驟 4: 測試連接..."
        if docker exec urban_renewal_db_prod mariadb -uroot -p"${DB_ROOT_PASSWORD}" -e "SELECT 'Database initialized!' as result;" 2>&1 | grep -q "Database initialized"; then
            echo ""
            echo "✅ 資料庫已重新初始化！"
            echo ""
            echo "⚠️  注意：所有舊資料已刪除"
            echo "您需要重新導入資料或執行遷移腳本"
        else
            echo "❌ 初始化失敗"
        fi
        ;;
        
    3)
        echo ""
        echo "🔍 嘗試常見密碼..."
        echo ""
        
        # 常見密碼列表
        PASSWORDS=("" "root" "password" "123456" "admin" "lGHgaZec" "F5fwDJxr")
        
        for pwd in "${PASSWORDS[@]}"; do
            if [ -z "$pwd" ]; then
                echo "嘗試空密碼..."
                if docker exec urban_renewal_db_prod mariadb -uroot -e "SELECT 'Found!' as result;" 2>&1 | grep -q "Found"; then
                    echo "✅ 找到了！密碼是：(空密碼)"
                    echo ""
                    echo "請更新 .env.production 中的 DB_ROOT_PASSWORD 為空值"
                    exit 0
                fi
            else
                echo "嘗試密碼: $pwd"
                if docker exec urban_renewal_db_prod mariadb -uroot -p"$pwd" -e "SELECT 'Found!' as result;" 2>&1 | grep -q "Found"; then
                    echo "✅ 找到了！正確的密碼是：$pwd"
                    echo ""
                    echo "請更新 .env.production:"
                    echo "  DB_ROOT_PASSWORD=$pwd"
                    exit 0
                fi
            fi
        done
        
        echo ""
        echo "❌ 未找到正確的密碼"
        echo "建議使用選項 1 重置密碼"
        ;;
        
    4)
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
echo "✅ 操作完成！"
echo "========================================="
