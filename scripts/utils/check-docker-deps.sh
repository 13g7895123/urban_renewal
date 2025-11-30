#!/bin/bash

# 快速檢查 Docker 容器依賴的便利腳本
# 使用方式: ./check-docker-deps.sh

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CONTAINER_NAME="urban_renewal_backend_dev"

echo "════════════════════════════════════════════════════════════"
echo "Urban Renewal Backend - Docker 依賴檢查"
echo "════════════════════════════════════════════════════════════"
echo ""

# 檢查容器是否運行
if ! docker ps | grep -q "$CONTAINER_NAME"; then
    echo "❌ 容器 '$CONTAINER_NAME' 未運行"
    echo ""
    echo "請先啟動容器:"
    echo "  docker compose -f docker-compose.dev.yml up -d"
    exit 1
fi

echo "✓ 找到容器: $CONTAINER_NAME"
echo ""

# 執行檢查
echo "正在執行依賴檢查..."
echo ""

docker exec "$CONTAINER_NAME" bash /var/www/html/check-dependencies.sh

EXIT_CODE=$?

echo ""
echo "════════════════════════════════════════════════════════════"

if [ $EXIT_CODE -eq 0 ]; then
    echo "✅ 依賴檢查完成 - 所有項目通過"
    echo ""
    echo "提示: 可以安全地啟動應用"
else
    echo "⚠️  依賴檢查完成 - 發現問題"
    echo ""
    echo "請參考上面的輸出了解詳細信息"
    echo "更多幫助: 查看 backend/README-DEPENDENCIES.md"
fi

echo "════════════════════════════════════════════════════════════"

exit $EXIT_CODE
