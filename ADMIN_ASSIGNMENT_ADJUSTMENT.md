# 分配管理者功能調整 - 一對多架構適配

**調整日期**：2025-11-15  
**最後修正**：2025-11-15 12:29 UTC
**調整版本**：2.0  
**狀態**：✅ 完成

---

## 📋 概述

根據企業與更新會一對多架構的調整建議，已更新分配管理者功能的權限檢查邏輯，從基於 `urban_renewal_id` 改為基於 `company_id`。

**重要修正**：API 返回格式已統一，與 `/api/users` 保持一致。

---

## 🔄 關鍵變更

### 1. 權限檢查邏輯調整

#### 舊邏輯（一對一架構）
```php
// 企業管理者只能分配自己所屬的更新會
if (!$isAdmin && $isCompanyManager) {
    $userUrbanRenewalId = $user['urban_renewal_id'] ?? null;
    
    foreach ($data['assignments'] as $urbanRenewalId => $adminId) {
        if ((int)$urbanRenewalId !== (int)$userUrbanRenewalId) {
            // 權限不足
        }
    }
}
```

#### 新邏輯（一對多架構）
```php
// 企業管理者只能分配自己公司所屬的更新會
if (!$isAdmin && $isCompanyManager) {
    // 新架構：取得用戶的 company_id
    $userCompanyId = $user['company_id'] ?? null;
    
    // 過渡期兼容：從 urban_renewal_id 推導 company_id
    if (!$userCompanyId && isset($user['urban_renewal_id'])) {
        $existingRenewal = $this->urbanRenewalModel->find($user['urban_renewal_id']);
        if ($existingRenewal && $existingRenewal['company_id']) {
            $userCompanyId = $existingRenewal['company_id'];
        }
    }

    foreach ($data['assignments'] as $urbanRenewalId => $adminId) {
        $renewal = $this->urbanRenewalModel->find($urbanRenewalId);
        if ((int)$renewal['company_id'] !== (int)$userCompanyId) {
            // 權限不足
        }
    }
}
```

**關鍵改進**：
- ✅ 使用 `company_id` 進行權限檢查
- ✅ 支持過渡期 `urban_renewal_id` 推導
- ✅ 驗證更新會存在性
- ✅ 返回更清晰的錯誤訊息

---

### 2. 管理者列表查詢調整（已修正）

#### 舊邏輯（按 urban_renewal_id 分組）
```php
$groupedManagers = [];
foreach ($managers as $manager) {
    $renewalId = $manager['urban_renewal_id'];
    $groupedManagers[$renewalId][] = $manager;
}
// 返回：{ renewalId: [managers] }
```

#### 新邏輯（與 /api/users 格式一致）
```php
// 查詢同一企業下的所有管理者
$filters = [
    'company_id' => $userCompanyId,
    'is_company_manager' => 1,
    'is_active' => 1,
    'user_type' => 'enterprise'
];

$managers = $userModel->getUsers($page, $perPage, $filters);

// 移除敏感資訊
$managers = array_map(function($userData) {
    unset($userData['password_hash'], $userData['password_reset_token']);
    return $userData;
}, $managers);

// 返回格式與 /api/users 一致
return [
    'managers' => $managers,
    'pager' => $userModel->pager->getDetails()
];
```

**關鍵改進**：
- ✅ **重要修正**：API 返回格式與 `/api/users` 統一
- ✅ 同一企業的所有更新會顯示相同的管理者列表
- ✅ 一個管理者可以分配給公司的任何更新會
- ✅ 支持分頁和過濾（與 `/api/users` 一致）
- ✅ 移除敏感資訊（password_hash, password_reset_token）

---

## 🔄 API 返回格式對比

### /api/users (現有)
```json
{
  "status": "success",
  "message": "使用者列表",
  "data": {
    "users": [
      { "id": 1, "full_name": "王大明", "email": "wang@example.com", ... },
      { "id": 2, "full_name": "李小華", "email": "li@example.com", ... }
    ],
    "pager": { "current_page": 1, "per_page": 100, "total": 2, "total_pages": 1 }
  }
}
```

### /api/urban-renewals/company-managers (新)
```json
{
  "status": "success",
  "message": "企業管理者列表",
  "data": {
    "managers": [
      { "id": 1, "full_name": "王大明", "email": "wang@example.com", ... },
      { "id": 2, "full_name": "李小華", "email": "li@example.com", ... }
    ],
    "pager": { "current_page": 1, "per_page": 100, "total": 2, "total_pages": 1 }
  }
}
```

**差異說明**：
- 結構完全一致，只是 key 名稱改為 `managers`
- 支持分頁和過濾
- 返回相同的用戶欄位信息

---

## 🗄️ 數據庫調整

### 新增遷移文件

**文件**：`backend/app/Database/Migrations/2025-11-15-000002_AddCompanyIdToUsersTable.php`

**變更**：
- ✅ 添加 `users.company_id` 欄位
  - 類型：INT UNSIGNED
  - 允許 NULL
  - 外鍵：關聯到 `companies.id`
- ✅ 添加外鍵約束 `fk_users_company_id`
  - ON DELETE SET NULL
  - ON UPDATE CASCADE
- ✅ 添加索引 `idx_company_id`

**狀態**：✅ 已執行，遷移成功

---

## 📝 代碼變更

### 1. UrbanRenewalController.php

