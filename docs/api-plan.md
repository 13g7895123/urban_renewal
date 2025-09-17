# 都市更新管理系統 API 規劃

## 現有 API 控制器
✅ **已完成**
1. `UrbanRenewalController` - 更新會管理
2. `PropertyOwnerController` - 所有權人管理
3. `LandPlotController` - 地號管理
4. `LocationController` - 行政區劃層級

## 需要新建的 API 控制器

### 1. 會議管理模組 (Meeting Management)

#### MeetingController
```
GET    /api/meetings                     # 取得會議列表
GET    /api/meetings/{id}                # 取得單一會議詳情
POST   /api/meetings                     # 建立新會議
PUT    /api/meetings/{id}                # 更新會議資料
DELETE /api/meetings/{id}                # 刪除會議
GET    /api/urban-renewals/{id}/meetings # 取得特定更新會的會議列表
```

#### MeetingAttendanceController
```
GET    /api/meetings/{id}/attendances              # 取得會議出席記錄
POST   /api/meetings/{id}/attendances/{ownerId}    # 會員報到
PUT    /api/meetings/{id}/attendances/{ownerId}    # 更新出席狀態
GET    /api/meetings/{id}/attendances/statistics   # 取得出席統計
POST   /api/meetings/{id}/attendances/export       # 匯出報到結果
```

#### MeetingObserverController
```
GET    /api/meetings/{id}/observers      # 取得列席者列表
POST   /api/meetings/{id}/observers      # 新增列席者
PUT    /api/meetings/{id}/observers/{id} # 更新列席者資訊
DELETE /api/meetings/{id}/observers/{id} # 刪除列席者
```

### 2. 投票管理模組 (Voting Management)

#### VotingTopicController
```
GET    /api/meetings/{id}/voting-topics           # 取得會議投票議題
GET    /api/voting-topics/{id}                    # 取得單一議題詳情
POST   /api/meetings/{id}/voting-topics           # 建立新議題
PUT    /api/voting-topics/{id}                    # 更新議題資料
DELETE /api/voting-topics/{id}                    # 刪除議題
PUT    /api/voting-topics/{id}/status             # 更新議題狀態 (draft/active/closed)
```

#### VotingController
```
GET    /api/voting-topics/{id}/voters             # 取得可投票人員列表
POST   /api/voting-topics/{id}/vote               # 執行投票
PUT    /api/voting-topics/{id}/vote/{ownerId}     # 修改投票
DELETE /api/voting-topics/{id}/vote/{ownerId}     # 取消投票
GET    /api/voting-topics/{id}/results            # 取得投票結果統計
GET    /api/voting-topics/{id}/results/export     # 匯出投票結果
```

### 3. 使用者認證模組 (Authentication)

#### AuthController
```
POST   /api/auth/login                    # 使用者登入
POST   /api/auth/logout                   # 使用者登出
POST   /api/auth/refresh                  # 刷新 token
GET    /api/auth/me                       # 取得當前使用者資訊
POST   /api/auth/forgot-password          # 忘記密碼
POST   /api/auth/reset-password           # 重設密碼
```

#### UserController
```
GET    /api/users                         # 取得使用者列表
GET    /api/users/{id}                    # 取得單一使用者
POST   /api/users                         # 建立新使用者
PUT    /api/users/{id}                    # 更新使用者資料
DELETE /api/users/{id}                    # 刪除使用者
PUT    /api/users/{id}/password           # 修改密碼
PUT    /api/users/{id}/permissions        # 更新權限
```

### 4. 系統設定模組 (System Settings)

#### SystemSettingsController
```
GET    /api/settings                      # 取得系統設定列表
GET    /api/settings/{key}                # 取得特定設定
PUT    /api/settings/{key}                # 更新系統設定
GET    /api/settings/public               # 取得公開設定
GET    /api/settings/categories           # 取得設定分類
```

#### NotificationController
```
GET    /api/notifications                 # 取得通知列表
POST   /api/notifications                 # 發送通知
PUT    /api/notifications/{id}/read       # 標記已讀
DELETE /api/notifications/{id}            # 刪除通知
GET    /api/notifications/unread-count    # 未讀通知數量
```

### 5. 文件管理模組 (Document Management)

#### DocumentController
```
GET    /api/meetings/{id}/documents       # 取得會議文件列表
POST   /api/meetings/{id}/documents       # 上傳會議文件
GET    /api/documents/{id}/download       # 下載文件
DELETE /api/documents/{id}                # 刪除文件
```

## API 回應格式標準

### 成功回應
```json
{
  "success": true,
  "data": {...},
  "message": "操作成功",
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 100,
    "total_pages": 10
  }
}
```

### 錯誤回應
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "驗證失敗",
    "details": {
      "field": ["錯誤訊息"]
    }
  }
}
```

## 認證與權限

### JWT Token 認證
- 使用 JWT Token 進行 API 認證
- Token 過期時間：24小時
- 支援 Token 刷新機制

### 權限控制
- **admin**: 系統管理員 - 完整權限
- **chairman**: 理事長 - 管理特定更新會
- **member**: 一般會員 - 檢視與投票權限
- **observer**: 列席者 - 僅檢視權限

## 錯誤處理

### HTTP 狀態碼
- 200: 操作成功
- 201: 資源建立成功
- 400: 請求參數錯誤
- 401: 未認證
- 403: 權限不足
- 404: 資源不存在
- 422: 驗證失敗
- 500: 伺服器錯誤

### 錯誤碼定義
- `VALIDATION_ERROR`: 資料驗證失敗
- `UNAUTHORIZED`: 未認證或認證失敗
- `FORBIDDEN`: 權限不足
- `NOT_FOUND`: 資源不存在
- `DUPLICATE_ENTRY`: 資料重複
- `BUSINESS_LOGIC_ERROR`: 業務邏輯錯誤

## API 開發優先順序

### 高優先級 (第一階段)
1. AuthController - 使用者認證
2. MeetingController - 會議基本管理
3. MeetingAttendanceController - 會員報到系統
4. VotingTopicController - 投票議題管理

### 中優先級 (第二階段)
1. VotingController - 投票執行系統
2. UserController - 使用者管理
3. SystemSettingsController - 系統設定

### 低優先級 (第三階段)
1. NotificationController - 通知系統
2. DocumentController - 文件管理
3. MeetingObserverController - 列席者管理

## 測試策略

### 單元測試
- 每個 Controller 都需要對應的測試檔案
- 測試涵蓋率目標：80% 以上

### 整合測試
- API 端點完整測試
- 資料庫交互測試
- 權限驗證測試

### 前後端整合測試
- API 與前端 Nuxt 3 應用整合測試
- 跨域請求 (CORS) 測試
- 錯誤處理測試