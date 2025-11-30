# API 重構記錄

**重構日期**: 2025-11-15  
**重構內容**: `/api/urban-renewals/company-managers` 端點  
**狀態**: ✅ 完成

---

## 重構摘要

### 前置背景

企業與更新會架構從 **一對一** 升級到 **一對多**，新增了 `company_managers_renewals` 關聯表支持企業管理者管理多個更新會的功能。

該 API 端點需要重構以適配新架構，返回包含多對多授權關係的完整數據。

### 重構目標

- ✅ 使用新的 `CompanyManagerRenewalModel`
- ✅ 返回完整的多對多授權關係
- ✅ 包含權限等級和主管理者標記
- ✅ 保持向後相容性
- ✅ 改進數據結構清晰度

---

## 變更詳情

### 重構位置

| 項目 | 內容 |
|------|------|
| **檔案** | `backend/app/Controllers/Api/UrbanRenewalController.php` |
| **方法** | `getCompanyManagers()` |
| **行號** | 574-660 |
| **路由** | `GET /api/urban-renewals/company-managers` |

### 變更統計

| 項目 | 數值 |
|------|------|
| 移除行數 | ~65 行 |
| 新增行數 | ~87 行 |
| 淨增加 | ~22 行 |
| 改動複雜度 | 低 |

### 核心改變

#### 之前 (舊實現)
```php
// 使用 UserModel 查詢簡單的管理者列表
$userModel = new \App\Models\UserModel();
$managers = $userModel->getUsers($page, $perPage, $filters);

// 返回結構：只有基本用戶信息，無授權關係
{
  "managers": [
    {
      "id": 1,
      "username": "manager1",
      "urban_renewal_id": 10
    }
  ]
}
```

#### 現在 (新實現)
```php
// 使用 CompanyManagerRenewalModel 查詢含授權關係的數據
$cmrModel = new \App\Models\CompanyManagerRenewalModel();
$allRecords = $cmrModel->getCompanyManagersWithRenewals($userCompanyId);

// 返回結構：包含完整的多對多授權關係
{
  "managers": [
    {
      "id": 1,
      "username": "manager1",
      "authorized_renewals": [
        {
          "urban_renewal_id": 10,
          "permission_level": "full",
          "is_primary": 1
        },
        {
          "urban_renewal_id": 11,
          "permission_level": "full",
          "is_primary": 0
        }
      ]
    }
  ]
}
```

---

## 返回數據結構

### 新返回格式

```json
{
  "status": "success",
  "message": "企業管理者列表（包含授權更新會）",
  "data": {
    "company_id": 1,
    "total_managers": 2,
    "managers": [
      {
        "id": 1,
        "username": "manager1",
        "full_name": "管理者一",
        "email": "manager1@company.com",
        "is_company_manager": 1,
        "user_type": "enterprise",
        "company_id": 1,
        "authorized_renewals": [
          {
            "id": 1,
            "urban_renewal_id": 10,
            "renewal_name": "文山社區",
            "permission_level": "full",
            "is_primary": 1
          },
          {
            "id": 2,
            "urban_renewal_id": 11,
            "renewal_name": "信義社區",
            "permission_level": "full",
            "is_primary": 0
          }
        ]
      }
    ]
  }
}
```

### 字段說明

#### 管理者信息
- `id`: 用戶ID
- `username`: 用戶名
- `full_name`: 真實姓名
- `email`: 電郵
- `is_company_manager`: 企業管理者標記 (1=是)
- `user_type`: 用戶類型 (enterprise)
- `company_id`: 所屬企業ID

#### 授權更新會信息
- `id`: company_managers_renewals.id (關聯表記錄ID)
- `urban_renewal_id`: 更新會ID
- `renewal_name`: 更新會名稱
- `permission_level`: 權限等級 (full/readonly/finance)
  - `full`: 完全權限
  - `readonly`: 唯讀
  - `finance`: 財務相關
- `is_primary`: 主管理者標記 (1=主管理者, 0=協管)

---

## 實現邏輯

### 步驟 1: 驗證用戶權限
- 檢查用戶是否已登入
- 驗證用戶是否關聯企業

### 步驟 2: 確定企業ID
- 優先使用 `company_id`
- 過渡期兼容 `urban_renewal_id`