#### getCompanyManagers() 方法（已修正）
- **變更**：改為返回與 `/api/users` 相同格式的數據
- **邏輯**：
  - 取得用戶的 company_id（新架構）或推導（過渡期）
  - 使用 UserModel::getUsers() 查詢同一企業的管理者
  - 支持分頁和過濾
  - 移除敏感資訊
  - 返回統一結構：`{ managers: [...], pager: {...} }`
- **優勢**：
  - 所有更新會顯示相同的管理者列表
  - 一個管理者可以分配給任何更新會
  - 與現有 API 設計一致

### 2. UserModel.php

#### allowedFields
- **變更**：添加 `'company_id'` 到允許字段清單

### 3. 前端元件修改

#### AssignAdminModal.vue
- **Props 修改**：`companyManagers: Array`
- **getManagersForRenewal() 方法**：返回完整列表

#### pages/urban-renewal/index.vue
- **fetchCompanyManagers() 方法**：
  - 從 `response.data.data.managers` 提取數據
  - 適配新的返回格式

---

## ⚡ 過渡期兼容性

### 支持場景

✅ **舊系統（urban_renewal_id 為主）**
```
企業管理者使用舊 JWT Token，仍含 urban_renewal_id
→ 系統自動從 urban_renewal_id 推導 company_id
→ 正常執行分配操作
```

✅ **新系統（company_id 為主）**
```
企業管理者使用新 JWT Token，含 company_id
→ 系統直接使用 company_id 進行權限檢查
→ 高效執行分配操作
```

✅ **API 調用一致性**
```
前端通過統一格式調用 API
→ 無論舊新系統都返回相同結構
→ 前端邏輯無需改變
```

---

## ✅ 驗證結果

| 項目 | 狀態 |
|------|------|
| 遷移文件創建 | ✅ 完成 |
| 遷移執行 | ✅ 成功 |
| 後端 PHP 語法 | ✅ 正確 |
| 權限檢查邏輯 | ✅ 改進 |
| 返回格式統一 | ✅ 完成 |
| 過渡期兼容 | ✅ 實現 |
| 錯誤處理 | ✅ 改進 |
| 前端適配 | ✅ 完成 |

---

## 🔐 權限檢查流程（新架構）

```
企業管理者請求分配更新會
    ↓
取得用戶 company_id（或從 urban_renewal_id 推導）
    ↓
驗證用戶 company_id 存在
    ├─ 不存在 → 返回 403 Forbidden
    └─ 存在 → 繼續
    ↓
遍歷每個更新會
    ↓
查詢更新會資料
    ├─ 不存在 → 返回 404 Not Found
    └─ 存在 → 繼續
    ↓
驗證更新會的 company_id 與用戶 company_id 是否相同
    ├─ 不相同 → 返回 403 Forbidden
    └─ 相同 → 繼續
    ↓
驗證管理者身份和狀態
    ↓
執行分配
    ↓
返回成功訊息
```

---

## 📊 影響範圍

### 受影響的 API 端點

1. **POST /api/urban-renewals/batch-assign**
   - 權限檢查邏輯已更新（基於 company_id）
   - 支持過渡期

2. **GET /api/urban-renewals/company-managers**
   - **重要變更**：返回格式已改變
   - 從簡單陣列改為 `{ managers: [...], pager: {...} }`
   - 與 `/api/users` 格式一致
   - 同一企業的統一管理者列表

### 相容性

- ✅ 後端完全兼容，支持過渡期
- ✅ 前端已適配新的返回格式
- ✅ API 設計與現有系統統一

---

## 🚀 後續建議

### 前端適配（已完成）

前端分配介面已根據新的統一列表方式進行調整：

**新邏輯**：
```javascript
// 獲取企業的所有管理者
const managers = response.data.data.managers
// 所有更新會使用相同的列表
companyManagers.value = managers
```

**優勢**：
- ✅ 一個管理者可以分配給公司的任何更新會
- ✅ 前端邏輯簡單統一
- ✅ 用戶體驗一致

### 2. 用戶認證系統升級

- 新增 JWT Token 中的 `company_id` 欄位
- 維持 `urban_renewal_id` 用於過渡期
- 建議 6 個月內完成全面升級

### 3. 數據遷移

考慮執行一次性數據遷移，為所有企業管理者設置 `company_id`

---

## 📚 相關文檔

| 文檔 | 說明 |
|------|------|
| `docs/function/urban-renewal-assignment.md` | 分配管理者功能完整文檔 |
| `COMPANY_URBAN_RENEWAL_ONE_TO_MANY_PLANNING.md` | 一對多架構規劃書 |
| `IMPLEMENTATION_COMPLETE.md` | 一對多架構實施報告 |

---

## ⚠️ 注意事項

1. **API 格式變更**
   - 返回格式與 `/api/users` 統一
   - 前端需要從 `data.managers` 而非 `data` 獲取數據

2. **數據一致性**
   - 新增了外鍵約束 `fk_users_company_id`
   - 刪除企業時，相關管理者的 `company_id` 自動設為 NULL

3. **性能影響**
   - 新增索引 1 個（`idx_company_id`）
   - 查詢性能無負面影響
   - 支持分頁提高大數據集效率

4. **過渡期計劃**
   - 建議 6 個月內完成全面升級
   - 期間系統支持雙架構共存

---

**調整完成時間**：2025-11-15 12:29 UTC  
**驗證狀態**：✅ 全部通過  
**部署準備**：✅ 就緒  
**設計確認**：✅ 與現有 API 格式統一
