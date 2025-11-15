# 移除所有權人數手動輸入欄位 - 改為統計欄位

## 概述

`member_count` (所有權人數) 目前在新建和編輯更新會時是手動輸入欄位，但這個數字應該是自動統計該更新會關聯的所有權人數量。本文件說明需要調整的所有地方。

## 問題分析

### 當前狀況
1. **新建更新會**（`/tables/urban-renewal` - index.vue）
   - 表單中包含「所有權人數」輸入欄位（第95-106行）
   - 使用者需要手動輸入數字
   - 提交時包含在 POST 請求中（第342行）

2. **編輯更新會**（`/tables/urban-renewal/[id]/basic-info.vue`）
   - 基本資訊區塊包含「所有權人數」輸入欄位（第60-69行）
   - 使用者可以手動修改
   - 儲存時包含在 PUT 請求中（第749行）

3. **列表顯示**（`/tables/urban-renewal` - index.vue）
   - 表格中顯示 `member_count` 欄位（第175行表頭，第201行資料）
   - 顯示的是資料庫中儲存的值

### 應該的行為
- `member_count` 應該是**唯讀統計欄位**
- 數值應該自動計算：該更新會的 `property_owners` 資料表中的記錄數
- 不應該出現在新建或編輯表單中
- 列表中可以顯示（因為是統計資訊）

---

## 需要調整的地方

### 一、Frontend 前端調整

#### 1. `/frontend/pages/tables/urban-renewal/index.vue`

##### 1.1 移除新建表單中的所有權人數欄位
**位置**：第95-106行

**需要刪除的代碼**：
```vue
<!-- 所有權人數 -->
<div>
  <label for="memberCount" class="block text-sm font-medium text-gray-700 mb-2">所有權人數 <span class="text-red-500">*</span></label>
  <input
    id="memberCount"
    v-model="formData.memberCount"
    type="number"
    placeholder="請輸入所有權人數"
    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
    required
  />
</div>
```

##### 1.2 移除表單資料中的 memberCount
**位置**：第302-309行

**原始代碼**：
```javascript
const formData = reactive({
  name: '',
  area: '',
  memberCount: '',  // <-- 移除這行
  chairmanName: '',
  chairmanPhone: ''
})
```

**調整後**：
```javascript
const formData = reactive({
  name: '',
  area: '',
  chairmanName: '',
  chairmanPhone: ''
})
```

##### 1.3 移除 createUrbanRenewal 函數中的 memberCount
**位置**：第337-352行

**原始代碼**：
```javascript
const createUrbanRenewal = async (data) => {
  try {
    const response = await post('/urban-renewals', {
      name: data.name,
      area: parseFloat(data.area),
      memberCount: parseInt(data.memberCount),  // <-- 移除這行
      chairmanName: data.chairmanName,
      chairmanPhone: data.chairmanPhone
    })
    // ...
  }
}
```

**調整後**：
```javascript
const createUrbanRenewal = async (data) => {
  try {
    const response = await post('/urban-renewals', {
      name: data.name,
      area: parseFloat(data.area),
      chairmanName: data.chairmanName,
      chairmanPhone: data.chairmanPhone
    })
    // ...
  }
}
```

##### 1.4 移除 resetForm 中的 memberCount
**位置**：第426-432行

**原始代碼**：
```javascript
const resetForm = () => {
  formData.name = ''
  formData.area = ''
  formData.memberCount = ''  // <-- 移除這行
  formData.chairmanName = ''
  formData.chairmanPhone = ''
}
```

##### 1.5 移除測試資料填充中的 memberCount
**位置**：第435-484行 - `fillRandomTestData` 函數

**需要移除的代碼**：
```javascript
// Random member count between 15-150
const randomMemberCount = Math.floor(Math.random() * 135) + 15

// ...

formData.memberCount = randomMemberCount.toString()  // <-- 移除這行
```

##### 1.6 移除 onSubmit 驗證中的 memberCount
**位置**：第486-514行

**原始代碼**：
```javascript
const onSubmit = async () => {
  // Basic validation
  if (!formData.name || !formData.area || !formData.memberCount || ...) {  // <-- 移除 !formData.memberCount
    error.value = '請填寫所有必填項目'
    return
  }
  // ...
}
```

**調整後**：
```javascript
const onSubmit = async () => {
  // Basic validation
  if (!formData.name || !formData.area || !formData.chairmanName || !formData.chairmanPhone) {
    error.value = '請填寫所有必填項目'
    return
  }
  // ...
}
```

##### 1.7 列表顯示保持不變
**位置**：第175行（表頭）、第201行（資料）

**說明**：列表中可以繼續顯示所有權人數，因為這是統計資訊。但後端需要在查詢時自動計算並返回這個數值。