### 步驟 3: 查詢完整數據
```php
$cmrModel = new CompanyManagerRenewalModel();
$allRecords = $cmrModel->getCompanyManagersWithRenewals($userCompanyId);
```

### 步驟 4: 按管理者分組重組
- 遍歷查詢結果
- 按 `manager_id` 聚合
- 每個管理者包含其所有授權更新會

### 步驟 5: 返回結構化數據
- 包含企業ID和管理者總數
- 每個管理者含 `authorized_renewals` 陣列
- 清晰的層級結構

---

## 相容性考量

### ✅ 向後相容性

- **過渡期兼容**: 保留從 `urban_renewal_id` 推導 `company_id` 的邏輯
- **權限驗證**: 維持原有的權限檢查邏輯
- **錯誤處理**: 保留原有的錯誤狀態碼和消息

### ⚠️ 破壞性變更

返回結構有所改變，前端需要調整：
- 原 `urban_renewal_id` 移至 `authorized_renewals[].urban_renewal_id`
- 新增 `authorized_renewals[]` 陣列，包含完整授權信息

**前端適配指南**:
```javascript
// 舊方式 (不適用)
const renewalId = manager.urban_renewal_id;

// 新方式
const renewals = manager.authorized_renewals;
renewals.forEach(renewal => {
  console.log(renewal.urban_renewal_id);     // 更新會ID
  console.log(renewal.permission_level);     // 權限等級
  console.log(renewal.is_primary);           // 是否主管理者
});
```

---

## 驗證清單

| 項目 | 狀態 | 說明 |
|------|------|------|
| PHP 語法 | ✅ | 無語法錯誤 |
| 使用新 Model | ✅ | CompanyManagerRenewalModel |
| 權限驗證 | ✅ | 保留原有邏輯 |
| 過渡期兼容 | ✅ | 支持 urban_renewal_id 推導 |
| 數據重組 | ✅ | 按管理者分組 |
| 返回結構 | ✅ | 層級清晰 |
| 錯誤處理 | ✅ | 改進日誌記錄 |
| 文檔更新 | ✅ | 本文檔 |

---

## 測試建議

### 1. 基本查詢
```bash
curl -H "Authorization: Bearer {token}" \
  http://localhost:8080/api/urban-renewals/company-managers
```

### 2. 驗證返回結構
```javascript
// 檢查管理者列表
assert(response.data.managers.length > 0);

// 檢查授權更新會
assert(response.data.managers[0].authorized_renewals);
assert(response.data.managers[0].authorized_renewals[0].permission_level);
```

### 3. 權限等級驗證
```javascript
// 檢查不同的權限等級
const permissions = ['full', 'readonly', 'finance'];
response.data.managers.forEach(manager => {
  manager.authorized_renewals.forEach(renewal => {
    assert(permissions.includes(renewal.permission_level));
  });
});
```

### 4. 主管理者標記
```javascript
// 檢查 is_primary 值
response.data.managers.forEach(manager => {
  manager.authorized_renewals.forEach(renewal => {
    assert([0, 1].includes(renewal.is_primary));
  });
});
```

---

## 後續改進方向

### 可選項 1: 分頁支持
目前返回所有管理者及其授權。可考慮添加分頁功能。

### 可選項 2: 查詢參數過濾
支持按權限等級或主管理者標記過濾。

### 可選項 3: 新增 REST 端點
```
GET /api/companies/{companyId}/managers
GET /api/companies/{companyId}/managers/{managerId}/renewals
```

---

## 參考文檔

- `COMPANY_MANAGERS_RENEWALS_ARCHITECTURE.md` - 架構詳解
- `CompanyManagerRenewalModel.php` - Model 實現
- `UrbanRenewalController.php` - 控制器實現

---

**重構完成日期**: 2025-11-15 13:04  
**狀態**: ✅ Ready for Testing

---

# 補充：API 查詢邏輯調整 (企業管理者查詢統一)

**調整日期**: 2025-11-15 13:13  
**調整對象**: `/api/users` 和 `/api/urban-renewals/company-managers`

## 變更背景

在新的一對多架構中，企業與更新會的關係已改變：
- ❌ 舊：企業管理者 --1:1--> 企業 --1:1--> 更新會
- ✅ 新：企業 --1:N--> 更新會，企業管理者 --M:N--> 更新會

因此，兩支 API 的查詢邏輯需要統一：
- 都基於 `company_id` 查詢企業管理者
- 不再依賴 `urban_renewal_id` 篩選

