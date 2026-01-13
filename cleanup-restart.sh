#!/bin/bash
# Cleanup and Restart Production
# 清理並重啟生產環境

set -e

echo "========================================="
echo "🧹 清理舊容器並重啟"
echo "========================================="
echo ""

echo "步驟 1: 停止所有相關容器..."
docker stop urban_renewal_db_prod 2>/dev/null || echo "  容器未運行"
docker stop urban_renewal_backend_prod 2>/dev/null || echo "  容器未運行"
docker stop urban_renewal_frontend_prod 2>/dev/null || echo "  容器未運行"
docker stop urban_renewal_phpmyadmin_prod 2>/dev/null || echo "  容器未運行"
docker stop urban_renewal_cron_prod 2>/dev/null || echo "  容器未運行"
docker stop urban_renewal_db_temp 2>/dev/null || echo "  臨時容器未運行"

echo ""
echo "步驟 2: 移除舊容器..."
docker rm urban_renewal_db_prod 2>/dev/null || echo "  容器不存在"
docker rm urban_renewal_backend_prod 2>/dev/null || echo "  容器不存在"
docker rm urban_renewal_frontend_prod 2>/dev/null || echo "  容器不存在"
docker rm urban_renewal_phpmyadmin_prod 2>/dev/null || echo "  容器不存在"
docker rm urban_renewal_cron_prod 2>/dev/null || echo "  容器不存在"
docker rm urban_renewal_db_temp 2>/dev/null || echo "  臨時容器不存在"

echo ""
echo "步驟 3: 檢查 Docker Compose 命令..."
if command -v docker-compose &> /dev/null; then
    COMPOSE_CMD="docker-compose"
    echo "  使用: docker-compose"
else
    COMPOSE_CMD="docker compose"
    echo "  使用: docker compose"
fi

echo ""
echo "步驟 4: 啟動服務..."
$COMPOSE_CMD -f docker-compose.prod.yml --env-file .env.production up -d

echo ""
echo "步驟 5: 等待服務啟動..."
sleep 5

echo ""
echo "步驟 6: 檢查容器狀態..."
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep -E "urban_renewal|NAMES"

echo ""
echo "========================================="
echo "✅ 清理並重啟完成！"
echo "========================================="
echo ""
echo "💡 提示："
echo "  - 如需查看日誌: docker logs urban_renewal_db_prod"
echo "  - phpMyAdmin: https://urban-renewal.mercylife.cc/pma"
echo "  - 後端 API: https://urban-renewal.mercylife.cc/api"
