#!/bin/bash

# 都更計票系統 - 會議 API 測試腳本
# 使用方式：./test-api.sh [command]
# 範例：./test-api.sh list

# 設定 API Base URL
BASE_URL="${API_BASE_URL:-http://localhost:4002/api}"

# 顏色設定
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 輔助函數：顯示訊息
info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# 檢查 jq 是否安裝
check_jq() {
    if ! command -v jq &> /dev/null; then
        error "jq 未安裝，請先安裝 jq"
        echo "Ubuntu/Debian: sudo apt-get install jq"
        echo "CentOS/RHEL: sudo yum install jq"
        echo "macOS: brew install jq"
        exit 1
    fi
}

# 登入取得 Token
login() {
    local username="${1:-admin}"
    local password="${2:-admin123}"

    info "登入中... (使用者: $username)"

    TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
        -H "Content-Type: application/json" \
        -d "{\"username\":\"$username\",\"password\":\"$password\"}" \
        | jq -r '.data.token // empty')

    if [ -z "$TOKEN" ]; then
        error "登入失敗，請檢查帳號密碼"
        return 1
    fi

    success "登入成功"
    info "Token: ${TOKEN:0:50}..."
    return 0
}

# 取得會議列表
list_meetings() {
    local page="${1:-1}"
    local per_page="${2:-10}"

    info "取得會議列表 (page=$page, per_page=$per_page)"

    curl -s -X GET "$BASE_URL/meetings?page=$page&per_page=$per_page" \
        -H "Authorization: Bearer $TOKEN" \
        | jq '.'
}

# 取得會議詳情
get_meeting() {
    local id="$1"

    if [ -z "$id" ]; then
        error "請提供會議 ID"
        echo "使用方式: $0 get <meeting_id>"
        return 1
    fi

    info "取得會議詳情 (ID: $id)"

    curl -s -X GET "$BASE_URL/meetings/$id" \
        -H "Authorization: Bearer $TOKEN" \
        | jq '.'
}

# 建立會議
create_meeting() {
    info "建立新會議"

    read -p "更新會 ID: " urban_renewal_id
    read -p "會議名稱: " meeting_name
    read -p "會議類型 (會員大會/理事會/監事會/臨時會議): " meeting_type
    read -p "會議日期 (YYYY-MM-DD): " meeting_date
    read -p "會議時間 (HH:MM): " meeting_time
    read -p "會議地點: " meeting_location

    curl -s -X POST "$BASE_URL/meetings" \
        -H "Authorization: Bearer $TOKEN" \
        -H "Content-Type: application/json" \
        -d "{
            \"urban_renewal_id\": $urban_renewal_id,
            \"meeting_name\": \"$meeting_name\",
            \"meeting_type\": \"$meeting_type\",
            \"meeting_date\": \"$meeting_date\",
            \"meeting_time\": \"$meeting_time\",
            \"meeting_location\": \"$meeting_location\"
        }" \
        | jq '.'
}

# 更新會議
update_meeting() {
    local id="$1"

    if [ -z "$id" ]; then
        error "請提供會議 ID"
        echo "使用方式: $0 update <meeting_id>"
        return 1
    fi

    info "更新會議 (ID: $id)"

    read -p "會議地點: " meeting_location

    curl -s -X PUT "$BASE_URL/meetings/$id" \
        -H "Authorization: Bearer $TOKEN" \
        -H "Content-Type: application/json" \
        -d "{\"meeting_location\": \"$meeting_location\"}" \
        | jq '.'
}

# 更新會議狀態
update_status() {
    local id="$1"
    local status="$2"

    if [ -z "$id" ] || [ -z "$status" ]; then
        error "請提供會議 ID 和狀態"
        echo "使用方式: $0 status <meeting_id> <status>"
        echo "狀態: draft, scheduled, in_progress, completed, cancelled"
        return 1
    fi

    info "更新會議狀態 (ID: $id, 狀態: $status)"

    curl -s -X PUT "$BASE_URL/meetings/$id/status" \
        -H "Authorization: Bearer $TOKEN" \
        -H "Content-Type: application/json" \
        -d "{\"status\": \"$status\"}" \
        | jq '.'
}

