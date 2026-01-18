# 公司審核系統測試說明

## 功能概述

最後新增的功能是**使用者審核系統**（User Approval System），包含以下核心功能：

### 1. 公司邀請碼管理
- **產生邀請碼**: 公司管理者可以產生8位數的唯一邀請碼
- **取得邀請碼**: 查看當前公司的邀請碼及啟用狀態
- **邀請碼驗證**: 新使用者註冊時使用邀請碼加入公司

### 2. 使用者審核流程
- **待審核列表**: 公司管理者可查看所有待審核的使用者
- **核准使用者**: 審核通過後，使用者成為實質性帳號並啟用
- **拒絕使用者**: 拒絕申請，使用者帳號保持停用狀態
- **權限控制**: 只有公司管理者可以執行審核操作

### 3. 成員指派管理
- **查看可用成員**: 列出所有已核准的公司成員
- **指派成員到專案**: 將成員指派到特定的都市更新專案
- **查看專案成員**: 查看特定專案的所有成員
- **取消指派**: 從專案中移除成員

## 測試文件

測試文件位於: `backend/tests/app/Controllers/Api/CompanyApprovalTest.php`

### 測試涵蓋範圍

#### 邀請碼管理測試 (4個測試)
- ✅ `testGetInviteCodeSuccess` - 成功取得邀請碼
- ✅ `testGetInviteCodeWithoutPermission` - 無權限時拒絕存取
- ✅ `testGenerateInviteCodeSuccess` - 成功產生新邀請碼
- ✅ `testGenerateInviteCodeUpdatesDatabase` - 驗證資料庫更新

#### 待審核使用者測試 (3個測試)
- ✅ `testGetPendingUsersSuccess` - 成功取得待審核列表
- ✅ `testGetPendingUsersWithPagination` - 分頁功能正常
- ✅ `testGetPendingUsersDoesNotShowOtherCompanies` - 不顯示其他公司的使用者

#### 審核使用者測試 (3個測試)
- ✅ `testApproveUserSuccess` - 成功核准使用者
- ✅ `testRejectUserSuccess` - 成功拒絕使用者
- ✅ `testApproveUserFromDifferentCompanyFails` - 無法審核其他公司的使用者

#### 可用成員測試 (2個測試)
- ✅ `testGetAvailableMembersSuccess` - 成功取得可用成員列表
- ✅ `testGetAvailableMembersDoesNotContainSensitiveData` - 不包含敏感資料

#### 成員指派測試 (5個測試)
- ✅ `testAssignMemberToRenewalSuccess` - 成功指派成員
- ✅ `testAssignPendingUserFails` - 無法指派待審核使用者
- ✅ `testGetRenewalMembersSuccess` - 成功取得專案成員
- ✅ `testUnassignMemberFromRenewalSuccess` - 成功取消指派
- ✅ `testUnassignMemberFromOtherCompanyRenewalFails` - 無法操作其他公司的專案

#### 權限驗證測試 (2個測試)
- ✅ `testNonManagerCannotAccessApprovalEndpoints` - 非管理者無法存取
- ✅ `testUnauthenticatedUserCannotAccessApprovalEndpoints` - 未登入無法存取

**總計: 19個測試案例**

## 如何執行測試

### 方式一: 在 Docker 容器中執行

```bash
# 執行所有公司審核系統測試
docker exec urban_renewal_dev-backend-1 php vendor/bin/phpunit tests/app/Controllers/Api/CompanyApprovalTest.php

# 使用 testdox 格式顯示測試結果
docker exec urban_renewal_dev-backend-1 php vendor/bin/phpunit tests/app/Controllers/Api/CompanyApprovalTest.php --testdox

# 執行特定測試
docker exec urban_renewal_dev-backend-1 php vendor/bin/phpunit tests/app/Controllers/Api/CompanyApprovalTest.php --filter testApproveUserSuccess
```

### 方式二: 在本機執行 (需要 PHP 環境)

```bash
cd backend
php vendor/bin/phpunit tests/app/Controllers/Api/CompanyApprovalTest.php --testdox
```

### 測試前準備

確保資料庫遷移已執行:

```bash
docker exec urban_renewal_dev-backend-1 php spark migrate
```

## API 端點說明

### 邀請碼管理

#### 取得邀請碼
```http
GET /api/companies/me/invite-code
Authorization: Bearer {token}
```

**回應範例:**
```json
{
  "status": "success",
  "data": {
    "invite_code": "A1B2C3D4",
    "invite_code_active": 1
  }
}
```

#### 產生新邀請碼
```http
POST /api/companies/me/generate-invite-code
Authorization: Bearer {token}
```