---

#### 2. `/frontend/pages/tables/urban-renewal/[id]/basic-info.vue`

##### 2.1 移除或改為唯讀的所有權人數欄位
**位置**：第60-69行

**選項A：完全移除**
```vue
<!-- 直接刪除整個 div 區塊 -->
```

**選項B：改為唯讀顯示**（推薦）
```vue
<div>
  <label for="memberCount" class="block text-sm font-medium text-gray-700 mb-2">所有權人數</label>
  <input
    id="memberCount"
    v-model="renewalData.member_count"
    type="number"
    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed"
    disabled
    readonly
  />
  <p class="mt-1 text-sm text-gray-500">此數值由系統自動統計</p>
</div>
```

##### 2.2 移除 saveChanges 中的 member_count
**位置**：第742-843行

**原始代碼**：
```javascript
const response = await put(`/urban-renewals/${route.params.id}`, {
  name: renewalData.name,
  area: parseFloat(renewalData.area),
  member_count: parseInt(renewalData.member_count),  // <-- 移除這行
  chairman_name: renewalData.chairman_name,
  // ...
})
```

**調整後**：
```javascript
const response = await put(`/urban-renewals/${route.params.id}`, {
  name: renewalData.name,
  area: parseFloat(renewalData.area),
  chairman_name: renewalData.chairman_name,
  // ...
})
```

##### 2.3 移除測試資料填充中的 member_count
**位置**：第867行

**需要移除的代碼**：
```javascript
renewalData.member_count = Math.floor(Math.random() * 100) + 20  // <-- 移除這行
```

---

### 二、Backend 後端調整

#### 1. `/backend/app/Models/UrbanRenewalModel.php`

##### 1.1 調整 allowedFields（移除 member_count）
**位置**：第16-25行

**原始代碼**：
```php
protected $allowedFields = [
    'name',
    'area',
    'member_count',  // <-- 移除這行
    'chairman_name',
    'chairman_phone',
    'address',
    'representative',
    'assigned_admin_id'
];
```

**調整後**：
```php
protected $allowedFields = [
    'name',
    'area',
    'chairman_name',
    'chairman_phone',
    'address',
    'representative',
    'assigned_admin_id'
];
```

##### 1.2 調整驗證規則（移除 member_count）
**位置**：第33-39行、第52-56行

**需要移除的代碼**：
```php
// 驗證規則
'member_count' => 'required|integer|greater_than[0]',

// 驗證訊息
'member_count' => [
    'required' => '所有權人數為必填項目',
    'integer' => '所有權人數必須為整數',
    'greater_than' => '所有權人數必須大於0'
],
```

##### 1.3 新增計算所有權人數的方法
**位置**：在類別中新增方法（建議加在最後）

**新增代碼**：
```php
/**
 * Calculate member count for an urban renewal
 * @param int $urbanRenewalId
 * @return int
 */
public function calculateMemberCount(int $urbanRenewalId): int
{
    $propertyOwnerModel = new \App\Models\PropertyOwnerModel();
    return $propertyOwnerModel->where('urban_renewal_id', $urbanRenewalId)->countAllResults();
}

/**
 * Get urban renewal with calculated member count
 * @param int $id
 * @return array|null
 */
public function getWithMemberCount(int $id): ?array
{
    $urbanRenewal = $this->find($id);
    if (!$urbanRenewal) {
        return null;
    }
    
    $urbanRenewal['member_count'] = $this->calculateMemberCount($id);
    return $urbanRenewal;
}

/**
 * Get all urban renewals with calculated member counts
 * @param int $page
 * @param int $perPage
 * @param int|null $urbanRenewalId
 * @return array
 */
public function getUrbanRenewalsWithMemberCount($page = 1, $perPage = 10, $urbanRenewalId = null)
{
    $urbanRenewals = $this->getUrbanRenewals($page, $perPage, $urbanRenewalId);
    
    // Add calculated member count to each record
    foreach ($urbanRenewals as &$renewal) {
        $renewal['member_count'] = $this->calculateMemberCount($renewal['id']);
    }
    
    return $urbanRenewals;
}
```

---

#### 2. `/backend/app/Controllers/Api/UrbanRenewalController.php`

##### 2.1 調整 index() 方法 - 使用新的計算方法
**位置**：第39-109行

**原始代碼**（第84-88行）：
```php
if ($search) {
    $data = $this->urbanRenewalModel->searchByName($search, $page, $perPage, $urbanRenewalId);
} else {
    $data = $this->urbanRenewalModel->getUrbanRenewals($page, $perPage, $urbanRenewalId);
}
```

