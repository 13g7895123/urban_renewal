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

# 檢查 .env.production 檔案是否存在
if [ ! -f .env.production ]; then
    echo "❌ 錯誤：找不到 .env.production 檔案"
    echo "請先複製 .env.example 並設定環境變數："
    echo "  cp .env.example .env.production"
    exit 1
fi

# 載入環境變數
source .env.production

echo "🔧 環境配置："
echo "  - 前端 Port: ${FRONTEND_PORT}"
echo "  - 後端 Port: ${BACKEND_PORT}"
echo "  - 資料庫 Port: ${DB_PORT}"
echo "  - phpMyAdmin Port: ${PHPMYADMIN_PORT}"
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
$DOCKER_COMPOSE -f docker-compose.prod.yml --env-file .env.production build

echo ""
echo "�🚀 啟動 Docker Compose (Production Mode)..."
$DOCKER_COMPOSE -f docker-compose.prod.yml --env-file .env.production up -d

echo ""
echo "⏳ 等待服務啟動..."
sleep 5

echo ""
echo "✅ 服務啟動完成！"
echo ""
echo "📊 服務存取資訊："
echo "  - 前端網站: http://localhost:${FRONTEND_PORT}"
echo "  - 後端 API: http://localhost:${BACKEND_PORT}/api"
echo "  - phpMyAdmin: http://localhost:${PHPMYADMIN_PORT}"
echo "  - 資料庫連線: localhost:${DB_PORT}"
echo ""
echo "📝 常用指令："
echo "  - 查看服務狀態: docker-compose -f docker-compose.prod.yml ps"
echo "  - 查看服務日誌: docker-compose -f docker-compose.prod.yml logs -f"
echo "  - 停止所有服務: ./stop-prod.sh"
echo ""
