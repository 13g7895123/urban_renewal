#!/bin/bash

# 測試執行腳本
# 用法:
#   ./test.sh frontend  - 執行前端測試
#   ./test.sh backend   - 執行後端測試
#   ./test.sh all       - 執行所有測試

set -e

# 顏色輸出
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 函數：顯示使用說明
show_usage() {
    echo -e "${BLUE}測試執行腳本${NC}"
    echo ""
    echo "用法:"
    echo "  ./test.sh frontend    執行前端測試"
    echo "  ./test.sh backend     執行後端測試"
    echo "  ./test.sh all         執行所有測試"
    echo ""
}

# 函數：執行前端測試
run_frontend_tests() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}執行前端測試${NC}"
    echo -e "${BLUE}========================================${NC}"
    echo ""
    
    cd frontend
    
    # 檢查 node_modules 是否存在
    if [ ! -d "node_modules" ]; then
        echo -e "${YELLOW}node_modules 不存在，正在安裝依賴...${NC}"
        npm install
    fi
    
    # 執行測試
    echo -e "${GREEN}開始執行 Vitest 測試...${NC}"
    npm run test:run
    
    local exit_code=$?
    cd ..
    
    if [ $exit_code -eq 0 ]; then
        echo -e "${GREEN}✓ 前端測試通過${NC}"
        return 0
    else
        echo -e "${RED}✗ 前端測試失敗${NC}"
        return 1
    fi
}

# 函數：執行後端測試
run_backend_tests() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}執行後端測試${NC}"
    echo -e "${BLUE}========================================${NC}"
    echo ""
    
    # 檢查是否在 Docker 環境中
    if docker ps | grep -q "urban_renewal_dev-backend"; then
        echo -e "${GREEN}在 Docker 容器中執行測試...${NC}"
        
        # 檢查測試資料庫
        echo -e "${YELLOW}檢查測試資料庫...${NC}"
        docker exec urban_renewal_dev-backend-1 php -r "
            \$mysqli = new mysqli('mariadb', 'root', getenv('DB_PASSWORD') ?: 'password', '');
            if (!\$mysqli->connect_error) {
                \$result = \$mysqli->query(\"SHOW DATABASES LIKE 'urban_renewal_test'\");
                if (\$result->num_rows == 0) {
                    echo 'Creating test database...\n';
                    \$mysqli->query(\"CREATE DATABASE urban_renewal_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci\");
                }
            }
            \$mysqli->close();
        "
        
        # 在容器中執行 PHPUnit
        docker exec urban_renewal_dev-backend-1 bash -c "cd /var/www/html && ./vendor/bin/phpunit"
        local exit_code=$?
        
    else
        echo -e "${GREEN}在本地執行測試...${NC}"
        cd backend
        
        # 檢查 vendor 是否存在
        if [ ! -d "vendor" ]; then
            echo -e "${YELLOW}vendor 不存在，正在安裝依賴...${NC}"
            composer install
        fi
        
        # 執行測試
        ./vendor/bin/phpunit
        local exit_code=$?
        cd ..
    fi
    
    if [ $exit_code -eq 0 ]; then
        echo -e "${GREEN}✓ 後端測試通過${NC}"
        return 0
    else
        echo -e "${RED}✗ 後端測試失敗${NC}"
        return 1
    fi
}

# 主程式
main() {
    if [ $# -eq 0 ]; then
        show_usage
        exit 1
    fi
    
    case "$1" in
        frontend|front|f)
            run_frontend_tests
            exit $?
            ;;
        backend|back|b)
            run_backend_tests
            exit $?
            ;;
        all|a)
            echo -e "${BLUE}========================================${NC}"
            echo -e "${BLUE}執行所有測試${NC}"
            echo -e "${BLUE}========================================${NC}"
            echo ""
            
            frontend_result=0
            backend_result=0
            
            run_frontend_tests || frontend_result=$?
            echo ""
            run_backend_tests || backend_result=$?
            
            echo ""
            echo -e "${BLUE}========================================${NC}"
            echo -e "${BLUE}測試結果總結${NC}"
            echo -e "${BLUE}========================================${NC}"
            
            if [ $frontend_result -eq 0 ]; then
                echo -e "${GREEN}✓ 前端測試: 通過${NC}"
            else
                echo -e "${RED}✗ 前端測試: 失敗${NC}"
            fi
            
            if [ $backend_result -eq 0 ]; then
                echo -e "${GREEN}✓ 後端測試: 通過${NC}"
            else
                echo -e "${RED}✗ 後端測試: 失敗${NC}"
            fi
            
            if [ $frontend_result -eq 0 ] && [ $backend_result -eq 0 ]; then
                echo -e "${GREEN}所有測試通過！${NC}"
                exit 0
            else
                echo -e "${RED}部分測試失敗${NC}"
                exit 1
            fi
            ;;
        help|h|-h|--help)
            show_usage
            exit 0
            ;;
        *)
            echo -e "${RED}錯誤: 未知的參數 '$1'${NC}"
            echo ""
            show_usage
            exit 1
            ;;
    esac
}

main "$@"
