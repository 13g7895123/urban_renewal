# API 合約規範目錄

本目錄包含都更計票系統的 OpenAPI 3.0 規範文件，定義系統的 API 合約。

## 檔案說明

### 共用定義
- **common.yaml** - 共用的 schema 定義（錯誤回應、分頁、基礎欄位等）

### API 模組

#### 核心功能
- **auth.yaml** - 身份驗證 API (`/api/auth/*`)
  - 使用者登入、登出、Token 刷新
  - 密碼重設功能
  - 使用者資訊查詢

- **urban-renewals.yaml** - 都市更新會 API (`/api/urban-renewals/*`)
  - 更新會的 CRUD 操作
  - 關聯的土地資料、所有權人管理

- **meetings.yaml** - 會議 API (`/api/meetings/*`)
  - 會議的 CRUD 操作
  - 會議狀態管理
  - 會員報到管理

- **voting.yaml** - 投票 API (`/api/voting-topics/*` 和 `/api/voting/*`)
  - 投票議題管理
  - 投票表決功能
  - 投票結果統計

## 使用方式

### 檢視規範
可以使用以下工具檢視和測試 API 規範：

1. **Swagger Editor**
   - 線上版：https://editor.swagger.io/
   - 將 YAML 檔案內容貼上即可檢視

2. **Swagger UI**
   ```bash
   npx swagger-ui-watcher auth.yaml
   ```

3. **Redoc**
   ```bash
   npx @redocly/cli preview-docs auth.yaml
   ```

### 驗證規範
```bash
# 使用 swagger-cli 驗證
npx @apidevtools/swagger-cli validate auth.yaml

# 使用 openapi-generator
openapi-generator validate -i auth.yaml
```

### 產生客戶端程式碼
```bash
# 產生 JavaScript 客戶端
openapi-generator generate -i auth.yaml -g javascript -o ./generated/js-client

# 產生 TypeScript Axios 客戶端
openapi-generator generate -i auth.yaml -g typescript-axios -o ./generated/ts-client
```

## 規範版本

- **OpenAPI 版本**: 3.0.3
- **API 版本**: 1.0.0
- **最後更新**: 2025-10-08

## 伺服器環境

### 開發環境
- **URL**: http://localhost:4002
- **說明**: 本地開發環境

### 正式環境
- **URL**: https://api.urban-renewal.example.com
- **說明**: 正式生產環境（需配置）

## 安全性

所有需要身份驗證的 API 端點都使用 **Bearer Token (JWT)** 進行認證：

```http
Authorization: Bearer <JWT_TOKEN>
```

Token 有效期為 24 小時，可使用 Refresh Token 延長登入狀態。

## 回應格式

### 成功回應
```json
{
  "success": true,
  "data": { ... },
  "message": "操作成功"
}
```

### 錯誤回應
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "錯誤訊息",
    "details": { ... }
  }
}
```

### 分頁回應
```json
{
  "success": true,
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 100,
    "total_pages": 10
  },
  "message": "查詢成功"
}
```

## HTTP 狀態碼

- **200** - 成功
- **201** - 建立成功
- **204** - 刪除成功（無內容）
- **400** - 請求錯誤
- **401** - 未授權（需要登入）
- **403** - 禁止訪問（權限不足）
- **404** - 資源不存在
- **422** - 驗證錯誤
- **500** - 伺服器錯誤

## 變更紀錄

### 2025-10-08
- 初始版本建立
- 定義核心 API 模組規範
- 建立共用 schema 定義

## 貢獻指南

更新 API 規範時請遵循以下原則：

1. 所有說明使用正體中文
2. 遵循 RESTful 設計原則
3. 保持向後相容性
4. 更新變更紀錄
5. 驗證規範正確性

## 聯絡資訊

如有任何問題或建議，請聯絡開發團隊。
