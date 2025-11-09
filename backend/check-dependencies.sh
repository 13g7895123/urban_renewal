#!/bin/bash

# Color codes for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Counter
PASSED=0
FAILED=0
WARNING=0

echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}Docker 容器依賴檢查工具${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}\n"

# Function to check command exists
check_command() {
    if command -v $1 &> /dev/null; then
        echo -e "${GREEN}✓${NC} $1 已安裝"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗${NC} $1 未安裝"
        ((FAILED++))
        return 1
    fi
}

# Function to check PHP extension
check_php_extension() {
    if php -m | grep -q "^${1}$"; then
        echo -e "${GREEN}✓${NC} PHP extension: $1"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗${NC} PHP extension: $1 未安裝"
        ((FAILED++))
        return 1
    fi
}

# Function to check composer package
check_composer_package() {
    # Try composer show first
    VERSION=$(composer show $1 2>/dev/null | grep "versions" | awk '{print $NF}')
    if [ ! -z "$VERSION" ]; then
        echo -e "${GREEN}✓${NC} Composer package: $1 ($VERSION)"
        ((PASSED++))
        return 0
    # Fallback to directory check
    elif [ -d "vendor/$1" ]; then
        echo -e "${GREEN}✓${NC} Composer package: $1 (已安裝)"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗${NC} Composer package: $1 未安裝"
        ((FAILED++))
        return 1
    fi
}

# Function to check directory exists
check_directory() {
    if [ -d "$1" ]; then
        SIZE=$(du -sh "$1" | cut -f1)
        echo -e "${GREEN}✓${NC} 目錄: $1 ($SIZE)"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗${NC} 目錄: $1 不存在"
        ((FAILED++))
        return 1
    fi
}

echo -e "${BLUE}1. PHP 版本檢查${NC}"
echo "─────────────────────"
PHP_VERSION=$(php -v | head -n1)
echo -e "${GREEN}✓${NC} $PHP_VERSION"
((PASSED++))
echo ""

echo -e "${BLUE}2. 系統指令檢查${NC}"
echo "─────────────────────"
check_command "git"
check_command "curl"
check_command "zip"
check_command "unzip"
check_command "mysql"
check_command "composer"
echo ""

echo -e "${BLUE}3. PHP 必要擴展檢查${NC}"
echo "─────────────────────"
check_php_extension "pdo_mysql"
check_php_extension "mysqli"
check_php_extension "mbstring"
check_php_extension "exif"
check_php_extension "pcntl"
check_php_extension "bcmath"
check_php_extension "gd"
check_php_extension "intl"
check_php_extension "zip"
echo ""

echo -e "${BLUE}4. PHP 建議擴展檢查${NC}"
echo "─────────────────────"
if php -m | grep -q "^curl$"; then
    echo -e "${GREEN}✓${NC} PHP extension: curl"
    ((PASSED++))
else
    echo -e "${YELLOW}⚠${NC} PHP extension: curl (可選)"
    ((WARNING++))
fi

if php -m | grep -q "^openssl$"; then
    echo -e "${GREEN}✓${NC} PHP extension: openssl"
    ((PASSED++))
else
    echo -e "${YELLOW}⚠${NC} PHP extension: openssl (可選)"
    ((WARNING++))
fi

if php -m | grep -q "^dom$"; then
    echo -e "${GREEN}✓${NC} PHP extension: dom"
    ((PASSED++))
else
    echo -e "${YELLOW}⚠${NC} PHP extension: dom (可選)"
    ((WARNING++))
fi

if php -m | grep -q "^json$"; then
    echo -e "${GREEN}✓${NC} PHP extension: json"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} PHP extension: json"
    ((FAILED++))
fi

if php -m | grep -q "^xml$"; then
    echo -e "${GREEN}✓${NC} PHP extension: xml"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} PHP extension: xml"
    ((FAILED++))
fi
echo ""

echo -e "${BLUE}5. Composer 主要套件檢查${NC}"
echo "─────────────────────"

# Main packages
PACKAGES=(
    "codeigniter4/framework"
    "firebase/php-jwt"
    "phpoffice/phpspreadsheet"
)

for pkg in "${PACKAGES[@]}"; do
    check_composer_package "$pkg"
