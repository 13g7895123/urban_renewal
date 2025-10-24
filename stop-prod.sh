#!/bin/bash
# 都更計票系統 - 正式環境停止腳本
# Urban Renewal Voting System - Production Stop Script

set -e

echo "========================================="
echo "都更計票系統 - 正式環境停止"
echo "Urban Renewal Voting System - Production"
echo "========================================="
echo ""

# 檢查並使用正確的 docker compose 命令
if docker compose version > /dev/null 2>&1; then
    DOCKER_COMPOSE="docker compose"
elif command -v docker-compose > /dev/null 2>&1; then
    DOCKER_COMPOSE="docker-compose"
else
    echo "❌ 錯誤：找不到 docker compose 或 docker-compose 命令"
    exit 1
fi

echo "🛑 停止 Docker Compose 服務..."
$DOCKER_COMPOSE -f docker-compose.prod.yml --env-file .env down

echo ""
echo "✅ 所有服務已停止"
echo ""
echo "💡 提示："
echo "  - 重新啟動: ./start-prod.sh"
echo "  - 完全清除 (包含資料): docker-compose -f docker-compose.prod.yml down -v"
echo ""
