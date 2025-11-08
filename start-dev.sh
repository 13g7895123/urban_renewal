#!/bin/bash
# 都更計票系統 - 開發環境啟動腳本
# Urban Renewal Voting System - Development Startup Script
#
# 此腳本會啟動開發環境，包含：
#   - Backend (後端 API 服務)
#   - MariaDB (資料庫)
#   - phpMyAdmin (資料庫管理介面)
#   - Cron (定時任務服務)
#
# 注意：前端需要另外使用 npm run dev 啟動

set -e

echo "========================================="
echo "都更計票系統 - 開發環境啟動"
echo "Urban Renewal Voting System - Development"
echo "========================================="
echo ""

# 檢查 .env 檔案是否存在
if [ ! -f .env ]; then
    echo "❌ 錯誤：找不到 .env 檔案"
    echo "請先複製 .env.example 並設定環境變數："
    echo "  cp .env.example .env"
    exit 1
fi

# 載入環境變數
source .env

echo "🔧 環境配置："
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

# 檢查容器是否存在（包含 running 和 stopped）
echo "🔍 檢查現有容器狀態..."
CONTAINER_NAMES=(
    "urban_renewal_backend_dev"
    "urban_renewal_db_dev"
    "urban_renewal_phpmyadmin_dev"
    "urban_renewal_cron_dev"
)

FOUND_CONTAINERS=false
for container in "${CONTAINER_NAMES[@]}"; do
    if docker ps -a --format '{{.Names}}' | grep -q "^${container}$"; then
        FOUND_CONTAINERS=true
        STATUS=$(docker inspect -f '{{.State.Status}}' "$container" 2>/dev/null)
        echo "  ⚠️  發現容器: $container (狀態: $STATUS)"
    fi
done

if [ "$FOUND_CONTAINERS" = true ]; then
    echo ""
    echo "📦 停止並移除現有容器..."
    $DOCKER_COMPOSE -f docker-compose.dev.yml --env-file .env down

    # 額外檢查並強制移除任何殘留容器
    for container in "${CONTAINER_NAMES[@]}"; do
        if docker ps -a --format '{{.Names}}' | grep -q "^${container}$"; then
            echo "  🗑️  強制移除殘留容器: $container"
            docker rm -f "$container" 2>/dev/null || true
        fi
    done

    echo "✅ 現有服務已停止並清理"
    echo ""
fi

# 檢查 Port 是否被佔用
echo "🔍 檢查 Port 可用性..."
PORTS_IN_USE=false
for port in "${BACKEND_PORT}" "${DB_PORT}" "${PHPMYADMIN_PORT}"; do
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        echo "  ⚠️  Port $port 已被使用"
        PORTS_IN_USE=true
    fi
done

if [ "$PORTS_IN_USE" = true ]; then
    echo ""
    echo "⚠️  警告：部分 Port 已被佔用，可能導致啟動失敗"
    echo "提示：使用 'lsof -i :<port>' 查看佔用程序"
    echo ""
fi

echo "🚀 啟動 Docker Compose (Development Mode)..."
$DOCKER_COMPOSE -f docker-compose.dev.yml --env-file .env up -d

echo ""
echo "⏳ 等待服務啟動..."
sleep 5

echo ""
echo "✅ 後端服務啟動完成！"
echo ""
echo "📊 服務存取資訊："
echo "  - 後端 API: http://localhost:${BACKEND_PORT}/api"
echo "  - phpMyAdmin: http://localhost:${PHPMYADMIN_PORT}"
echo "  - 資料庫連線: localhost:${DB_PORT}"
echo ""
echo "📝 前端開發："
echo "  請在另一個終端視窗執行："
echo "    cd frontend"
echo "    npm install"
echo "    npm run dev"
echo "  前端通常會在 http://localhost:3000 啟動"
echo ""
echo "📝 常用指令："
echo "  - 查看服務狀態: docker-compose -f docker-compose.dev.yml ps"
echo "  - 查看服務日誌: docker-compose -f docker-compose.dev.yml logs -f"
echo "  - 停止所有服務: ./stop-dev.sh"
echo ""