done

# Special handling for phpword - check both directory and loadable class
PHPWORD_LOADED=false
if php -r "use PhpOffice\PhpWord\TemplateProcessor; echo 'OK';" 2>/dev/null | grep -q "OK"; then
    PHPWORD_LOADED=true
fi

if [ -d "vendor/phpoffice/phpword" ] || [ "$PHPWORD_LOADED" = true ]; then
    echo -e "${GREEN}✓${NC} Composer package: phpoffice/phpword (已安裝)"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Composer package: phpoffice/phpword 未安裝"
    ((FAILED++))
fi

if [ -d "vendor/phpoffice/math" ] || [ "$PHPWORD_LOADED" = true ]; then
    echo -e "${GREEN}✓${NC} Composer package: phpoffice/math (已安裝)"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Composer package: phpoffice/math 未安裝"
    ((FAILED++))
fi

echo ""

echo -e "${BLUE}6. 目錄結構檢查${NC}"
echo "─────────────────────"
check_directory "vendor"
check_directory "vendor/bin"
check_directory "app"
check_directory "public"
check_directory "writable"
check_directory "writable/cache"
check_directory "writable/logs"
check_directory "writable/session"
check_directory "writable/uploads"
echo ""

echo -e "${BLUE}7. 檔案權限檢查${NC}"
echo "─────────────────────"
if [ -w "writable" ]; then
    echo -e "${GREEN}✓${NC} writable 目錄可寫"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} writable 目錄不可寫"
    ((FAILED++))
fi

if [ -r ".env" ] 2>/dev/null; then
    echo -e "${GREEN}✓${NC} .env 檔案存在且可讀"
    ((PASSED++))
else
    echo -e "${YELLOW}⚠${NC} .env 檔案不存在或不可讀"
    ((WARNING++))
fi
echo ""

echo -e "${BLUE}8. 特定服務檢查${NC}"
echo "─────────────────────"

# Check if TemplateProcessor can be imported
if php -r "use PhpOffice\PhpWord\TemplateProcessor; echo 'OK';" 2>/dev/null | grep -q "OK"; then
    echo -e "${GREEN}✓${NC} PhpOffice\PhpWord\TemplateProcessor 可用"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} PhpOffice\PhpWord\TemplateProcessor 無法導入"
    ((FAILED++))
fi

# Check if CodeIgniter can be loaded
if php -r "require_once 'vendor/autoload.php'; echo 'OK';" 2>/dev/null | grep -q "OK"; then
    echo -e "${GREEN}✓${NC} Composer autoloader 正常"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Composer autoloader 異常"
    ((FAILED++))
fi

# Check JWT library
if php -r "use Firebase\JWT\JWT; echo 'OK';" 2>/dev/null | grep -q "OK"; then
    echo -e "${GREEN}✓${NC} Firebase\JWT\JWT 可用"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Firebase\JWT\JWT 無法導入"
    ((FAILED++))
fi

echo ""
echo -e "${BLUE}9. 資料庫連線檢查${NC}"
echo "─────────────────────"

if [ -z "$DB_HOST" ] || [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ]; then
    echo -e "${YELLOW}⚠${NC} 資料庫環境變數未設定，跳過連線測試"
    ((WARNING++))
else
    if php -r "try { new PDO('mysql:host=${DB_HOST};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}'); echo 'OK'; } catch (Exception \$e) { echo 'FAIL'; }" 2>/dev/null | grep -q "OK"; then
        echo -e "${GREEN}✓${NC} 資料庫連線成功"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} 資料庫連線失敗"
        ((FAILED++))
    fi
fi

echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}檢查結果摘要${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "✓ 通過: ${GREEN}${PASSED}${NC}"
echo -e "✗ 失敗: ${RED}${FAILED}${NC}"
echo -e "⚠ 警告: ${YELLOW}${WARNING}${NC}"

if [ $FAILED -eq 0 ]; then
    echo -e "\n${GREEN}所有關鍵依賴已安裝！容器狀態良好。${NC}\n"
    exit 0
else
    echo -e "\n${RED}發現 $FAILED 個未安裝的依賴，請檢查上方的詳細信息。${NC}\n"
    exit 1
fi
