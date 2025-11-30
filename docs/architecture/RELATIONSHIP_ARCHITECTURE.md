# 都市更新會與企業的關係架構說明文件

## 目錄
1. [概述](#概述)
2. [核心關係模式](#核心關係模式)
3. [資料庫架構](#資料庫架構)
4. [模型設計](#模型設計)
5. [API 接口](#api-接口)
6. [權限控制](#權限控制)
7. [業務流程](#業務流程)

---

## 概述

在都市更新管理系統中，**「都市更新會」（Urban Renewal）** 和 **「企業」（Company）** 存在著 **一對一的關係**。每個都市更新會對應一個企業，企業是都市更新會的管理實體。

### 關鍵定義

| 術語 | 定義 | 職責 |
|------|------|------|
| **更新會** (Urban Renewal) | 都市更新協會/組織 | 管理地主、會議、投票等實體資訊 |
| **企業** (Company) | 承包或管理該更新會的公司 | 管理更新會的配額、許可證等 |

---

## 核心關係模式

### 關係結構圖

```
┌─────────────────────────────────────────────────────────────┐
│                    Urban Renewal (更新會)                     │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ ID: 1                                                │  │
│  │ Name: 文山社區更新會                                 │  │
│  │ Chairman Name: 王小明                               │  │
│  │ Chairman Phone: 02-12345678                         │  │
│  │ Address: 台北市文山區...                            │  │
│  │ assigned_admin_id: (系統管理員)                      │  │
│  └──────────────────────────────────────────────────────┘  │
│                           ↓                                  │
│              一對一關係 (Foreign Key)                        │
│                           ↓                                  │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ Company (企業)                                       │  │
│  │ ┌──────────────────────────────────────────────┐   │  │
│  │ │ ID: 1                                        │   │  │
│  │ │ urban_renewal_id: 1 (FK)                     │   │  │
│  │ │ Name: 艾聯建設股份有限公司                   │   │  │
│  │ │ Tax ID: 25234567                             │   │  │
│  │ │ Company Phone: 02-87654321                   │   │  │
│  │ │ max_renewal_count: 1                         │   │  │
│  │ │ max_issue_count: 8                           │   │  │
│  │ └──────────────────────────────────────────────┘   │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

### 一對一關係特性

- **唯一性**：每個 urban_renewal_id 在 companies 表中只能出現一次
- **必需性**：companies 表中的 urban_renewal_id 不可為空
- **層級**：企業屬於更新會，不是獨立實體
- **生命週期**：企業資料依附於更新會

---

## 資料庫架構

### 表結構設計演進

#### 第一階段：統一設計（已廢棄）
在系統初期，所有企業相關欄位都存放在 `urban_renewals` 表中：

```sql
-- 廢棄的設計
CREATE TABLE urban_renewals (
    id INT PRIMARY KEY,
    name VARCHAR(255),               -- 更新會名稱
    tax_id VARCHAR(20),              -- 統一編號 ❌ 企業欄位
    company_phone VARCHAR(20),       -- 企業電話 ❌ 企業欄位
    max_renewal_count INT DEFAULT 1, -- ❌ 企業欄位
    max_issue_count INT DEFAULT 8,   -- ❌ 企業欄位
    chairman_name VARCHAR(100),
    chairman_phone VARCHAR(20),
    address VARCHAR(255),
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME
);
```

#### 第二階段：資料正規化（現行設計）

**遷移時間**：2025-11-02

設計分為兩個獨立的表，遵循資料庫正規化原則：

##### 1. urban_renewals 表（更新會信息）

```sql
CREATE TABLE urban_renewals (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL COMMENT '更新會名稱（非企業名稱）',
    chairman_name VARCHAR(100) NOT NULL COMMENT '理事長姓名',
    chairman_phone VARCHAR(20) NOT NULL COMMENT '理事長電話',
    address VARCHAR(255) COMMENT '地址',
    representative VARCHAR(255) COMMENT '代表人',
    assigned_admin_id INT UNSIGNED COMMENT '指派的系統管理員',
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,
    
    KEY idx_name (name),
    KEY idx_created_at (created_at)
);
```

**職責**：
- 儲存更新會的組織資訊
- 理事長和聯絡方式
- 與地主、會議、投票等的核心業務聯繫

**注意**：
- `member_count` 和 `area` 欄位已移除，改為動態計算
- 透過 `assigned_admin_id` 關聯系統管理員

##### 2. companies 表（企業信息）

```sql
CREATE TABLE companies (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    urban_renewal_id INT UNSIGNED NOT NULL COMMENT '對應的更新會ID (一對一關係)',
    name VARCHAR(255) NOT NULL COMMENT '企業名稱',
    tax_id VARCHAR(20) COMMENT '統一編號',
    company_phone VARCHAR(20) COMMENT '企業電話',
    max_renewal_count INT UNSIGNED DEFAULT 1 COMMENT '最大更新會數量',
    max_issue_count INT UNSIGNED DEFAULT 8 COMMENT '最大議題數量',
    created_at DATETIME,
    updated_at DATETIME,
    deleted_at DATETIME,
    
    KEY idx_urban_renewal_id (urban_renewal_id),
    UNIQUE KEY unique_urban_renewal_id (urban_renewal_id),
    FOREIGN KEY (urban_renewal_id) REFERENCES urban_renewals(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
);
```

**職責**：
- 儲存企業管理資訊
- 企業配額限制（max_renewal_count, max_issue_count）
- 企業聯絡資訊

### 遷移過程

遷移時：
1. 從 `urban_renewals` 建立新的 `companies` 表
2. 複製企業相關欄位到 `companies` 表
3. 從 `urban_renewals` 刪除企業欄位

```sql
-- 建立企業表時的資料遷移
INSERT INTO companies (urban_renewal_id, name, tax_id, company_phone, 
                       max_renewal_count, max_issue_count, created_at, updated_at)
SELECT 
    id as urban_renewal_id,
    name,
    tax_id,
    company_phone,
    COALESCE(max_renewal_count, 1) as max_renewal_count,
    COALESCE(max_issue_count, 8) as max_issue_count,
    created_at,
    updated_at
FROM urban_renewals
WHERE deleted_at IS NULL;
```

---

## 模型設計

### UrbanRenewalModel

位置：`backend/app/Models/UrbanRenewalModel.php`

**允許欄位**：
```php
protected $allowedFields = [
    'name',              // 更新會名稱
    'chairman_name',     // 理事長姓名
    'chairman_phone',    // 理事長電話
    'address',          // 地址
    'representative',    // 代表人
    'assigned_admin_id'  // 指派的管理員
];
```

**關鍵方法**：

| 方法 | 功能 | 用途 |
|------|------|------|
| `getUrbanRenewals($page, $perPage, $urbanRenewalId)` | 分頁取得更新會列表 | 列表查詢 |
| `getCompany($urbanRenewalId)` | 取得該更新會對應的企業 | 獲取企業資訊 |
| `calculateMemberCount($urbanRenewalId)` | 計算成員數量 | 動態統計 |
| `calculateTotalLandArea($urbanRenewalId)` | 計算總土地面積 | 動態統計 |
| `getUrbanRenewalsWithAdmin($page, $perPage)` | 取得含管理員資訊的更新會 | JOIN 查詢 |

**範例：取得企業資訊**
```php
$urbanRenewalModel = new UrbanRenewalModel();
$company = $urbanRenewalModel->getCompany($urbanRenewalId);
// 返回: ['id' => 1, 'name' => '企業名', 'tax_id' => '...', ...]
```

### CompanyModel

位置：`backend/app/Models/CompanyModel.php`

**允許欄位**：
```php
protected $allowedFields = [
    'urban_renewal_id',    // 關聯的更新會 ID
    'name',               // 企業名稱
    'tax_id',            // 統一編號
    'company_phone',     // 企業電話
    'max_renewal_count', // 最大更新會數量
    'max_issue_count'    // 最大議題數量
];
```

**關鍵方法**：

| 方法 | 功能 | 用途 |
|------|------|------|
| `getByUrbanRenewalId($urbanRenewalId)` | 透過更新會ID取得企業 | 一對一查詢 |
| `getWithUrbanRenewal($id)` | 取得企業與其更新會資訊 | JOIN 查詢 |
| `getUrbanRenewal($companyId)` | 取得該企業的更新會 | 反向查詢 |
| `updateCompany($id, $data)` | 更新企業資料 | 業務邏輯 |

**範例：查詢企業**
```php
$companyModel = new CompanyModel();

// 透過 urban_renewal_id 查詢
$company = $companyModel->getByUrbanRenewalId(1);

// 取得含更新會資訊的企業
$company = $companyModel->getWithUrbanRenewal(1);
```

### 驗證規則

#### UrbanRenewalModel 驗證

```php
protected $validationRules = [
    'name' => 'required|min_length[2]|max_length[255]',
    'chairman_name' => 'required|min_length[2]|max_length[100]',
    'chairman_phone' => 'required|min_length[8]|max_length[20]'
];

protected $validationMessages = [
    'name' => [
        'required' => '更新會名稱為必填項目',
        'min_length' => '更新會名稱至少需要2個字元',
        'max_length' => '更新會名稱不能超過255個字元'
    ],
    'chairman_name' => [
        'required' => '理事長姓名為必填項目',
        'min_length' => '理事長姓名至少需要2個字元',
        'max_length' => '理事長姓名不能超過100個字元'
    ],
    'chairman_phone' => [
        'required' => '理事長電話為必填項目',
        'min_length' => '理事長電話至少需要8個字元',
        'max_length' => '理事長電話不能超過20個字元'
    ]
];
```

#### CompanyModel 驗證

```php
protected $validationRules = [
    'name' => 'required|min_length[2]|max_length[255]',
    'tax_id' => 'permit_empty|min_length[8]|max_length[20]',
    'company_phone' => 'permit_empty|min_length[8]|max_length[20]',
    'max_renewal_count' => 'permit_empty|integer|greater_than[0]',
    'max_issue_count' => 'permit_empty|integer|greater_than[0]'
];

protected $validationMessages = [
    'name' => [
        'required' => '企業名稱為必填項目',
        'min_length' => '企業名稱至少需要2個字元',
        'max_length' => '企業名稱不能超過255個字元'
    ],
    'tax_id' => [
        'min_length' => '統一編號至少需要8個字元',
        'max_length' => '統一編號不能超過20個字元'
    ],
    // ... 更多驗證信息
];
```

---

## API 接口

### 更新會 API

#### 1. 取得所有更新會列表
```
GET /api/urban-renewals
?page=1&per_page=10&search=關鍵字

回應：
{
    "status": "success",
    "message": "查詢成功",
    "data": [
        {
            "id": 1,
            "name": "文山社區更新會",
            "chairman_name": "王小明",
            "chairman_phone": "02-12345678",
            "address": "台北市文山區...",
            "representative": null,
            "assigned_admin_id": 1,
            "member_count": 50,      // 動態計算
            "area": 12500.50,        // 動態計算
            "created_at": "2025-01-01 10:00:00",
            "updated_at": "2025-11-15 12:00:00"
        }
    ],
    "pager": {...}
}
```

#### 2. 取得單一更新會詳情
```
GET /api/urban-renewals/{id}

回應：
{
    "status": "success",
    "message": "查詢成功",
    "data": {
        "id": 1,
        "name": "文山社區更新會",
        "chairman_name": "王小明",
        "chairman_phone": "02-12345678",
        ...
    }
}
```

#### 3. 建立更新會
```
POST /api/urban-renewals

請求體：
{
    "name": "新社區更新會",
    "chairman_name": "李美姐",
    "chairman_phone": "03-98765432",
    "address": "新竹市東區...",
    "representative": "代表人"
}

回應：
{
    "status": "success",
    "message": "建立成功",
    "data": {
        "id": 2,
        "name": "新社區更新會",
        ...
    }
}
```

#### 4. 更新更新會資訊
```
PUT /api/urban-renewals/{id}

請求體：
{
    "chairman_name": "新理事長名字",
    "chairman_phone": "新電話號碼"
}

回應：
{
    "status": "success",
    "message": "更新成功",
    "data": {...}
}
```

#### 5. 刪除更新會（軟刪除）
```
DELETE /api/urban-renewals/{id}

回應：
{
    "status": "success",
    "message": "刪除成功"
}
```

### 企業 API

#### 1. 取得目前用戶的企業資訊
```
GET /api/companies/me
Authorization: Bearer {token}

需求權限：企業管理者 (is_company_manager = 1)

回應：
{
    "status": "success",
    "message": "查詢成功",
    "data": {
        "id": 1,
        "urban_renewal_id": 1,
        "name": "艾聯建設股份有限公司",
        "tax_id": "25234567",
        "company_phone": "02-87654321",
        "max_renewal_count": 1,
        "max_issue_count": 8,
        "created_at": "2025-01-01 10:00:00",
        "updated_at": "2025-11-15 12:00:00"
    }
}
```

#### 2. 更新企業資訊
```
PUT /api/companies/me
Authorization: Bearer {token}

請求體：
{
    "name": "新企業名稱",
    "company_phone": "新電話號碼",
    "max_renewal_count": 2,
    "max_issue_count": 10
}

回應：
{
    "status": "success",
    "message": "更新成功",
    "data": {...}
}

限制：
- 不能修改 urban_renewal_id（它是不可變的外鍵）
- 只有該企業的管理者可以修改
```

### 路由設置

```php
// backend/app/Config/Routes.php

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // 更新會路由
    $routes->group('urban-renewals', function ($routes) {
        $routes->get('/', 'UrbanRenewalController::index');
        $routes->get('(:num)', 'UrbanRenewalController::show/$1');
        $routes->post('/', 'UrbanRenewalController::create');
        $routes->put('(:num)', 'UrbanRenewalController::update/$1');
        $routes->delete('(:num)', 'UrbanRenewalController::delete/$1');
    });

    // 企業路由
    $routes->group('companies', function ($routes) {
        $routes->get('me', 'CompanyController::me');
        $routes->put('me', 'CompanyController::update');
    });
});
```

---

## 權限控制

### 用戶類型

| 類型 | is_company_manager | 權限 |
|------|-------------------|------|
| 系統管理員 | 0/false | 可查看/管理所有更新會和企業 |
| 企業管理者 | 1/true | 只能查看/管理自己的企業 |
| 普通用戶 | 0/false | 無管理權限 |

### 企業管理者驗證流程

```php
// 步驟 1：檢查用戶身份
$user = $_SERVER['AUTH_USER'] ?? null;
if (!$user) {
    // 返回 401 - 未授權
}

// 步驟 2：檢查是否為企業管理者
$isCompanyManager = isset($user['is_company_manager']) && 
                   ($user['is_company_manager'] === 1 || 
                    $user['is_company_manager'] === '1' || 
                    $user['is_company_manager'] === true);
if (!$isCompanyManager) {
    // 返回 403 - 權限不足
}

// 步驟 3：取得用戶關聯的 urban_renewal_id
$urbanRenewalId = $user['urban_renewal_id'] ?? null;
if (!$urbanRenewalId) {
    // 返回 403 - 帳號未關聯任何企業
}

// 步驟 4：查詢企業資料
$company = $companyModel->getByUrbanRenewalId($urbanRenewalId);
if (!$company) {
    // 返回 404 - 查無企業資料
}
```

### CompanyController 中的權限檢查

```php
public function me()
{
    // 完整的權限驗證流程
    $user = $_SERVER['AUTH_USER'] ?? null;
    if (!$user) {
        return $this->response->setStatusCode(401)->setJSON([
            'status' => 'error',
            'message' => '未授權：無法識別用戶身份'
        ]);
    }

    $isCompanyManager = isset($user['is_company_manager']) && 
                       ($user['is_company_manager'] === 1 || 
                        $user['is_company_manager'] === '1' || 
                        $user['is_company_manager'] === true);
    
    if (!$isCompanyManager) {
        return $this->response->setStatusCode(403)->setJSON([
            'status' => 'error',
            'message' => '權限不足：您沒有管理企業的權限'
        ]);
    }

    $urbanRenewalId = $user['urban_renewal_id'] ?? null;
    if (!$urbanRenewalId) {
        return $this->response->setStatusCode(403)->setJSON([
            'status' => 'error',
            'message' => '權限不足：您的帳號未關聯任何企業'
        ]);
    }

    $company = $this->companyModel->getByUrbanRenewalId($urbanRenewalId);
    if (!$company) {
        return $this->response->setStatusCode(404)->setJSON([
            'status' => 'error',
            'message' => '查無企業資料'
        ]);
    }

    // ... 後續業務邏輯
}
```

---

## 業務流程

### 建立新的更新會與企業

#### 流程步驟

1. **建立更新會記錄**
   ```
   POST /api/urban-renewals
   {
       "name": "新社區更新會",
       "chairman_name": "理事長",
       "chairman_phone": "電話"
   }
   ```
   返回：urban_renewal_id (例如: 5)

2. **建立企業記錄**
   ```
   POST /api/companies
   {
       "urban_renewal_id": 5,
       "name": "企業名稱",
       "tax_id": "統一編號",
       "max_renewal_count": 1,
       "max_issue_count": 8
   }
   ```
   返回：company_id (例如: 10)

3. **建立/指派企業管理者用戶**
   ```
   POST /api/auth/register
   {
       "username": "user@example.com",
       "password": "密碼",
       "is_company_manager": true,
       "urban_renewal_id": 5
   }
   ```

#### 關係建立圖示

```
更新會 (ID: 5)
    ↓ 1:1 關係
企業 (ID: 10, urban_renewal_id: 5)
    ↓ 關聯
企業管理者用戶 (is_company_manager: 1, urban_renewal_id: 5)
```

### 企業管理者登入後的操作流程

```
1. 登入 (JWT Token)
   ↓
2. 呼叫 GET /api/companies/me
   - 驗證 is_company_manager = 1
   - 驗證 urban_renewal_id
   - 查詢企業資訊
   ↓
3. 顯示該企業資訊
   - 企業名稱、統一編號、電話
   - 配額 (max_renewal_count, max_issue_count)
   ↓
4. 企業管理者可以修改的資訊
   - PUT /api/companies/me
     * name (企業名稱)
     * tax_id (統一編號)
     * company_phone (企業電話)
     * max_renewal_count (配額)
     * max_issue_count (配額)
```

### 系統管理員的操作流程

```
系統管理員 (role: 'admin', is_company_manager: false)
    ↓
1. 可查看所有更新會
   GET /api/urban-renewals (無過濾)
   ↓
2. 可以管理企業配額
   - 透過 urban_renewal_id 查詢企業
   - 更新 max_renewal_count, max_issue_count
   ↓
3. 可指派管理員
   - 更新 urban_renewals.assigned_admin_id
```

---

## 資料查詢範例

### 查詢一個更新會及其企業資訊

```php
// 場景：前端需要顯示某個更新會的完整資訊（包括企業）

$urbanRenewalModel = new UrbanRenewalModel();

// 取得更新會基本資訊
$urbanRenewal = $urbanRenewalModel->getUrbanRenewal(1);
// 結果:
// [
//     'id' => 1,
//     'name' => '文山社區更新會',
//     'chairman_name' => '王小明',
//     'chairman_phone' => '02-12345678',
//     ...
// ]

// 取得該更新會的企業資訊
$company = $urbanRenewalModel->getCompany(1);
// 結果:
// [
//     'id' => 1,
//     'urban_renewal_id' => 1,
//     'name' => '艾聯建設股份有限公司',
//     'tax_id' => '25234567',
//     'company_phone' => '02-87654321',
//     'max_renewal_count' => 1,
//     'max_issue_count' => 8,
//     ...
// ]

// 組合返回給前端
$response = [
    'urban_renewal' => $urbanRenewal,
    'company' => $company
];
```

### 查詢企業及其更新會資訊

```php
$companyModel = new CompanyModel();

// 方式 1：透過公司 ID 查詢（含更新會資訊）
$company = $companyModel->getWithUrbanRenewal(1);
// 結果包含:
// - companies 表所有欄位
// - urban_renewals.name as urban_renewal_name
// - urban_renewals.chairman_name
// - 等等

// 方式 2：透過 urban_renewal_id 查詢
$company = $companyModel->getByUrbanRenewalId(1);

// 方式 3：取得企業關聯的更新會
$urbanRenewal = $companyModel->getUrbanRenewal(1); // 公司 ID
```

---

## 常見問題與注意事項

### Q1：更新會和企業可以 1 對多嗎？
**A**：目前設計是 1 對 1。如果需要改為 1 對多，需要修改資料庫外鍵（移除 UNIQUE 約束）。

### Q2：刪除更新會時，企業記錄會怎樣？
**A**：由於外鍵設置了 `ON DELETE CASCADE`，刪除更新會時企業記錄會自動級聯刪除。

### Q3：企業名稱和更新會名稱有什麼區別？
**A**：
- **更新會名稱**：協會/組織名稱（例如：「文山社區更新會」）
- **企業名稱**：管理該更新會的公司名稱（例如：「艾聯建設股份有限公司」）

### Q4：如何查詢某位企業管理者管理的企業？
**A**：使用 urban_renewal_id：
```php
// 用戶已登入，JWT 中含有 urban_renewal_id
$urbanRenewalId = $_SERVER['AUTH_USER']['urban_renewal_id'];

// 查詢該企業
$company = $companyModel->getByUrbanRenewalId($urbanRenewalId);
```

### Q5：max_renewal_count 和 max_issue_count 有什麼用途？
**A**：
- **max_renewal_count**：該企業最多能管理幾個更新會（目前通常為 1）
- **max_issue_count**：該企業在一次會議中最多能提出幾個議題

---

## 遷移參考

### 從舊架構升級到新架構

如果要將現有系統升級到新架構：

1. **備份現有資料**
   ```bash
   mysqldump -u user -p database > backup.sql
   ```

2. **執行遷移**
   ```bash
   cd backend
   php spark migrate
   ```

3. **驗證資料**
   ```sql
   -- 檢查企業表資料是否完整
   SELECT COUNT(*) FROM companies;
   
   -- 確保一對一關係
   SELECT ur.id, c.id, c.urban_renewal_id 
   FROM urban_renewals ur 
   LEFT JOIN companies c ON ur.id = c.urban_renewal_id
   WHERE c.id IS NULL;
   ```

4. **回滾（如需要）**
   ```bash
   php spark migrate:rollback
   ```

---

## 技術細節

### 軟刪除 (Soft Delete)

兩個表都支援軟刪除（使用 `deleted_at` 欄位）：

```php
// 軟刪除企業
$companyModel->delete($id);
// 將 companies.deleted_at 設為當前時間

// 查詢時自動排除軟刪除記錄
$company = $companyModel->find($id); // 不返回已刪除的記錄

// 查詢包括已刪除的記錄
$company = $companyModel->withDeleted()->find($id);

// 只查詢已刪除的記錄
$company = $companyModel->onlyDeleted()->find($id);
```

### 時間戳記 (Timestamps)

自動管理建立和修改時間：

```php
// 插入時自動設置 created_at
$companyModel->insert($data);

// 更新時自動設置 updated_at
$companyModel->update($id, $data);

// deleted_at 在軟刪除時自動設置
$companyModel->delete($id);
```

---

## 總結

都市更新會與企業的一對一關係架構是系統的核心設計，它清晰地分離了：
- **更新會**：協會組織與成員管理
- **企業**：承包商與配額管理

這種設計遵循資料庫正規化原則，提高了系統的可維護性和擴展性。