**調整後**：
```php
if ($search) {
    $data = $this->urbanRenewalModel->searchByName($search, $page, $perPage, $urbanRenewalId);
    // Add calculated member count
    foreach ($data as &$renewal) {
        $renewal['member_count'] = $this->urbanRenewalModel->calculateMemberCount($renewal['id']);
    }
} else {
    $data = $this->urbanRenewalModel->getUrbanRenewalsWithMemberCount($page, $perPage, $urbanRenewalId);
}
```

**或者更簡潔的方式**：
```php
if ($search) {
    $data = $this->urbanRenewalModel->searchByName($search, $page, $perPage, $urbanRenewalId);
} else {
    $data = $this->urbanRenewalModel->getUrbanRenewals($page, $perPage, $urbanRenewalId);
}

// Add calculated member count to all results
foreach ($data as &$renewal) {
    $renewal['member_count'] = $this->urbanRenewalModel->calculateMemberCount($renewal['id']);
}
unset($renewal); // 解除引用
```

##### 2.2 調整 show() 方法 - 返回計算後的數值
**位置**：第115-176行

**原始代碼**（第156行）：
```php
$data = $this->urbanRenewalModel->getUrbanRenewal($id);
```

**調整後**：
```php
$data = $this->urbanRenewalModel->getWithMemberCount($id);
```

**或者**：
```php
$data = $this->urbanRenewalModel->getUrbanRenewal($id);
if ($data) {
    $data['member_count'] = $this->urbanRenewalModel->calculateMemberCount($id);
}
```

##### 2.3 調整 create() 方法 - 移除 member_count 處理
**位置**：第182-232行

**原始代碼**（第185-193、196-206行）：
```php
$data = [
    'name' => $this->request->getPost('name'),
    'area' => $this->request->getPost('area'),
    'member_count' => $this->request->getPost('memberCount') ?? $this->request->getPost('member_count'),  // <-- 移除
    // ...
];

// Handle JSON requests
if ($this->request->getHeaderLine('Content-Type') === 'application/json') {
    $json = $this->request->getJSON(true);
    $data = [
        'name' => $json['name'] ?? null,
        'area' => $json['area'] ?? null,
        'member_count' => $json['memberCount'] ?? $json['member_count'] ?? null,  // <-- 移除
        // ...
    ];
}
```

**調整後**：移除所有 member_count 相關的程式碼

##### 2.4 調整 update() 方法 - 移除 member_count 處理
**位置**：第238-339行

**原始代碼**（第291-299、302-309行）：
```php
$data = [
    'name' => $json['name'] ?? null,
    'area' => $json['area'] ?? null,
    'member_count' => $json['memberCount'] ?? $json['member_count'] ?? null,  // <-- 移除
    // ...
];

// 以及 POST 處理部分
$data = [
    'name' => $this->request->getPost('name'),
    'area' => $this->request->getPost('area'),
    'member_count' => $this->request->getPost('memberCount') ?? $this->request->getPost('member_count'),  // <-- 移除
    // ...
];
```

**調整後**：移除所有 member_count 相關的程式碼

---

### 三、Database 資料庫調整

#### 1. member_count 欄位保留策略

**選項A：保留欄位，改為可空值（推薦）**

理由：
- 保持向後相容
- 可以用於快取計算結果（效能優化）
- 不需要資料遷移

**新增 Migration**：
```php
// backend/app/Database/Migrations/YYYY-MM-DD-HHMMSS_MakeMemberCountNullable.php

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeMemberCountNullable extends Migration
{
    public function up()
    {
        $fields = [
            'member_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // 改為可空
                'comment'    => '所有權人數（系統自動計算）',
            ],
        ];
        
        $this->forge->modifyColumn('urban_renewals', $fields);
    }

    public function down()
    {
        $fields = [
            'member_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '所有權人數',
            ],
        ];
        
        $this->forge->modifyColumn('urban_renewals', $fields);
    }
}
```

**選項B：移除欄位（不推薦）**

理由：
- 需要資料遷移
- 影響現有資料
- 無法快取計算結果

如果選擇此方案，需要：
1. 新增 migration 移除 member_count 欄位
2. 確保所有相關程式碼都已調整完畢

---

### 四、觸發器/自動更新機制（選配）

如果希望在資料庫層面自動維護 member_count 欄位（快取策略），可以考慮：

#### 選項1：資料庫觸發器（MySQL/MariaDB）