## API 調整詳情

### API 1: `/api/users` (UserController)

**位置**: `backend/app/Controllers/Api/UserController.php::index()`

**變更內容**:
```php
// 新增：支持 company_id 查詢參數
'company_id' => $this->request->getGet('company_id')

// 新邏輯：企業管理者改為基於 company_id 查詢
if ($isCompanyManager) {
    if (!empty($filters['company_id'])) {
        // 驗證權限
        if ($filters['company_id'] != $user['company_id']) {
            return $this->failForbidden('權限不足');
        }
    } else {
        // 過渡期兼容：使用用戶的 company_id
        $filters['company_id'] = $user['company_id'];
    }
    // 移除 urban_renewal_id 篩選
    unset($filters['urban_renewal_id']);
}
```

**新用法**:
```bash
# 查詢公司1的所有企業管理者
GET /api/users?company_id=1&user_type=enterprise&is_company_manager=1

# 返回所有企業的管理者（簡單列表，無授權詳情）
{
  "users": [
    {
      "id": 1,
      "username": "manager1",
      "company_id": 1,
      "user_type": "enterprise",
      "is_company_manager": 1
    },
    {
      "id": 2,
      "username": "manager2",
      "company_id": 1,
      "user_type": "enterprise",
      "is_company_manager": 1
    }
  ]
}
```

### API 2: `/api/urban-renewals/company-managers` (UrbanRenewalController)

**位置**: `backend/app/Controllers/Api/UrbanRenewalController.php::getCompanyManagers()`

**現況**: 已支持基於 `company_id` 查詢，無需調整

**返回結構**: 包含每個管理者的授權更新會（詳細結構）
```bash
GET /api/urban-renewals/company-managers

# 返回當前用戶企業的所有管理者及其授權更新會
{
  "managers": [
    {
      "id": 1,
      "username": "manager1",
      "authorized_renewals": [
        {
          "urban_renewal_id": 10,
          "permission_level": "full",
          "is_primary": 1
        }
      ]
    }
  ]
}
```

### Model 調整: `UserModel::getUsers()`

**位置**: `backend/app/Models/UserModel.php::getUsers()`

**變更內容**:
```php
// 新增：支持 company_id 篩選
if (!empty($filters['company_id'])) {
    $builder->where('users.company_id', $filters['company_id']);
}

// 保留：urban_renewal_id 篩選（過渡期相容）
if (!empty($filters['urban_renewal_id'])) {
    $builder->where('users.urban_renewal_id', $filters['urban_renewal_id']);
}
```

## 使用對比

### 查詢企業管理者

| 用途 | API | 返回內容 |
|------|-----|--------|
| **簡單列表** | `/api/users?company_id=1&is_company_manager=1` | 管理者基本信息 |
| **詳細結構** | `/api/urban-renewals/company-managers` | 管理者 + 授權更新會 |

### 實際使用流程

**前端需求**：獲取當前用戶企業的所有管理者及其授權

**方案1**（推薦）：
```javascript
// 直接調用詳細 API
GET /api/urban-renewals/company-managers
// 返回完整的管理者及授權信息
```

**方案2**（簡單列表）：
```javascript
// 獲取簡單列表
GET /api/users?company_id=1&user_type=enterprise&is_company_manager=1
// 只返回管理者基本信息
```

## 相容性

### ✅ 向後相容
- 舊的 `urban_renewal_id` 查詢仍然支持
- 主席（chairman）的查詢邏輯不變
- 管理員（admin）無限制

### ⚠️ 變更注意
- 企業管理者查詢從 `urban_renewal_id` 改為 `company_id`
- `/api/users` API 返回結構不變
- `/api/urban-renewals/company-managers` 已支持新邏輯

## 驗證清單

| 項目 | 狀態 |
|------|------|
| UserModel 修改 | ✅ 完成 |
| UserController 修改 | ✅ 完成 |
| UrbanRenewalController | ✅ 已支持 |
| PHP 語法驗證 | ✅ 通過 |
| 向後相容 | ✅ 確保 |

## 下一步

前端需要調整：
- [ ] 企業管理者查詢改用 `company_id` 而非 `urban_renewal_id`
- [ ] 如需授權詳情，調用 `/api/urban-renewals/company-managers`
- [ ] 測試查詢和過濾功能

