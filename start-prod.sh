#!/bin/bash
# 都更計票系統 - 正式環境啟動腳本
# Urban Renewal Voting System - Production Startup Script
#
# 此腳本會啟動完整的正式環境，包含：
#   - Frontend (前端服務)
#   - Backend (後端 API 服務)
#   - MariaDB (資料庫)
#   - phpMyAdmin (資料庫管理介面)
#   - Cron (定時任務服務)

set -e

echo "========================================="
echo "都更計票系統 - 正式環境啟動"
echo "Urban Renewal Voting System - Production"
echo "========================================="
echo ""

# 檢查 .env.production 檔案是否存在，不存在則自動創建
if [ ! -f .env.production ]; then
    echo "⚠️  找不到 .env.production 檔案"
    if [ -f .env.example ]; then
        echo "📝 自動從 .env.example 創建 .env.production..."
        cp .env.example .env.production
        echo "✅ 已創建 .env.production"
        echo ""
    else
        echo "❌ 錯誤：找不到 .env.example 檔案"
        echo "請確保專案檔案完整"
        exit 1
    fi
fi

# 載入環境變數 (正確處理 .env 檔案格式)
echo "📂 載入環境變數..."
set -a  # 自動 export 所有變數
while IFS='=' read -r key value || [ -n "$key" ]; do
    # 跳過空行和註解
    [[ -z "$key" || "$key" =~ ^[[:space:]]*# ]] && continue
    # 移除前後空白
    key=$(echo "$key" | xargs)
    value=$(echo "$value" | xargs)
    # 移除值的引號
    value="${value%\"}"
    value="${value#\"}"
    value="${value%\'}"
    value="${value#\'}"
    # 設定變數
    if [ -n "$key" ]; then
        export "$key=$value"
    fi
done < .env.production
set +a

# 檢查必要的環境變數
MISSING_VARS=()
[ -z "$FRONTEND_PORT" ] && MISSING_VARS+=("FRONTEND_PORT")
[ -z "$BACKEND_PORT" ] && MISSING_VARS+=("BACKEND_PORT")
[ -z "$DB_PORT" ] && MISSING_VARS+=("DB_PORT")
[ -z "$PHPMYADMIN_PORT" ] && MISSING_VARS+=("PHPMYADMIN_PORT")
[ -z "$DB_HOST" ] && MISSING_VARS+=("DB_HOST")
[ -z "$DB_DATABASE" ] && MISSING_VARS+=("DB_DATABASE")
[ -z "$DB_USERNAME" ] && MISSING_VARS+=("DB_USERNAME")
[ -z "$DB_PASSWORD" ] && MISSING_VARS+=("DB_PASSWORD")
[ -z "$DB_ROOT_PASSWORD" ] && MISSING_VARS+=("DB_ROOT_PASSWORD")
[ -z "$BACKEND_URL" ] && MISSING_VARS+=("BACKEND_URL")
[ -z "$BACKEND_API_URL" ] && MISSING_VARS+=("BACKEND_API_URL")

if [ ${#MISSING_VARS[@]} -gt 0 ]; then
    echo "❌ 錯誤：以下環境變數未設定："
    for var in "${MISSING_VARS[@]}"; do
        echo "   - $var"
    done
    echo ""
    echo "請檢查 .env.production 檔案，確保包含以下變數："
    echo "   FRONTEND_PORT, BACKEND_PORT, DB_PORT, PHPMYADMIN_PORT"
    echo "   DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, DB_ROOT_PASSWORD"
    echo "   BACKEND_URL, BACKEND_API_URL"
    exit 1
fi

echo "✅ 環境變數載入完成"
echo ""
echo "🔧 環境配置："
echo "  - 前端 Port: ${FRONTEND_PORT}"
echo "  - 後端 Port: ${BACKEND_PORT}"
echo "  - 資料庫 Port: ${DB_PORT}"
echo "  - phpMyAdmin Port: ${PHPMYADMIN_PORT}"
echo "  - 資料庫: ${DB_DATABASE}@${DB_HOST}"
echo ""

# 檢查 Docker 是否正在執行
if ! docker info > /dev/null 2>&1; then
    echo "❌ 錯誤：Docker 未運行"
    echo "請先啟動 Docker Desktop 或 Docker 服務"
    exit 1
fi

# 檢查並使用正確的 docker compose 命令
if docker compose version > /dev/null 2>&1; then
    DOCKER_COMPOSE="docker compose"
elif command -v docker-compose > /dev/null 2>&1; then
    DOCKER_COMPOSE="docker-compose"
else
    echo "❌ 錯誤：找不到 docker compose 或 docker-compose 命令"
    exit 1
fi

# 檢查容器是否正在運行
echo "🔍 檢查現有容器狀態..."
if $DOCKER_COMPOSE -f docker-compose.prod.yml ps --quiet 2>/dev/null | grep -q .; then
    echo "⚠️  發現正式環境容器正在運行，先停止現有服務..."
    $DOCKER_COMPOSE -f docker-compose.prod.yml --env-file .env.production down
    echo "✅ 現有服務已停止"
    echo ""
fi

echo "� 重建映像檔 (確保使用最新代碼)..."
if ! $DOCKER_COMPOSE -f docker-compose.prod.yml --env-file .env.production build; then
    echo "❌ 錯誤：映像建置失敗"
    echo "請檢查 Docker 日誌或網路連線"
    exit 1
fi

echo ""
echo "🚀 啟動 Docker Compose (Production Mode)..."
if ! $DOCKER_COMPOSE -f docker-compose.prod.yml --env-file .env.production up -d; then
    echo "❌ 錯誤：服務啟動失敗"
    echo "請執行以下命令查看詳細錯誤："
    echo "  docker compose -f docker-compose.prod.yml logs"
    exit 1
fi

echo ""
echo "⏳ 等待服務啟動..."
sleep 8

# 檢查服務是否正常運行
echo "🔍 檢查服務健康狀態..."
RETRY=0
MAX_RETRY=30

while [ $RETRY -lt $MAX_RETRY ]; do
    if $DOCKER_COMPOSE -f docker-compose.prod.yml ps --status running | grep -q "urban_renewal"; then
        echo "✅ 服務啟動成功"
        break
    fi
    echo "   等待服務啟動... ($((RETRY+1))/$MAX_RETRY)"
    sleep 2
    RETRY=$((RETRY+1))
done

if [ $RETRY -eq $MAX_RETRY ]; then
    echo "⚠️  警告：部分服務可能未正常啟動"
    echo "請執行以下命令檢查："
    echo "  docker compose -f docker-compose.prod.yml ps"
    echo "  docker compose -f docker-compose.prod.yml logs"
fi

echo ""
echo "========================================="
echo "✅ 部署完成！"
echo "========================================="
echo ""
echo "📊 服務存取資訊："

# 檢測是否在 VPS 上運行
if [ -n "$SSH_CONNECTION" ] || [ -n "$SSH_CLIENT" ]; then
    # 在 VPS 上，顯示 IP 地址
    SERVER_IP=$(hostname -I | awk '{print $1}')
    echo "  - 前端網站: http://${SERVER_IP}:${FRONTEND_PORT}"
    echo "  - 後端 API: http://${SERVER_IP}:${BACKEND_PORT}/api"
    echo "  - phpMyAdmin: http://${SERVER_IP}:${PHPMYADMIN_PORT}"
    echo "  - 資料庫連線: ${SERVER_IP}:${DB_PORT}"
else
    # 在本地運行
    echo "  - 前端網站: http://localhost:${FRONTEND_PORT}"
    echo "  - 後端 API: http://localhost:${BACKEND_PORT}/api"
    echo "  - phpMyAdmin: http://localhost:${PHPMYADMIN_PORT}"
    echo "  - 資料庫連線: localhost:${DB_PORT}"
fi

echo ""
echo "📝 常用指令："
echo "  - 查看服務狀態: docker compose -f docker-compose.prod.yml ps"
echo "  - 查看服務日誌: docker compose -f docker-compose.prod.yml logs -f"
echo "  - 重啟特定服務: docker compose -f docker-compose.prod.yml restart [service]"
echo "  - 停止所有服務: docker compose -f docker-compose.prod.yml down"
echo ""
echo "🔒 安全提醒："
echo "  - 請修改 .env.production 中的資料庫密碼"
echo "  - 建議設定防火牆規則"
echo "  - 考慮使用 Nginx 反向代理並設定 SSL"
echo ""
