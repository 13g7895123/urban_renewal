# 企業管理者建立使用者邏輯修復

**修復日期**: 2025-11-15 13:23  
**修復對象**: `/api/users` POST 端點 - 企業管理者建立使用者邏輯  
**狀態**: ✅ 完成

---

## 問題描述

企業管理者建立使用者時，使用者的歸屬關係設定錯誤：

❌ **舊邏輯**：
- 企業管理者建立的使用者被掛在 `urban_renewal_id` 下面
- 沒有設定 `company_id`
- 使用者無法明確歸屬於企業

✅ **新邏輯**：
- 企業管理者建立的使用者應該掛在 `company_id` 下面
- `urban_renewal_id` 變為可選的「預設工作會」
- 使用者明確歸屬於企業

---

## 修復內容

### 1. UserController::create() 修改

**位置**: `backend/app/Controllers/Api/UserController.php::create()` (第 204-216 行)

**修改前**:
```php
// 權限檢查：企業管理者只能建立同企業的使用者
if ($isCompanyManager && !$isAdmin) {
    if (isset($data['urban_renewal_id']) && $data['urban_renewal_id'] !== $user['urban_renewal_id']) {
        return $this->failForbidden('只能建立同企業的使用者');
    }
    $data['urban_renewal_id'] = $user['urban_renewal_id'];
    $data['user_type'] = 'enterprise';
    // ...
}
```

**修改後**:
```php
// 權限檢查：企業管理者只能建立同企業的使用者
if ($isCompanyManager && !$isAdmin) {
    // 新架構：驗證 company_id
    if (isset($data['company_id']) && $data['company_id'] != $user['company_id']) {
        return $this->failForbidden('只能建立同企業的使用者');
    }
    // 設定該使用者的企業
    $data['company_id'] = $user['company_id'];
    $data['user_type'] = 'enterprise';
    
    // urban_renewal_id 改為可選（用戶的預設工作會）
    if (!empty($data['urban_renewal_id'])) {
        // 驗證更新會屬於該企業
        $urbanRenewalModel = new \App\Models\UrbanRenewalModel();
        $renewal = $urbanRenewalModel->find($data['urban_renewal_id']);
        if (!$renewal || $renewal['company_id'] != $user['company_id']) {
            return $this->failForbidden('指定的更新會不屬於該企業');
        }
    } else {
        $data['urban_renewal_id'] = null;
    }
    // ...
}
```

**變更要點**:
- ✅ 改為驗證 `company_id` 而非 `urban_renewal_id`
- ✅ 設定使用者的 `company_id`（必填）
- ✅ `urban_renewal_id` 變為可選
- ✅ 如果提供 `urban_renewal_id`，驗證其屬於該企業
- ✅ 如未提供，設為 `null`

### 2. UserModel 驗證規則修改

**位置**: `backend/app/Models/UserModel.php::validationRules` (第 45-54 行)

**修改前**:
```php
protected $validationRules = [
    'username' => 'required|max_length[100]|is_unique[users.username,id,{id}]',
    'email' => 'permit_empty|valid_email|is_unique[users.email,id,{id}]',
    'password_hash' => 'required|min_length[6]',
    'role' => 'required|in_list[admin,chairman,member,observer]',
    'full_name' => 'permit_empty|max_length[100]',
    'phone' => 'permit_empty|max_length[20]'
];
```

**修改後**:
```php
protected $validationRules = [
    'username' => 'required|max_length[100]|is_unique[users.username,id,{id}]',
    'email' => 'permit_empty|valid_email|is_unique[users.email,id,{id}]',
    'password_hash' => 'required|min_length[6]',
    'role' => 'required|in_list[admin,chairman,member,observer]',
    'full_name' => 'permit_empty|max_length[100]',
    'phone' => 'permit_empty|max_length[20]',
    'company_id' => 'permit_empty|integer',
    'user_type' => 'permit_empty|in_list[general,enterprise]',
    'urban_renewal_id' => 'permit_empty|integer'
];
```

**變更要點**:
- ✅ 新增 `company_id` 驗證（可選整數）
- ✅ 新增 `user_type` 驗證（general/enterprise）
- ✅ 更新 `urban_renewal_id` 為可選

---

## 新的建立流程

### 企業管理者建立企業使用者

**請求範例**:
```bash
POST /api/users
{
  "username": "employee1",
  "email": "employee1@company.com",
  "password": "SecurePassword123!",
  "role": "member",
  "full_name": "員工一",
  "user_type": "enterprise",
  "company_id": 1,                    // 新增：企業ID
  "urban_renewal_id": 10              // 可選：預設工作會
}
```

**返回範例**:
```json
{
  "status": "success",
  "message": "使用者建立成功",
  "data": {
    "id": 10,
    "username": "employee1",
    "email": "employee1@company.com",
    "role": "member",
    "user_type": "enterprise",
    "company_id": 1,                  // ✅ 明確歸屬企業
    "urban_renewal_id": 10,           // 預設工作會
    "is_company_manager": 0,
    "is_active": 1,
    "created_at": "2025-11-15 13:23:00"
  }
}
```

### 驗證邏輯

| 條件 | 檢驗 | 結果 |
|------|------|------|
| company_id 不一致 | 企業管理者設定的 company_id 與自己不同 | ❌ 拒絕 (403) |
| urban_renewal_id 無效 | 指定的更新會不屬於該企業 | ❌ 拒絕 (403) |
| 無 urban_renewal_id | 不提供預設工作會 | ✅ 允許，設為 NULL |
| user_type | 企業管理者建立的使用者 user_type 必須是 enterprise | ✅ 自動設定 |

---

## 數據結構對比

### 舊結構（❌ 不正確）
```
User:
  - id: 10
  - username: employee1
  - company_id: NULL          ❌ 未設定
  - urban_renewal_id: 6       （掛在更新會下）
  - user_type: enterprise
```

### 新結構（✅ 正確）
```
User:
  - id: 10
  - username: employee1
  - company_id: 1             ✅ 明確歸屬企業
  - urban_renewal_id: 6       （可選的預設工作會）
  - user_type: enterprise
```

---

## 影響範圍

### ✅ 新建立的企業使用者
- 都會有 `company_id`
- `urban_renewal_id` 可選

### 🔄 既有使用者
- 舊數據保持不變
- 透過遷移腳本可補充 `company_id`

### 📋 API 端點影響
- `/api/users` POST - 企業管理者建立邏輯已更新
- `/api/users` GET - 使用 `company_id` 查詢（已在前次調整中完成）
- `/api/urban-renewals/company-managers` - 無影響

---

## 驗證清單

| 項目 | 狀態 |
|------|------|
| UserController 修改 | ✅ 完成 |
| UserModel 驗證規則 | ✅ 完成 |
| PHP 語法驗證 | ✅ 通過 |
| 邏輯完整 | ✅ 完整 |
| 文檔更新 | ✅ 完成 |

---

## 下一步建議

### 可選項 1: 補充現有數據
```php
// 為現有企業使用者補充 company_id
UPDATE users u
JOIN companies c ON u.company_id = c.id
SET u.company_id = c.id
WHERE u.user_type = 'enterprise' AND u.company_id IS NULL;
```

### 可選項 2: 前端更新
- 建立企業使用者表單需要改為使用 `company_id`
- 不再強制要求 `urban_renewal_id`

---

**修復完成日期**: 2025-11-15 13:23  
**狀態**: ✅ Ready for Testing
