# 企業與更新會管理架構調整文檔

**更新日期**: 2025-11-15  
**版本**: v2.0 (一對多關係架構)  
**狀態**: 已實施

---

## 目錄

1. [架構概述](#架構概述)
2. [資料庫表設計](#資料庫表設計)
3. [核心關係](#核心關係)
4. [使用者角色與權限](#使用者角色與權限)
5. [Model 方法](#model-方法)
6. [查詢示例](#查詢示例)
7. [遷移資料說明](#遷移資料說明)
8. [向後相容性](#向後相容性)

---

## 架構概述

### 新架構特點

系統已從 **企業與更新會一對一** 升級為 **一對多** 關係，同時支持 **企業管理者管理多個更新會**。

**核心改變**：
- ✅ 一個企業可管理多個更新會
- ✅ 一個企業管理者可被授權管理多個更新會
- ✅ 支持精細化的權限控制（完全、唯讀、財務）
- ✅ 完整的資料庫外鍵約束和級聯刪除

### 三層架構

```
┌──────────────────────────────────────────────────────────────┐
│                    companies (企業)                           │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ id | name | tax_id | company_phone | max_renewal_count│ │
│  └────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────┘
         │                          │
         │ 1:N                      │ 1:N
         ↓                          ↓
    ┌─────────────────┐    ┌──────────────────────────────────┐
    │  urban_renewals │    │ company_managers_renewals (新)   │
    ├─────────────────┤    ├──────────────────────────────────┤
    │ id              │    │ id                               │
    │ name            │    │ company_id (FK)                  │
    │ company_id (FK) │◄───│ manager_id (FK) → users.id       │
    │ chairman_name   │    │ urban_renewal_id (FK)            │
    │ ...             │    │ permission_level                 │
    │                 │    │ is_primary                       │
    └─────────────────┘    └──────────────────────────────────┘
         ▲                           △
         │ 1:1                       │ 多對多
         │                          │
         └──────────────────────────┘
              (通過關聯表)
              
    ┌──────────────────────────────────────────────┐
    │              users (使用者)                  │
    ├──────────────────────────────────────────────┤
    │ id                                           │
    │ username                                     │
    │ role (admin, chairman, member, observer)    │
    │ user_type = 'enterprise'                     │
    │ company_id (FK) → companies.id               │
    │ is_company_manager = 1                       │
    │ urban_renewal_id (預設工作會，可NULL)       │
    │ ...                                          │
    └──────────────────────────────────────────────┘
```

---

## 資料庫表設計

### 1. users 表（使用者表）

```sql
SELECT * FROM users WHERE user_type = 'enterprise' AND is_company_manager = 1;
```

**相關欄位**：
| 欄位 | 類型 | 說明 |
|------|------|------|
| `id` | INT | 主鍵 |
| `company_id` | INT (FK) | 所屬企業ID（企業管理者必填） |
| `is_company_manager` | TINYINT | 是否為企業管理者 (1=是, 0=否) |
| `urban_renewal_id` | INT | 預設/主要工作更新會（可為NULL） |
| `user_type` | ENUM | 使用者類型 ('enterprise'/'general') |
| `created_at`, `updated_at`, `deleted_at` | DATETIME | 時間戳 |

**注意**：
- `urban_renewal_id` 保留作為「預設工作會」
- 登入時若設置了此欄位，會預設進入該更新會
- 企業管理者可訪問的完整更新會列表應通過 `company_managers_renewals` 表查詢

### 2. companies 表（企業表）

```sql
SELECT * FROM companies;
```

**相關欄位**：
| 欄位 | 類型 | 說明 |
|------|------|------|
| `id` | INT | 主鍵 |
| `name` | VARCHAR | 企業名稱 |
| `tax_id` | VARCHAR | 統一編號 |
| `company_phone` | VARCHAR | 企業電話 |
| `max_renewal_count` | INT | 最大可管理更新會數量 |
| `max_issue_count` | INT | 最大可提出議題數量 |

**注意**：
- 已移除 `urban_renewal_id` 欄位（遷移至 `urban_renewals.company_id`）

### 3. urban_renewals 表（更新會表）

```sql
SELECT * FROM urban_renewals WHERE company_id = ?;
```

**相關欄位**：
| 欄位 | 類型 | 說明 |
|------|------|------|
| `id` | INT | 主鍵 |
| `company_id` | INT (FK) | 所屬企業ID |
| `name` | VARCHAR | 更新會名稱 |
| `chairman_name` | VARCHAR | 理事長姓名 |
| `chairman_phone` | VARCHAR | 理事長電話 |

**特性**：
- 一個企業可擁有多個更新會
- 通過 `company_id` 與企業關聯

### 4. ✨ 新增：company_managers_renewals 表（企業管理者-更新會關聯表）

**用途**：管理企業管理者對各個更新會的權限

```sql
SELECT * FROM company_managers_renewals 
WHERE company_id = ? AND manager_id = ?;
```

**表結構**：
| 欄位 | 類型 | 說明 |
|------|------|------|
| `id` | INT | 主鍵 |
| `company_id` | INT (FK) | 企業ID |
| `manager_id` | INT (FK) | 管理者用戶ID (users.id) |
| `urban_renewal_id` | INT (FK) | 更新會ID |
| `permission_level` | VARCHAR | 權限等級：'full'(完全), 'readonly'(唯讀), 'finance'(財務) |
| `is_primary` | TINYINT | 是否為主管理者 (1=是, 0=否) |
| `created_at`, `updated_at` | DATETIME | 時間戳 |

**約束**：
```sql
UNIQUE KEY unique_manager_renewal (company_id, manager_id, urban_renewal_id)
-- 同一企業的同一管理者對同一更新會只能有一條記錄
```

**索引**：
```sql
INDEX idx_company_manager (company_id, manager_id)
-- 查詢某企業的某管理者可訪問的更新會

INDEX idx_company_renewal (company_id, urban_renewal_id)  
-- 查詢某企業的某更新會有哪些管理者

INDEX idx_manager (manager_id)
-- 查詢某管理者的所有授權
```

**外鍵級聯規則**：
- 删除企業時，相關記錄自動刪除
- 删除管理者時，相關記錄自動刪除
- 删除更新會時，相關記錄自動刪除

---

## 核心關係

### 一個企業對多個更新會

```
企業 (ID: 1) - 艾聯建設
├── 文山社區更新會 (ID: 10)
├── 信義社區更新會 (ID: 11)
└── 大安社區更新會 (ID: 12)
```

### 一個企業管理者對多個更新會

```
企業管理者用戶 (ID: 1, username: john)
├─ company_id: 1 (所屬企業：艾聯建設)
├─ is_company_manager: 1 (確認為管理者)
├─ urban_renewal_id: 10 (預設工作會)
└─ 可訪問的更新會 (通過 company_managers_renewals):
   ├─ 文山社區更新會 (ID: 10) - 完全權限
   └─ 信義社區更新會 (ID: 11) - 完全權限

企業管理者用戶 (ID: 2, username: jane)  
├─ company_id: 1 (所屬企業：艾聯建設)
├─ is_company_manager: 1 (確認為管理者)
├─ urban_renewal_id: 11 (預設工作會)
└─ 可訪問的更新會 (通過 company_managers_renewals):
   ├─ 信義社區更新會 (ID: 11) - 完全權限
   └─ 大安社區更新會 (ID: 12) - 唯讀權限
```

---

## 使用者角色與權限

### 企業管理者 (user_type='enterprise', is_company_manager=1)

**歸屬**：
- `company_id` → 所屬企業（必填）
- `urban_renewal_id` → 預設工作會（可選）

**權限**：
- ✅ 查詢所屬企業的完整信息
- ✅ 訪問該企業旗下的所有授權更新會
- ✅ 在授權的更新會間切換
- ✅ 管理企業配額 (max_renewal_count, max_issue_count)
- ✅ 根據 `permission_level` 的值執行相應操作

**查詢方式**：
```php
// 方式1：直接查詢（推薦）
SELECT * FROM company_managers_renewals 
WHERE company_id = ? AND manager_id = ?;

// 方式2：包含更新會信息
SELECT cmr.*, ur.name, ur.chairman_name
FROM company_managers_renewals cmr
JOIN urban_renewals ur ON cmr.urban_renewal_id = ur.id
WHERE cmr.company_id = ? AND cmr.manager_id = ?;
```

### 企業員工使用者 (user_type='enterprise', is_company_manager=0)

**歸屬**：
- `company_id` → 所屬企業（必填）
- `urban_renewal_id` → 指定工作更新會（必填，唯一）

**權限**：
- ❌ 無法管理企業信息
- ❌ 無法訪問其他更新會
- ✅ 僅能訪問指定的更新會

**查詢方式**：
```php
// 直接使用 users 表的 urban_renewal_id
WHERE user_id = ? AND urban_renewal_id = ?;
```

---

## Model 方法

### CompanyManagerRenewalModel

已新增專用模型：`App\Models\CompanyManagerRenewalModel`

#### 常用方法

**1. 查詢管理者可訪問的更新會**
```php
$model = new CompanyManagerRenewalModel();
$renewals = $model->getManagerRenewals($companyId, $managerId);
// 返回該管理者在該企業下的所有授權更新會
```

**2. 查詢更新會的所有管理者**
```php
$managers = $model->getRenewalManagers($companyId, $urbanRenewalId);
// 返回該企業該更新會的所有管理者
```

**3. 檢查訪問權限**
```php
$hasAccess = $model->hasAccess($managerId, $companyId, $urbanRenewalId);
// 返回 true/false
```

**4. 獲取可訪問的更新會ID列表**
```php
$renewalIds = $model->getAccessibleRenewalIds($managerId, $companyId);
// 返回 [10, 11, 12] 等ID數組
```

**5. 授予權限**
```php
$model->grantAccess(
    $companyId, 
    $managerId, 
    $urbanRenewalId, 
    'full',  // permission_level
    true     // is_primary
);
```

**6. 撤銷權限**
```php
$model->revokeAccess($companyId, $managerId, $urbanRenewalId);
// 移除管理者對該更新會的權限
```

**7. 撤銷所有權限**
```php
$model->revokeAllAccess($companyId, $managerId);
// 移除管理者在企業內的所有權限
```

**8. 檢查是否為主管理者**
```php
$isPrimary = $model->isPrimaryManager($managerId, $companyId, $urbanRenewalId);
```

**9. 獲取企業的所有管理者**
```php
$allManagers = $model->getCompanyManagersWithRenewals($companyId);
// 返回該企業所有管理者及其管理的更新會
```

---

## 查詢示例

### 場景1：企業管理者登入後查詢可訪問的更新會

```php
// 假設登入用戶信息在 $_SERVER['AUTH_USER']
$user = $_SERVER['AUTH_USER'];

// 若用戶有 urban_renewal_id，直接進入該更新會
if ($user['urban_renewal_id']) {
    // 預設進入此更新會
    $defaultRenewal = $user['urban_renewal_id'];
}

// 查詢該管理者的所有可訪問更新會
$model = new CompanyManagerRenewalModel();
$renewals = $model->getManagerRenewals($user['company_id'], $user['id']);

// 結果示例：
// [
//     [
//         'id' => 1,
//         'company_id' => 1,
//         'manager_id' => 1,
//         'urban_renewal_id' => 10,
//         'permission_level' => 'full',
//         'is_primary' => 1,
//         'renewal_name' => '文山社區更新會',
//         'chairman_name' => '王小明'
//     ],
//     [
//         'id' => 2,
//         'company_id' => 1,
//         'manager_id' => 1,
//         'urban_renewal_id' => 11,
//         'permission_level' => 'full',
//         'is_primary' => 0,
//         'renewal_name' => '信義社區更新會',
//         'chairman_name' => '李大衛'
//     ]
// ]
```

### 場景2：管理者切換工作更新會

```php
$user = $_SERVER['AUTH_USER'];
$newRenewalId = $this->request->getPost('urban_renewal_id');

$model = new CompanyManagerRenewalModel();

// 驗證該管理者是否有權訪問新的更新會
if ($model->hasAccess($user['id'], $user['company_id'], $newRenewalId)) {
    // 允許切換
    // 可選：更新 users 表的 urban_renewal_id（下次登入的預設會）
} else {
    // 權限不足
    return $this->failForbidden('您沒有權限訪問該更新會');
}
```

### 場景3：檢查管理者的權限等級

```php
$model = new CompanyManagerRenewalModel();
$record = $model->where('company_id', $companyId)
               ->where('manager_id', $managerId)
               ->where('urban_renewal_id', $urbanRenewalId)
               ->first();

if ($record) {
    switch ($record['permission_level']) {
        case 'full':
            // 可執行所有操作
            break;
        case 'readonly':
            // 僅可查看
            break;
        case 'finance':
            // 僅可操作財務相關功能
            break;
    }
}
```

### 場景4：系統管理員為企業管理者授予新的更新會權限

```php
$model = new CompanyManagerRenewalModel();

// 授予 manager_id=1 對 urban_renewal_id=12 的完全權限
$model->grantAccess(
    $companyId = 1,
    $managerId = 1,
    $urbanRenewalId = 12,
    $permissionLevel = 'full',
    $isPrimary = false
);
```

---

## 遷移資料說明

### 遷移過程

在遷移時執行的 SQL：

```sql
INSERT INTO company_managers_renewals 
(company_id, manager_id, urban_renewal_id, permission_level, is_primary, created_at, updated_at)
SELECT 
    u.company_id,
    u.id as manager_id,
    u.urban_renewal_id,
    'full' as permission_level,
    1 as is_primary,
    NOW() as created_at,
    NOW() as updated_at
FROM users u
WHERE u.user_type = 'enterprise'
  AND u.is_company_manager = 1
  AND u.company_id IS NOT NULL
  AND u.urban_renewal_id IS NOT NULL
```

### 遷移規則

1. 只遷移 `user_type='enterprise'` 且 `is_company_manager=1` 的用戶
2. 必須同時有 `company_id` 和 `urban_renewal_id`
3. 所有遷移記錄的 `permission_level` 設為 `'full'`（完全權限）
4. 所有遷移記錄的 `is_primary` 設為 `1`（主管理者）

### 驗證遷移

```sql
-- 檢查遷移結果
SELECT COUNT(*) FROM company_managers_renewals;

-- 檢查是否有孤立記錄
SELECT cmr.* FROM company_managers_renewals cmr
WHERE cmr.manager_id NOT IN (SELECT id FROM users WHERE is_company_manager = 1);

-- 檢查權限分布
SELECT permission_level, COUNT(*) 
FROM company_managers_renewals 
GROUP BY permission_level;
```

---

## 向後相容性

### 現有字段保留

所有現有 `users` 表欄位保留，無需修改代碼：

| 欄位 | 保留 | 說明 |
|------|------|------|
| `company_id` | ✅ | 新增，指向企業 |
| `urban_renewal_id` | ✅ | 改為預設工作會（可選） |
| `is_company_manager` | ✅ | 繼續使用 |
| `user_type` | ✅ | 繼續使用 |

### 相容性規則

1. **既有代碼無需修改**
   - 仍可使用 `users.urban_renewal_id` 查詢
   - 仍可使用 `is_company_manager` 判斷管理者身份

2. **新功能使用新表**
   - 企業管理者管理多個更新會 → 使用 `company_managers_renewals`
   - 精細化權限控制 → 使用 `permission_level`

3. **漸進式遷移**
   - 可保留舊代碼
   - 新功能模塊使用新表
   - 逐步重構現有模塊

### 建議改進項（無需立即實施）

- [ ] 在登入後檢查 `company_managers_renewals` 而非僅 `users.urban_renewal_id`
- [ ] 實現企業管理者的更新會切換功能
- [ ] 添加精細化權限檢查邏輯
- [ ] 實現企業管理者的儀表板（多個更新會的統計）

---

## 總結

| 方面 | 說明 |
|------|------|
| **新增表** | `company_managers_renewals` |
| **新增 Model** | `CompanyManagerRenewalModel` |
| **修改表** | 無（保留現有欄位） |
| **資料遷移** | 自動遷移現有企業管理者數據 |
| **向後相容** | 100% 相容，無需改動現有代碼 |
| **擴展性** | 高度靈活，支持複雜的權限管理 |
| **效能** | 複合索引優化查詢速度 |

---

## 快速參考

### 常用查詢

```php
// 查詢管理者可訪問的更新會
$model->getManagerRenewals($companyId, $managerId);

// 查詢更新會的管理者
$model->getRenewalManagers($companyId, $urbanRenewalId);

// 檢查訪問權限
$model->hasAccess($managerId, $companyId, $urbanRenewalId);

// 授予權限
$model->grantAccess($companyId, $managerId, $urbanRenewalId);

// 撤銷權限
$model->revokeAccess($companyId, $managerId, $urbanRenewalId);
```

### 外鍵約束

```
company_managers_renewals.company_id → companies.id
company_managers_renewals.manager_id → users.id
company_managers_renewals.urban_renewal_id → urban_renewals.id

級聯刪除：CASCADE (刪除父記錄時自動刪除子記錄)
```
