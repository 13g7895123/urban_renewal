#!/bin/bash
# Quick phpMyAdmin Fix - Alternative Solutions
# phpMyAdmin 快速修復 - 替代方案

set -e

echo "========================================="
echo "phpMyAdmin 替代修復方案"
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
echo "1. 🔄 重建 phpMyAdmin 容器（保留資料庫）"
echo "2. 🔧 修改 MariaDB bind-address 允許遠端連接"
echo "3. 🌐 新增獨立的 phpMyAdmin 配置文件"
echo "4. 🐳 使用 Adminer 替代 phpMyAdmin"
echo "5. 📊 直接使用命令行查看資料庫"
echo "6. ❌ 取消"
echo ""
read -p "請選擇 (1-6): " choice

case $choice in
    1)
        echo ""
        echo "🔄 重建 phpMyAdmin 容器..."
        
        if command -v docker-compose &> /dev/null; then
            COMPOSE_CMD="docker-compose"
        else
            COMPOSE_CMD="docker compose"
        fi
        
        # 停止並移除舊容器
        $COMPOSE_CMD -f docker-compose.prod.yml --env-file .env.production stop phpmyadmin
        $COMPOSE_CMD -f docker-compose.prod.yml --env-file .env.production rm -f phpmyadmin
        
        # 重新建立並啟動
        $COMPOSE_CMD -f docker-compose.prod.yml --env-file .env.production up -d phpmyadmin
        
        echo "⏳ 等待 phpMyAdmin 啟動..."
        sleep 5
        
        echo "✅ phpMyAdmin 已重建！"
        echo "請訪問: https://urban-renewal.mercylife.cc/pma"
        ;;
        
    2)
        echo ""
        echo "🔧 修改 MariaDB bind-address..."
        
        # 建立自訂配置
        mkdir -p ./mariadb-config
        cat > ./mariadb-config/custom.cnf <<EOF
[mysqld]
bind-address = 0.0.0.0
skip-networking = 0
EOF
        
        echo "✅ 配置文件已建立: ./mariadb-config/custom.cnf"
        echo ""
        echo "請在 docker-compose.prod.yml 中的 mariadb 服務添加："
        echo ""
        echo "  volumes:"
        echo "    - ./mariadb-config/custom.cnf:/etc/mysql/conf.d/custom.cnf:ro"
        echo ""
        echo "然後執行: $COMPOSE_CMD -f docker-compose.prod.yml --env-file .env.production restart mariadb"
        ;;
        
    3)
        echo ""
        echo "🌐 建立 phpMyAdmin 配置文件..."
        
        mkdir -p ./phpmyadmin-config
        cat > ./phpmyadmin-config/config.user.inc.php <<'EOF'
<?php
// phpMyAdmin 自訂配置

$cfg['Servers'][1]['host'] = 'mariadb';
$cfg['Servers'][1]['port'] = '3306';
$cfg['Servers'][1]['connect_type'] = 'tcp';
$cfg['Servers'][1]['compress'] = false;
$cfg['Servers'][1]['AllowNoPassword'] = false;
$cfg['Servers'][1]['auth_type'] = 'cookie';

// 允許任意伺服器
$cfg['AllowArbitraryServer'] = true;

// 上傳限制
$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';
$cfg['MaxRows'] = 50;
$cfg['ProtectBinary'] = false;

// 增加超時時間
$cfg['ExecTimeLimit'] = 600;
$cfg['LoginCookieValidity'] = 14400;
?>
EOF
        
        echo "✅ phpMyAdmin 配置已建立: ./phpmyadmin-config/config.user.inc.php"
        echo ""
        echo "請在 docker-compose.prod.yml 中的 phpmyadmin 服務添加："
        echo ""
        echo "  volumes:"
        echo "    - ./phpmyadmin-config/config.user.inc.php:/etc/phpmyadmin/config.user.inc.php:ro"
        echo ""
        echo "然後執行: $COMPOSE_CMD -f docker-compose.prod.yml --env-file .env.production restart phpmyadmin"
        ;;
        
    4)
        echo ""
        echo "🐳 啟動 Adminer (輕量級資料庫管理工具)..."
        
        docker run -d \
            --name urban_renewal_adminer \
            --network urban_renewal_urban_renewal_network \
            -p 8888:8080 \
            --restart unless-stopped \
            adminer:latest
        
        echo "✅ Adminer 已啟動！"
        echo ""
        echo "訪問: http://$(hostname -I | awk '{print $1}'):8888"
        echo ""
        echo "登入資訊："
        echo "  系統: MySQL"
        echo "  伺服器: mariadb"
        echo "  用戶名: ${DB_USERNAME} 或 root"
        echo "  密碼: ${DB_PASSWORD} 或 ${DB_ROOT_PASSWORD}"
        echo "  資料庫: ${DB_DATABASE}"
        ;;
        
    5)
        echo ""
        echo "📊 使用命令行查看資料庫..."
        echo ""
        echo "以下是常用指令："
        echo ""
        echo "# 進入 MariaDB 命令行"
        echo "docker exec -it urban_renewal_db_prod mariadb -uroot -p${DB_ROOT_PASSWORD}"
        echo ""
        echo "# 顯示所有資料庫"
        echo "docker exec urban_renewal_db_prod mariadb -uroot -p${DB_ROOT_PASSWORD} -e 'SHOW DATABASES;'"
        echo ""
        echo "# 顯示 urban_renewal 的所有資料表"
        echo "docker exec urban_renewal_db_prod mariadb -uroot -p${DB_ROOT_PASSWORD} -e 'USE ${DB_DATABASE}; SHOW TABLES;'"
        echo ""
        
        read -p "是否要進入 MariaDB 命令行？(y/N) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            docker exec -it urban_renewal_db_prod mariadb -uroot -p${DB_ROOT_PASSWORD}
        fi
        ;;
        
    6)
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
