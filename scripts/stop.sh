#!/bin/bash

# Urban Renewal Voting System - 停止服務腳本
# 都更計票系統 - 停止服務

set -e

# 顏色定義
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 接收環境參數，預設為 dev
ENV=${1:-dev}

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  都更計票系統 - 停止服務${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# 檢查環境參數
if [[ "$ENV" != "production" && "$ENV" != "dev" ]]; then
    echo -e "${RED}❌ 錯誤：不支援的環境 '$ENV'${NC}"
    echo -e "${YELLOW}支援的環境：production, dev${NC}"
    exit 1
fi

echo -e "${YELLOW}📦 目標環境：${ENV}${NC}"
echo ""

# 檢查 docker-compose 檔案
COMPOSE_FILE="docker/docker-compose.$ENV.yml"
if [ ! -f "$COMPOSE_FILE" ]; then
    echo -e "${RED}❌ Docker Compose 檔案不存在: $COMPOSE_FILE${NC}"
    exit 1
fi

# 停止服務
echo -e "${BLUE}🛑 停止 Docker 服務...${NC}"
docker compose -f "$COMPOSE_FILE" down

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  ✓ 服務已停止！${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
