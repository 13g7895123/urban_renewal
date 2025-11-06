#!/bin/bash

# 快速建立企業管理者帳號腳本
# Urban Renewal System - Create Company Manager Account

set -e

# 顏色定義
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 預設值
DEFAULT_USERNAME="company_manager"
DEFAULT_PASSWORD="manager123"
DEFAULT_EMAIL="manager@company.com"
DEFAULT_FULL_NAME="企業管理者"

# 檢查參數
if [ "$1" == "--help" ] || [ "$1" == "-h" ]; then
    echo "==========================================="
    echo "企業管理者帳號建立工具"
    echo "==========================================="
    echo ""
    echo "使用方式："
    echo "  ./create-company-manager.sh [USERNAME] [PASSWORD] [EMAIL] [FULL_NAME]"
    echo ""
    echo "範例："
    echo "  ./create-company-manager.sh                                    # 使用預設值"
    echo "  ./create-company-manager.sh testmanager pass123                # 自訂帳密 (隨機產生 Email/名字)"
    echo "  ./create-company-manager.sh john john123 john@test.com John   # 完整自訂"
    echo ""
    echo "預設值："
    echo "  USERNAME:  $DEFAULT_USERNAME"
    echo "  PASSWORD:  $DEFAULT_PASSWORD"
    echo "  EMAIL:     $DEFAULT_EMAIL"
    echo "  FULL_NAME: $DEFAULT_FULL_NAME"
    echo ""
    exit 0
fi

# 取得參數或使用預設值
USERNAME="${1:-$DEFAULT_USERNAME}"
PASSWORD="${2:-$DEFAULT_PASSWORD}"

# 如果有提供帳號密碼但沒提供 Email,則隨機產生
if [ -n "$1" ] && [ -n "$2" ] && [ -z "$3" ]; then
    # 產生隨機 Email: username@隨機數字.com
    RANDOM_NUM=$(date +%s%N | md5sum | head -c 8)
    EMAIL="${USERNAME}@${RANDOM_NUM}.com"
    # 產生隨機名字: 使用帳號加上管理者
    FULL_NAME="${USERNAME}_管理者"
else
    EMAIL="${3:-$DEFAULT_EMAIL}"
    FULL_NAME="${4:-$DEFAULT_FULL_NAME}"
fi

echo -e "${BLUE}==========================================="
echo "企業管理者帳號建立工具"
echo "==========================================${NC}"
echo ""

# 顯示將要建立的帳號資訊
echo -e "${YELLOW}📋 帳號資訊：${NC}"
echo "  帳號 (username): $USERNAME"
echo "  密碼 (password): $PASSWORD"
echo "  Email:           $EMAIL"
echo "  姓名:            $FULL_NAME"
echo "  使用者類型:      enterprise (企業使用者)"
echo "  企業管理者:      是"
echo ""

# 檢查後端容器是否運行
if ! docker ps | grep -q "urban_renewal_backend_dev"; then
    echo -e "${RED}❌ 錯誤：後端服務未運行${NC}"
    echo "請先執行: ./start-dev.sh"
    exit 1
fi

# 讀取 .env 檔案
if [ -f .env ]; then
    export $(grep -v '^#' .env | grep -E 'DB_USERNAME|DB_PASSWORD|DB_DATABASE' | xargs)
fi

# 設定預設資料庫連線資訊
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-rootpassword}"
DB_DATABASE="${DB_DATABASE:-urban_renewal}"

# 建立 PHP 腳本來產生密碼雜湊
PASSWORD_HASH=$(docker exec urban_renewal_backend_dev php -r "echo password_hash('$PASSWORD', PASSWORD_DEFAULT);")

# 取得當前時間
CURRENT_TIME=$(date '+%Y-%m-%d %H:%M:%S')

echo -e "${BLUE}🚀 建立帳號中...${NC}"
echo ""

# 檢查帳號是否已存在
EXISTING_USER=$(docker exec urban_renewal_db_dev mariadb -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -N -e "SELECT COUNT(*) FROM users WHERE username='$USERNAME' OR email='$EMAIL';" 2>/dev/null)

if [ "$EXISTING_USER" != "0" ]; then
    echo -e "${RED}❌ 錯誤：帳號或 Email 已存在！${NC}"

    # 檢查是帳號還是 Email 重複
    USERNAME_EXISTS=$(docker exec urban_renewal_db_dev mariadb -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -N -e "SELECT COUNT(*) FROM users WHERE username='$USERNAME';" 2>/dev/null)
    EMAIL_EXISTS=$(docker exec urban_renewal_db_dev mariadb -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -N -e "SELECT COUNT(*) FROM users WHERE email='$EMAIL';" 2>/dev/null)

    if [ "$USERNAME_EXISTS" != "0" ]; then
        echo "   帳號 '$USERNAME' 已被使用"
    fi
    if [ "$EMAIL_EXISTS" != "0" ]; then
        echo "   Email '$EMAIL' 已被使用"
    fi
    exit 1
fi

# 插入新使用者
docker exec urban_renewal_db_dev mariadb -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -e "
INSERT INTO users (username, email, full_name, password_hash, role, user_type, is_company_manager, is_active, created_at, updated_at)
VALUES ('$USERNAME', '$EMAIL', '$FULL_NAME', '$PASSWORD_HASH', 'admin', 'enterprise', 1, 1, '$CURRENT_TIME', '$CURRENT_TIME');
" 2>/dev/null

if [ $? -eq 0 ]; then
    # 取得新建立的使用者 ID
    USER_ID=$(docker exec urban_renewal_db_dev mariadb -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -N -e "SELECT id FROM users WHERE username='$USERNAME';" 2>/dev/null)

    echo -e "${GREEN}✅ 企業管理者帳號建立成功！${NC}"
    echo ""
    echo -e "${YELLOW}📊 帳號詳細資訊：${NC}"
    echo "   使用者 ID: $USER_ID"
    echo "   帳號: $USERNAME"
    echo "   密碼: $PASSWORD"
    echo "   Email: $EMAIL"
    echo "   姓名: $FULL_NAME"
    echo "   角色: admin"
    echo "   使用者類型: enterprise"
    echo "   企業管理者: 是"
    echo ""
    echo -e "${BLUE}🔗 登入資訊：${NC}"
    echo "   前端登入頁面: http://localhost:3000/login (或 http://localhost:4001/login)"
    echo "   API 端點: http://localhost:9228/api/auth/login"
    echo ""
    echo -e "${GREEN}✨ 現在可以使用此帳號登入系統！${NC}"
    echo ""
    echo -e "${GREEN}==========================================="
    echo "✅ 完成！"
    echo "==========================================${NC}"
else
    echo -e "${RED}❌ 建立失敗${NC}"
    echo ""
    echo -e "${RED}==========================================="
    echo "❌ 建立失敗"
    echo "==========================================${NC}"
    exit 1
fi
