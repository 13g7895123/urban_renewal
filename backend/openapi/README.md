# 會議管理 API 文件

本目錄包含都更計票系統的會議管理 API 的 OpenAPI 3.0 規範文件。

## 文件說明

- `meetings-api.yaml` - 會議管理相關 API 的完整 OpenAPI 規範

## 如何使用 Swagger 文件

### 方法一：使用內建的 Swagger UI（推薦）✨

直接在瀏覽器中開啟：

```
http://localhost:9228/swagger-ui.html
```

或者先查看 API 文件首頁：

```
http://localhost:9228/api-docs.html
```

根據你的環境選擇：
- 開發環境（主要）: `http://localhost:9228/swagger-ui.html`
- 開發環境（前端）: `http://localhost:4002/swagger-ui.html`（如果前端代理了後端）
- 正式環境: `https://your-domain.com/swagger-ui.html`

**使用說明**:
1. 開啟上述網址
2. 會自動載入 API 文件
3. 點擊右上角的「Authorize」按鈕
4. 輸入你的 JWT Token（從登入 API 取得）
5. 就可以直接在頁面上測試 API 了！

**提示**:
- Token 會自動儲存在瀏覽器的 localStorage 中
- 也可以在 Console 中使用 `setAuthToken('your_token')` 設定 token
- 使用 `clearAuthToken()` 清除 token

### 方法二：使用線上 Swagger Editor

1. 前往 [Swagger Editor](https://editor.swagger.io/)
2. 將 `meetings-api.yaml` 的內容複製貼上到編輯器中
3. 編輯器會自動渲染 API 文件並提供互動式測試功能

### 方法三：使用 Docker 運行 Swagger UI

```bash
# 在專案根目錄執行
docker run -p 8080:8080 \
  -e SWAGGER_JSON=/openapi/meetings-api.yaml \
  -v $(pwd)/backend/openapi:/openapi \
  swaggerapi/swagger-ui
```

然後在瀏覽器中開啟 `http://localhost:8080`

## API 概覽

### 會議管理 (Meetings)

- `GET /api/meetings` - 取得會議列表（支援分頁和篩選）
- `POST /api/meetings` - 建立新會議
- `GET /api/meetings/{id}` - 取得會議詳情
- `PUT /api/meetings/{id}` - 更新會議資料
- `DELETE /api/meetings/{id}` - 刪除會議
- `PUT /api/meetings/{id}/status` - 更新會議狀態
- `GET /api/urban-renewals/{id}/meetings` - 取得特定更新會的會議列表

### 會議統計 (Meeting Statistics)

- `GET /api/meetings/{id}/statistics` - 取得會議統計資料（包含出席統計、投票議題、成會門檻）

### 會議匯出 (Meeting Export)

- `GET /api/meetings/{id}/export-notice` - 匯出會議通知（Word 格式）

## 認證方式

所有 API 都需要 JWT Bearer Token 認證。

在請求的 Header 中加入：
```
Authorization: Bearer {your_jwt_token}
```

### 取得 Token

```bash
curl -X POST http://localhost:4002/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "your_username",
    "password": "your_password"
  }'
```

回應範例：
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "expires_in": 3600
  }
}
```

## 測試 API

### 使用 curl

```bash
# 1. 登入取得 token
TOKEN=$(curl -s -X POST http://localhost:4002/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}' \
  | jq -r '.data.token')

# 2. 取得會議列表
curl -X GET "http://localhost:4002/api/meetings?page=1&per_page=10" \
  -H "Authorization: Bearer $TOKEN"

# 3. 取得會議詳情
curl -X GET "http://localhost:4002/api/meetings/1" \
  -H "Authorization: Bearer $TOKEN"

# 4. 建立新會議
curl -X POST "http://localhost:4002/api/meetings" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "urban_renewal_id": 6,
    "meeting_name": "114年度第一屆第4次會員大會",
    "meeting_type": "會員大會",
    "meeting_date": "2025-12-20",
    "meeting_time": "14:00",
    "meeting_location": "台北市南港區玉成街1號"
  }'

# 5. 更新會議狀態
curl -X PUT "http://localhost:4002/api/meetings/1/status" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "scheduled"}'

# 6. 取得會議統計
curl -X GET "http://localhost:4002/api/meetings/1/statistics" \
  -H "Authorization: Bearer $TOKEN"

# 7. 匯出會議通知
curl -X GET "http://localhost:4002/api/meetings/1/export-notice" \
  -H "Authorization: Bearer $TOKEN" \
  --output meeting_notice.docx
```

### 使用 Postman

1. 建立新的 Collection
2. 設定 Collection Variables:
   - `base_url`: `http://localhost:4002/api`
   - `token`: 登入後取得的 JWT token

3. 在 Collection 的 Authorization 標籤中設定：
   - Type: Bearer Token
   - Token: `{{token}}`

4. 匯入 `meetings-api.yaml` 或手動建立請求

## 權限說明

### Admin（系統管理員）
- 可以存取所有更新會的會議資料
- 可以建立、修改、刪除任何會議
- 可以查看所有統計資料

### Company Manager（公司管理者）
- 只能存取自己公司所屬更新會的會議資料
- 只能為自己的更新會建立會議
- 只能修改、刪除自己更新會的會議
- 只能查看自己更新會的統計資料

## 錯誤代碼說明

| 錯誤代碼 | HTTP Status | 說明 |
|---------|-------------|------|
| `UNAUTHORIZED` | 401 | 未授權，需要登入 |
| `FORBIDDEN` | 403 | 權限不足 |
| `NOT_FOUND` | 404 | 資源不存在 |
| `VALIDATION_ERROR` | 422 | 輸入驗證失敗 |
| `BUSINESS_LOGIC_ERROR` | 422 | 業務邏輯錯誤 |
| `INTERNAL_ERROR` | 500 | 伺服器內部錯誤 |

## 常見問題

### Q: 為什麼 API 回傳 401 錯誤？
A: 請檢查：
1. Token 是否已過期（預設 1 小時）
2. Authorization Header 格式是否正確
3. Token 是否有效

### Q: 為什麼無法建立會議？
A: 請檢查：
1. 必填欄位是否都已提供（urban_renewal_id, meeting_name, meeting_type, meeting_date, meeting_time）
2. meeting_type 是否為有效值（會員大會、理事會、監事會、臨時會議）
3. meeting_date 格式是否正確（YYYY-MM-DD）
4. 是否有該更新會的權限（company_manager）

### Q: 為什麼無法刪除會議？
A: 會議在以下情況無法刪除：
1. 會議狀態為 `in_progress` 或 `completed`
2. 會議有關聯的投票議題（需先刪除投票議題）

### Q: 如何變更會議狀態？
A: 會議狀態轉換規則：
- `draft` → `scheduled`, `cancelled`
- `scheduled` → `in_progress`, `cancelled`
- `in_progress` → `completed`, `cancelled`
- `completed` → （不能變更）
- `cancelled` → `draft`

## 相關資源

- [OpenAPI 3.0 規範](https://swagger.io/specification/)
- [Swagger UI 文件](https://swagger.io/tools/swagger-ui/)
- [Swagger Editor](https://editor.swagger.io/)
- [Postman](https://www.postman.com/)

## 更新日誌

### v1.0.0 (2025-11-08)
- 初始版本
- 包含所有會議管理 API
- 支援認證和權限控制
- 完整的錯誤處理和回應格式定義
