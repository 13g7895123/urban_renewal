# 使用者審核系統 - 測試與使用說明

## 📋 功能簡介

最後新增的功能是**使用者審核系統**，這是一個完整的企業帳號管理解決方案。

### 核心功能

1. **邀請碼系統** - 公司管理者可產生邀請碼供新成員註冊
2. **審核流程** - 管理者審核新註冊的使用者
3. **成員指派** - 將核准的成員指派到特定專案

## 🧪 測試文件

已為此功能創建完整的測試套件，包含 **19 個測試案例**:

- **測試文件**: `backend/tests/app/Controllers/Api/CompanyApprovalTest.php`
- **說明文件**: `backend/tests/app/Controllers/Api/README.md`

### 快速執行測試

```bash
# 在 Docker 容器中執行測試
docker exec urban_renewal_dev-backend-1 php vendor/bin/phpunit \
  tests/app/Controllers/Api/CompanyApprovalTest.php --testdox
```

## 🚀 如何使用

### 1. 產生邀請碼

公司管理者登入後，可以產生邀請碼:

```bash
POST /api/companies/me/generate-invite-code
Authorization: Bearer {管理者token}
```

回應:
```json
{
  "status": "success",
  "message": "邀請碼已更新",
  "data": {
    "invite_code": "A1B2C3D4"
  }
}
```

### 2. 新使用者註冊

新使用者使用邀請碼註冊:

```bash
POST /api/auth/register
Content-Type: application/json

{
  "username": "new_user",
  "password": "password123",
  "email": "user@example.com",
  "company_invite_code": "A1B2C3D4"
}
```

註冊後，使用者狀態為 `pending`（待審核）。

### 3. 查看待審核使用者

管理者查看待審核列表:

```bash
GET /api/companies/me/pending-users
Authorization: Bearer {管理者token}
```

回應:
```json
{
  "status": "success",
  "data": [
    {
      "id": 123,
      "username": "new_user",
      "email": "user@example.com",
      "approval_status": "pending",
      "created_at": "2026-01-18 10:00:00"
    }
  ]
}
```

### 4. 審核使用者

管理者核准或拒絕:

```bash
POST /api/companies/me/approve-user/123
Authorization: Bearer {管理者token}
Content-Type: application/json

{
  "action": "approve"  // 或 "reject"
}
```

核准後，使用者可以正常登入使用系統。

### 5. 指派成員到專案

將核准的成員指派到都市更新專案:

```bash
POST /api/companies/me/renewals/456/assign
Authorization: Bearer {管理者token}
Content-Type: application/json

{
  "user_id": 123,
  "permissions": ["view", "edit"]
}
```

## 📊 API 端點總覽

| 端點 | 方法 | 說明 | 權限 |
|------|------|------|------|
| `/api/companies/me/invite-code` | GET | 取得邀請碼 | 管理者 |
| `/api/companies/me/generate-invite-code` | POST | 產生新邀請碼 | 管理者 |
| `/api/companies/me/pending-users` | GET | 待審核列表 | 管理者 |
| `/api/companies/me/approve-user/{id}` | POST | 審核使用者 | 管理者 |
| `/api/companies/me/available-members` | GET | 可用成員列表 | 管理者 |
| `/api/companies/me/renewals/{id}/members` | GET | 專案成員列表 | 管理者 |
| `/api/companies/me/renewals/{id}/assign` | POST | 指派成員 | 管理者 |
| `/api/companies/me/renewals/{id}/members/{userId}` | DELETE | 取消指派 | 管理者 |

## 🔒 權限要求

所有 API 都需要:
- ✅ 已登入 (Bearer Token)
- ✅ 公司管理者權限 (`is_company_manager = 1`)
- ✅ 企業使用者類型 (`user_type = 'enterprise'`)

## 📁 相關文件

### 後端
- `backend/app/Controllers/Api/CompanyController.php` - 主要控制器
- `backend/app/Models/UserModel.php` - 使用者模型
- `backend/app/Models/CompanyModel.php` - 公司模型
- `backend/app/Models/UserRenewalAssignmentModel.php` - 指派模型

### 資料庫遷移
- `backend/app/Database/Migrations/2026-01-16-000001_UpdateUserAndCompanyForApprovalSystem.php`
- `backend/app/Database/Migrations/2026-01-16-000002_CreateUserRenewalAssignmentsTable.php`

### 前端
- `frontend/pages/tables/company-profile.vue` - 公司管理頁面
- `frontend/composables/useCompany.js` - 公司相關 API 呼叫
- `frontend/pages/signup.vue` - 註冊頁面（含邀請碼輸入）

### 測試
- `backend/tests/app/Controllers/Api/CompanyApprovalTest.php` - 完整測試套件
- `backend/tests/app/Controllers/Api/README.md` - 詳細測試說明

## 💡 使用場景

### 場景一: 新員工加入公司

1. HR 管理者產生邀請碼
2. 將邀請碼提供給新員工
3. 新員工使用邀請碼註冊
4. HR 管理者審核並核准
5. 專案經理將新員工指派到相關專案

### 場景二: 臨時協作者

1. 專案經理產生邀請碼
2. 外部協作者使用邀請碼註冊
3. 專案經理審核並核准
4. 指派到特定專案，設定有限權限
5. 專案結束後取消指派

## 🔍 測試涵蓋範圍

測試套件涵蓋以下情境:

✅ 邀請碼管理 (4個測試)
- 取得邀請碼
- 產生新邀請碼
- 權限驗證
- 資料庫更新驗證

✅ 使用者審核 (6個測試)
- 待審核列表
- 分頁功能
- 核准/拒絕操作
- 跨公司隔離
- 權限驗證

✅ 成員指派 (7個測試)
- 可用成員列表
- 指派操作
- 專案成員查詢
- 取消指派
- 權限驗證
- 敏感資料過濾

✅ 安全性測試 (2個測試)
- 未登入拒絕存取
- 非管理者拒絕存取

## 🎯 下一步

1. 執行測試確認功能正常
2. 根據需求調整權限設定
3. 自訂審核流程（如需要）
4. 整合通知系統（可選）

---

**提交訊息**: `feat: 新增使用者審核系統功能，包含後端模型、控制器、資料庫遷移、前端頁面及相關配置更新。`