```sql
-- 當新增所有權人時更新計數
DELIMITER $$
CREATE TRIGGER update_member_count_after_insert
AFTER INSERT ON property_owners
FOR EACH ROW
BEGIN
    UPDATE urban_renewals 
    SET member_count = (
        SELECT COUNT(*) 
        FROM property_owners 
        WHERE urban_renewal_id = NEW.urban_renewal_id
    )
    WHERE id = NEW.urban_renewal_id;
END$$

-- 當刪除所有權人時更新計數
CREATE TRIGGER update_member_count_after_delete
AFTER DELETE ON property_owners
FOR EACH ROW
BEGIN
    UPDATE urban_renewals 
    SET member_count = (
        SELECT COUNT(*) 
        FROM property_owners 
        WHERE urban_renewal_id = OLD.urban_renewal_id
    )
    WHERE id = OLD.urban_renewal_id;
END$$

DELIMITER ;
```

#### 選項2：Model 事件處理（推薦）

在 `PropertyOwnerModel.php` 中新增：

```php
protected $afterInsert = ['updateUrbanRenewalMemberCount'];
protected $afterDelete = ['updateUrbanRenewalMemberCount'];

protected function updateUrbanRenewalMemberCount(array $data)
{
    try {
        $urbanRenewalId = null;
        
        // Get urban_renewal_id from different scenarios
        if (isset($data['data']['urban_renewal_id'])) {
            $urbanRenewalId = $data['data']['urban_renewal_id'];
        } elseif (isset($data['id'])) {
            $owner = $this->find($data['id']);
            $urbanRenewalId = $owner['urban_renewal_id'] ?? null;
        }
        
        if ($urbanRenewalId) {
            $urbanRenewalModel = new \App\Models\UrbanRenewalModel();
            $count = $this->where('urban_renewal_id', $urbanRenewalId)->countAllResults();
            $urbanRenewalModel->update($urbanRenewalId, ['member_count' => $count]);
        }
    } catch (\Exception $e) {
        log_message('error', 'Failed to update member count: ' . $e->getMessage());
    }
    
    return $data;
}
```

---

## 實施步驟建議

### Phase 1：準備階段
1. 備份資料庫
2. 創建 feature branch：`git checkout -b remove-member-count-manual-input`

### Phase 2：後端調整
1. 執行資料庫 migration（使 member_count 可空）
2. 調整 `UrbanRenewalModel.php`
   - 移除 allowedFields 和驗證規則中的 member_count
   - 新增計算方法
3. 調整 `UrbanRenewalController.php`
   - 移除 create/update 中的 member_count 處理
   - 在 index/show 中返回計算後的值
4. （選配）在 `PropertyOwnerModel.php` 新增自動更新機制

### Phase 3：前端調整
1. 調整 `/frontend/pages/tables/urban-renewal/index.vue`
   - 移除新建表單中的所有權人數欄位
   - 移除相關的表單資料和驗證
2. 調整 `/frontend/pages/tables/urban-renewal/[id]/basic-info.vue`
   - 將所有權人數欄位改為唯讀或移除
   - 移除儲存時的 member_count

### Phase 4：測試
1. 測試新建更新會（不輸入所有權人數）
2. 測試編輯更新會（所有權人數為唯讀）
3. 測試列表顯示（確認顯示正確的統計數字）
4. 測試新增/刪除所有權人後，更新會的所有權人數是否自動更新

### Phase 5：部署
1. Code review
2. 合併到主分支
3. 部署到測試環境
4. 部署到生產環境

---

## 相關檔案清單

### Frontend
- `/frontend/pages/tables/urban-renewal/index.vue` ⚠️ 必須調整
- `/frontend/pages/tables/urban-renewal/[id]/basic-info.vue` ⚠️ 必須調整

### Backend
- `/backend/app/Models/UrbanRenewalModel.php` ⚠️ 必須調整
- `/backend/app/Controllers/Api/UrbanRenewalController.php` ⚠️ 必須調整
- `/backend/app/Models/PropertyOwnerModel.php` ⚙️ 選配調整（自動更新機制）
- `/backend/app/Database/Migrations/新增_MakeMemberCountNullable.php` ⚙️ 需要新增

### Database
- `urban_renewals` 資料表 ⚠️ 需要 migration
- `property_owners` 資料表 📊 用於計算

---

## 注意事項

1. **資料完整性**：確保所有現有的更新會資料在調整後仍能正確顯示所有權人數
2. **效能考量**：如果更新會數量很大，考慮使用快取或資料庫觸發器來維護 member_count 欄位
3. **向後相容**：如果有其他系統使用 API，需要通知他們不要再傳送 member_count
4. **測試覆蓋**：確保 TDD 測試案例也一併調整

---

## 預期效果

調整完成後：
- ✅ 使用者無法手動輸入或修改所有權人數
- ✅ 所有權人數由系統自動統計
- ✅ 新增/刪除所有權人後，數字會自動更新
- ✅ 資料準確性提升，不會有人為輸入錯誤

---

**文件版本**：1.0  
**建立日期**：2025-11-15  
**最後更新**：2025-11-15