# 取得會議統計
get_statistics() {
    local id="$1"

    if [ -z "$id" ]; then
        error "請提供會議 ID"
        echo "使用方式: $0 stats <meeting_id>"
        return 1
    fi

    info "取得會議統計 (ID: $id)"

    curl -s -X GET "$BASE_URL/meetings/$id/statistics" \
        -H "Authorization: Bearer $TOKEN" \
        | jq '.'
}

# 匯出會議通知
export_notice() {
    local id="$1"
    local filename="${2:-meeting_notice_${id}.docx}"

    if [ -z "$id" ]; then
        error "請提供會議 ID"
        echo "使用方式: $0 export <meeting_id> [filename]"
        return 1
    fi

    info "匯出會議通知 (ID: $id)"

    curl -X GET "$BASE_URL/meetings/$id/export-notice" \
        -H "Authorization: Bearer $TOKEN" \
        -o "$filename"

    if [ $? -eq 0 ]; then
        success "匯出成功: $filename"
    else
        error "匯出失敗"
    fi
}

# 刪除會議
delete_meeting() {
    local id="$1"

    if [ -z "$id" ]; then
        error "請提供會議 ID"
        echo "使用方式: $0 delete <meeting_id>"
        return 1
    fi

    warning "即將刪除會議 ID: $id"
    read -p "確定要刪除嗎？(y/N) " confirm

    if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
        info "取消刪除"
        return 0
    fi

    info "刪除會議 (ID: $id)"

    curl -s -X DELETE "$BASE_URL/meetings/$id" \
        -H "Authorization: Bearer $TOKEN" \
        | jq '.'
}

# 顯示使用說明
show_help() {
    echo "都更計票系統 - 會議 API 測試腳本"
    echo ""
    echo "使用方式: $0 [command] [arguments]"
    echo ""
    echo "可用指令:"
    echo "  login [username] [password]    登入（預設: admin/admin123）"
    echo "  list [page] [per_page]         取得會議列表"
    echo "  get <id>                       取得會議詳情"
    echo "  create                         建立新會議（互動式）"
    echo "  update <id>                    更新會議"
    echo "  status <id> <status>           更新會議狀態"
    echo "  stats <id>                     取得會議統計"
    echo "  export <id> [filename]         匯出會議通知"
    echo "  delete <id>                    刪除會議"
    echo "  help                           顯示此說明"
    echo ""
    echo "環境變數:"
    echo "  API_BASE_URL                   API 基礎 URL（預設: http://localhost:4002/api）"
    echo ""
    echo "範例:"
    echo "  $0 login admin admin123        使用 admin 帳號登入"
    echo "  $0 list 1 10                   取得第 1 頁的會議列表，每頁 10 筆"
    echo "  $0 get 1                       取得 ID 為 1 的會議詳情"
    echo "  $0 status 1 scheduled          將會議 1 的狀態更新為 scheduled"
    echo "  $0 stats 1                     取得會議 1 的統計資料"
    echo "  $0 export 1 notice.docx        匯出會議 1 的通知到 notice.docx"
    echo ""
}

# 主程式
main() {
    check_jq

    local command="${1:-help}"
    shift

    # 除了 help 和 login 以外的指令都需要先登入
    if [ "$command" != "help" ] && [ "$command" != "login" ]; then
        if ! login; then
            exit 1
        fi
    fi

    case "$command" in
        login)
            login "$@"
            ;;
        list)
            list_meetings "$@"
            ;;
        get)
            get_meeting "$@"
            ;;
        create)
            create_meeting
            ;;
        update)
            update_meeting "$@"
            ;;
        status)
            update_status "$@"
            ;;
        stats)
            get_statistics "$@"
            ;;
        export)
            export_notice "$@"
            ;;
        delete)
            delete_meeting "$@"
            ;;
        help|--help|-h)
            show_help
            ;;
        *)
            error "未知的指令: $command"
            echo ""
            show_help
            exit 1
            ;;
    esac
}

# 執行主程式
main "$@"