**回應範例:**
```json
{
  "status": "success",
  "message": "邀請碼已更新",
  "data": {
    "invite_code": "X9Y8Z7W6"
  }
}
```

### 使用者審核

#### 取得待審核使用者列表
```http
GET /api/companies/me/pending-users?page=1&per_page=10
Authorization: Bearer {token}
```

**回應範例:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 123,
      "username": "john_doe",
      "email": "john@example.com",
      "full_name": "John Doe",
      "approval_status": "pending",
      "created_at": "2026-01-18 10:00:00"
    }
  ],
  "pager": {
    "current_page": 1,
    "total_pages": 3,
    "per_page": 10
  }
}
```

#### 審核使用者
```http
POST /api/companies/me/approve-user/{userId}
Authorization: Bearer {token}
Content-Type: application/json

{
  "action": "approve"  // 或 "reject"
}
```

**回應範例:**
```json
{
  "status": "success",
  "message": "已核准使用者申請"
}
```

### 成員指派

#### 取得可用成員列表
```http
GET /api/companies/me/available-members
Authorization: Bearer {token}
```

**回應範例:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 456,
      "username": "jane_smith",
      "full_name": "Jane Smith",
      "email": "jane@example.com",
      "approval_status": "approved",
      "is_substantive": 1
    }
  ]
}
```

#### 指派成員到專案
```http
POST /api/companies/me/renewals/{renewalId}/assign
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 456,
  "permissions": ["view", "edit"]
}
```

**回應範例:**
```json
{
  "status": "success",
  "message": "指派成功"
}
```

#### 取得專案成員列表
```http
GET /api/companies/me/renewals/{renewalId}/members
Authorization: Bearer {token}
```

#### 取消成員指派
```http
DELETE /api/companies/me/renewals/{renewalId}/members/{userId}
Authorization: Bearer {token}
```

**回應範例:**
```json
{
  "status": "success",
  "message": "已取消指派"
}
```

## 權限要求

所有審核相關的 API 端點都需要:
1. **已登入**: 需要有效的 Bearer Token
2. **公司管理者權限**: `is_company_manager = 1`
3. **企業使用者**: `user_type = 'enterprise'`

## 資料庫結構

### 新增欄位 (users 表)
- `company_invite_code` - 使用者註冊時使用的邀請碼
- `approval_status` - 審核狀態 (pending/approved/rejected)
- `approved_at` - 審核時間
- `approved_by` - 審核者 ID
- `is_substantive` - 是否為實質性帳號

### 新增欄位 (companies 表)
- `invite_code` - 公司邀請碼
- `invite_code_active` - 邀請碼是否啟用

### 新增資料表 (user_renewal_assignments)
- `id` - 主鍵
- `user_id` - 使用者 ID
- `urban_renewal_id` - 都市更新專案 ID
- `assigned_by` - 指派者 ID
- `assigned_at` - 指派時間
- `permissions` - 權限設定 (JSON)

## 使用流程範例

### 1. 公司管理者產生邀請碼
```bash
curl -X POST https://your-domain.com/api/companies/me/generate-invite-code \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 2. 新使用者使用邀請碼註冊
```bash
curl -X POST https://your-domain.com/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "new_user",
    "password": "password123",
    "email": "new@example.com",
    "company_invite_code": "A1B2C3D4"
  }'
```

### 3. 公司管理者查看待審核使用者
```bash
curl -X GET https://your-domain.com/api/companies/me/pending-users \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. 公司管理者核准使用者
```bash
curl -X POST https://your-domain.com/api/companies/me/approve-user/123 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"action": "approve"}'
```

### 5. 公司管理者指派成員到專案
```bash
curl -X POST https://your-domain.com/api/companies/me/renewals/456/assign \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 123,
    "permissions": ["view", "edit"]
  }'
```

## 相關文件

- 主要控制器: `backend/app/Controllers/Api/CompanyController.php`
- 使用者模型: `backend/app/Models/UserModel.php`
- 公司模型: `backend/app/Models/CompanyModel.php`
- 指派模型: `backend/app/Models/UserRenewalAssignmentModel.php`
- 資料庫遷移: 
  - `backend/app/Database/Migrations/2026-01-16-000001_UpdateUserAndCompanyForApprovalSystem.php`
  - `backend/app/Database/Migrations/2026-01-16-000002_CreateUserRenewalAssignmentsTable.php`

## 注意事項

1. **測試環境**: 測試會自動創建和清理測試資料，不會影響生產資料
2. **權限驗證**: 所有測試都包含權限驗證，確保安全性
3. **資料隔離**: 測試確保公司之間的資料完全隔離
4. **敏感資料**: API 回應會自動過濾密碼等敏感資料
