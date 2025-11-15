# 土地面積自動計算規劃 - 改為統計欄位

## 概述

更新會的 `area` (土地面積) 目前是手動輸入欄位，但應該是由系統自動統計該更新會所有所有權人擁有的土地面積加總。本文件說明需要調整的策略和實現方式。

## 問題分析

### 當前狀況
1. **新建更新會**（`/tables/urban-renewal` - index.vue）
   - 表單中包含「土地面積」輸入欄位
   - 使用者需要手動輸入面積
   - 提交時包含在 POST 請求中

2. **編輯更新會**（`/tables/urban-renewal/[id]/basic-info.vue`）
   - 基本資訊區塊包含「土地面積」輸入欄位
   - 使用者可以手動修改
   - 儲存時包含在 PUT 請求中

3. **列表顯示**（`/tables/urban-renewal` - index.vue）
   - 表格中顯示 `area` 欄位
   - 顯示的是資料庫中儲存的值，不是計算值

### 應該的行為
- `area` 應該是**唯讀統計欄位**
- 數值應該自動計算：該更新會下所有所有權人的土地面積加總
- 計算規則：
  - 遍歷該更新會的所有 `property_owners` (所有權人)
  - 對每位所有權人，計算其所有土地面積加總 (已在 PropertyOwnerModel.calculateTotalAreas 實現)
  - 將所有所有權人的土地面積累加
  - 四捨五入到 2 位小數
- 不應該在新建或編輯表單中接受手動輸入
- 列表中顯示計算結果

### 資料結構分析

**相關表**：
- `urban_renewals` - 更新會（目前包含 area 欄位）
- `property_owners` - 所有權人
- `owner_land_ownership` - 所有權人與地號的所有權關係（ownership_numerator/ownership_denominator）
- `land_plots` - 地號資料（land_area 欄位）

**計算路徑**：
```
urban_renewals (id=1)
    ↓ (一對多)
property_owners (urban_renewal_id=1)
    ↓ (一對多，通過 owner_land_ownership)
land_plots (land_area)

計算：
總面積 = SUM(
    SELECT land_plots.land_area * 
           (owner_land_ownership.ownership_numerator / owner_land_ownership.ownership_denominator)
    FROM property_owners
    JOIN owner_land_ownership ON property_owners.id = owner_land_ownership.property_owner_id
    JOIN land_plots ON owner_land_ownership.land_plot_id = land_plots.id
    WHERE property_owners.urban_renewal_id = 1
)
```

## 需要調整的地方

### 一、Frontend 前端調整

#### 1. `/frontend/pages/tables/urban-renewal/index.vue`

##### 1.1 移除新建表單中的土地面積欄位
**位置**：新建表單的土地面積輸入欄
**操作**：刪除整個土地面積 `<div>` 區塊

##### 1.2 移除表單資料中的 area
**位置**：formData reactive 物件
**原始**：
```javascript
const formData = reactive({
  name: '',
  chairmanName: '',
  chairmanPhone: ''
})
```
**保持不變** ✅（已移除）

##### 1.3 移除 createUrbanRenewal 函數中的 area
**位置**：POST 請求的資料
**原始**：
```javascript
const response = await post('/urban-renewals', {
  name: data.name,
  chairmanName: data.chairmanName,
  chairmanPhone: data.chairmanPhone
})
```
**保持不變** ✅（已移除）

##### 1.4 列表保持顯示 area
**位置**：表格中的列
**說明**：列表中繼續顯示 area，但值由後端計算返回

---

#### 2. `/frontend/pages/tables/urban-renewal/[id]/basic-info.vue`

##### 2.1 改為唯讀顯示土地面積
**位置**：基本資訊區塊

**當前狀況**：無土地面積欄位
**新增改進**：可加入唯讀土地面積顯示區塊（可選）

```vue
<!-- 土地面積（唯讀統計） -->
<div v-if="renewalData.area !== undefined">
  <label class="block text-sm font-medium text-gray-700 mb-2">土地面積(平方公尺)</label>
  <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
    <p class="text-sm text-gray-900">{{ formatArea(renewalData.area) }}</p>
  </div>
  <p class="mt-1 text-sm text-gray-500">此數值由系統自動統計所有權人的土地面積加總</p>
</div>
```

##### 2.2 不在 saveChanges 中提交 area
**位置**：PUT 請求
**說明**：保持不變 ✅（已移除）

---

### 二、Backend 後端調整

#### 1. `/backend/app/Models/UrbanRenewalModel.php`

##### 1.1 調整 allowedFields（保持移除 area）
**位置**：allowedFields 屬性
**說明**：area 已從 allowedFields 移除 ✅

##### 1.2 新增計算土地面積的方法
**位置**：在類別中新增方法（建議加在 calculateMemberCount 方法之後）

**新增方法**：

```php
/**
 * Calculate total land area for an urban renewal
 * Sums up all property owners' land area for this urban renewal
 * 
 * @param int $urbanRenewalId
 * @return float
 */
public function calculateTotalLandArea(int $urbanRenewalId): float
{
    $propertyOwnerModel = new \App\Models\PropertyOwnerModel();
    $ownerLandModel = new \App\Models\OwnerLandOwnershipModel();
    $landPlotModel = new \App\Models\LandPlotModel();

    // Get all property owners for this urban renewal
    $propertyOwners = $propertyOwnerModel
        ->where('urban_renewal_id', $urbanRenewalId)
        ->findAll();

    $totalLandArea = 0;

    // For each property owner, sum their land areas
    foreach ($propertyOwners as $owner) {
        $ownerTotals = $propertyOwnerModel->calculateTotalAreas($owner['id']);
        $totalLandArea += $ownerTotals['total_land_area'] ?? 0;
    }

    return round($totalLandArea, 2);
}

/**
 * Get urban renewal with calculated total land area
 * 
 * @param int $id
 * @return array|null
 */
public function getWithCalculatedArea(int $id): ?array
{
    $urbanRenewal = $this->find($id);
    if (!$urbanRenewal) {
        return null;
    }
    
    $urbanRenewal['area'] = $this->calculateTotalLandArea($id);
    return $urbanRenewal;
}

/**
 * Get all urban renewals with calculated total land area
 * 
 * @param int $page
 * @param int $perPage
 * @param int|null $urbanRenewalId
 * @return array
 */
public function getUrbanRenewalsWithCalculatedArea($page = 1, $perPage = 10, $urbanRenewalId = null)
{
    $urbanRenewals = $this->getUrbanRenewals($page, $perPage, $urbanRenewalId);
    
    // Add calculated land area to each record
    foreach ($urbanRenewals as &$renewal) {
        $renewal['area'] = $this->calculateTotalLandArea($renewal['id']);
    }
    unset($renewal);
    
    return $urbanRenewals;
}
```

##### 1.3 更新已有方法以包含 area 計算
**位置**：getUrbanRenewalsWithMemberCount 方法

**修改說明**：
```php
/**
 * Get all urban renewals with calculated member counts AND land areas
 * 
 * @param int $page
 * @param int $perPage
 * @param int|null $urbanRenewalId
 * @return array
 */
public function getUrbanRenewalsWithMemberCount($page = 1, $perPage = 10, $urbanRenewalId = null)
{
    $urbanRenewals = $this->getUrbanRenewals($page, $perPage, $urbanRenewalId);
    
    // Add calculated member count AND land area to each record
    foreach ($urbanRenewals as &$renewal) {
        $renewal['member_count'] = $this->calculateMemberCount($renewal['id']);
        $renewal['area'] = $this->calculateTotalLandArea($renewal['id']);  // NEW
    }
    unset($renewal);
    
    return $urbanRenewals;
}
```

##### 1.4 更新 getWithMemberCount 方法
**位置**：getWithMemberCount 方法

**修改說明**：
```php
/**
 * Get urban renewal with calculated member count AND land area
 * 
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
    $urbanRenewal['area'] = $this->calculateTotalLandArea($id);  // NEW
    return $urbanRenewal;
}
```

---

#### 2. `/backend/app/Controllers/Api/UrbanRenewalController.php`

##### 2.1 index() 方法已正確
**位置**：index 方法
**說明**：已使用 getUrbanRenewalsWithMemberCount，需要確保其包含 area 計算 ✅

##### 2.2 show() 方法已正確
**位置**：show 方法
**說明**：已使用 getWithMemberCount，需要確保其包含 area 計算 ✅

##### 2.3 create() 方法已正確
**位置**：create 方法
**說明**：已使用 getWithMemberCount 返回，無需再調整 ✅

##### 2.4 update() 方法已正確
**位置**：update 方法
**說明**：已使用 getWithMemberCount 返回，無需再調整 ✅

---

### 三、Database 資料庫調整

#### 1. area 欄位保留策略

**選擇方案**：**保留欄位作為快取欄位**（推薦）

理由：
- 保持資料結構一致
- 可以存儲快取計算結果以提升查詢效能
- 避免頻繁重新計算
- 便於未來的效能優化

#### 2. 建立觸發器/自動更新機制

**選擇方案**：**Model 事件處理 + 定期更新**（推薦）

**步驟**：

##### 步驟1：在 OwnerLandOwnershipModel 中新增事件處理

```php
protected $afterInsert = ['updateUrbanRenewalLandArea'];
protected $afterUpdate = ['updateUrbanRenewalLandArea'];
protected $afterDelete = ['updateUrbanRenewalLandArea'];

/**
 * Update urban renewal land area when property owner's land changes
 */
protected function updateUrbanRenewalLandArea(array $data)
{
    try {
        // Get the property owner
        $propertyOwnerModel = new \App\Models\PropertyOwnerModel();
        
        $propertyOwnerId = null;
        if (isset($data['data']['property_owner_id'])) {
            $propertyOwnerId = $data['data']['property_owner_id'];
        } elseif (isset($data['id'])) {
            $ownership = $this->find($data['id']);
            $propertyOwnerId = $ownership['property_owner_id'] ?? null;
        }
        
        if ($propertyOwnerId) {
            $propertyOwner = $propertyOwnerModel->find($propertyOwnerId);
            if ($propertyOwner && isset($propertyOwner['urban_renewal_id'])) {
                $urbanRenewalId = $propertyOwner['urban_renewal_id'];
                
                // 觸發更新機制
                log_message('info', 'Land area changed for urban_renewal_id: ' . $urbanRenewalId);
                
                // 可選：異步更新快取欄位（需要實現事件隊列）
                // 或在下次查詢時實時計算
            }
        }
    } catch (\Exception $e) {
        log_message('error', 'Failed to update urban renewal land area: ' . $e->getMessage());
    }
    
    return $data;
}
```

##### 步驟2：在 UrbanRenewalModel 中新增快取更新方法（可選）

```php
/**
 * Update area cache for an urban renewal
 * This is called periodically or when property owners change
 * 
 * @param int $urbanRenewalId
 * @return bool
 */
public function updateAreaCache(int $urbanRenewalId): bool
{
    try {
        $calculatedArea = $this->calculateTotalLandArea($urbanRenewalId);
        return $this->update($urbanRenewalId, ['area' => $calculatedArea]);
    } catch (\Exception $e) {
        log_message('error', 'Failed to update area cache: ' . $e->getMessage());
        return false;
    }
}
```

---

### 四、建立 Migration（可選）

#### 1. 添加快取更新觸發器或定期任務

**說明**：可建立 migration 添加資料庫層面的快取更新機制，或在應用層通過 Cron Job 實現

**方案 A：MariaDB 觸發器**（不推薦，複雜且難維護）

**方案 B：定期後台任務**（推薦）
- 建立 Cron Job 定期更新所有 urban_renewals 的 area 快取欄位
- 可在 `/app/Commands/` 中建立 Command

```php
// app/Commands/UpdateUrbanRenewalAreas.php
<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UrbanRenewalModel;

class UpdateUrbanRenewalAreas extends BaseCommand
{
    protected $group       = 'Cache';
    protected $name        = 'cache:update-renewal-areas';
    protected $description = 'Update urban renewal land area cache for all renewals';

    public function run(array $params = [])
    {
        $urbanRenewalModel = new UrbanRenewalModel();
        $renewals = $urbanRenewalModel->findAll();
        
        $updated = 0;
        foreach ($renewals as $renewal) {
            if ($urbanRenewalModel->updateAreaCache($renewal['id'])) {
                $updated++;
            }
        }
        
        CLI::write("Updated {$updated} renewal areas", 'green');
    }
}
```

---

## 實施步驟建議

### Phase 1：準備階段
1. 備份資料庫
2. 創建 feature branch：`git checkout -b calculate-land-area-automatically`

### Phase 2：後端調整
1. 調整 `UrbanRenewalModel.php`
   - 新增 `calculateTotalLandArea()` 方法
   - 新增 `getWithCalculatedArea()` 方法
   - 新增 `getUrbanRenewalsWithCalculatedArea()` 方法
   - 更新 `getUrbanRenewalsWithMemberCount()` 加入面積計算
   - 更新 `getWithMemberCount()` 加入面積計算
   
2. 調整 `OwnerLandOwnershipModel.php`
   - 新增 `updateUrbanRenewalLandArea()` 事件處理方法
   - 設定 afterInsert、afterUpdate、afterDelete 回調
   
3. （可選）新增快取更新 Command
   - `/app/Commands/UpdateUrbanRenewalAreas.php`

4. 驗證後端邏輯
   - 測試計算結果是否正確

### Phase 3：前端調整
1. 調整 `/frontend/pages/tables/urban-renewal/index.vue`
   - 列表繼續顯示 area（從 API 返回的計算值）
   
2. 調整 `/frontend/pages/tables/urban-renewal/[id]/basic-info.vue`
   - （可選）新增唯讀土地面積顯示

### Phase 4：資料庫調整
1. （可選）建立 migration 刷新現有資料快取
   ```php
   // Migration 建立 Command 執行快取更新
   ```

2. 設定 Cron Job 定期更新快取（可選）

### Phase 5：測試
1. 測試新建更新會（area 為 NULL）
2. 測試新增所有權人後，area 是否正確計算
3. 測試編輯所有權人的地號後，area 是否更新
4. 測試刪除所有權人後，area 是否正確減少
5. 測試列表顯示是否正確
6. 測試詳情頁面顯示是否正確

### Phase 6：效能優化
1. 添加適當的資料庫索引
   - `urban_renewals.id`
   - `property_owners.urban_renewal_id`
   - `owner_land_ownership.property_owner_id`
   - `owner_land_ownership.land_plot_id`
   
2. 考慮面積快取的更新策略
   - 實時計算 vs 定期快取更新

### Phase 7：部署
1. Code review
2. 合併到主分支
3. 部署到測試環境
4. 部署到生產環境

---

## 相關檔案清單

### Frontend
- `/frontend/pages/tables/urban-renewal/index.vue` ✅ 列表保持顯示 area
- `/frontend/pages/tables/urban-renewal/[id]/basic-info.vue` 🔄 可新增唯讀顯示

### Backend
- `/backend/app/Models/UrbanRenewalModel.php` ⚠️ 需要新增面積計算方法
- `/backend/app/Models/OwnerLandOwnershipModel.php` ⚠️ 需要新增事件處理
- `/backend/app/Controllers/Api/UrbanRenewalController.php` ✅ 無需改動（已使用計算方法）
- `/backend/app/Commands/UpdateUrbanRenewalAreas.php` 🆕 可選新增

### Database
- `urban_renewals` 表 💾 area 欄位保留作為快取
- `property_owners` 表 📊 用於查詢所有權人
- `owner_land_ownership` 表 📊 用於計算持有比例
- `land_plots` 表 📊 用於取得地號面積

---

## 計算邏輯驗證範例

### 範例情景
```
更新會 ID=1

所有權人1：
  - 地號A (面積 100 m²) 持有 1/2 → 50 m²
  - 地號B (面積 200 m²) 持有 1/3 → 66.67 m²
  小計：116.67 m²

所有權人2：
  - 地號C (面積 300 m²) 持有 1/1 → 300 m²
  小計：300 m²

更新會1的總土地面積 = 116.67 + 300 = 416.67 m²
```

### 資料庫查詢驗證
```sql
SELECT ROUND(SUM(
    lp.land_area * (olo.ownership_numerator / olo.ownership_denominator)
), 2) as total_area
FROM property_owners po
LEFT JOIN owner_land_ownership olo ON po.id = olo.property_owner_id
LEFT JOIN land_plots lp ON olo.land_plot_id = lp.id
WHERE po.urban_renewal_id = 1
  AND lp.id IS NOT NULL;
```

---

## 注意事項

1. **計算效能**：
   - 土地面積計算涉及多表聯接，應添加適當索引
   - 對於大量資料，建議快取計算結果到 area 欄位
   - 定期通過 Cron Job 更新快取

2. **資料一致性**：
   - 所有權人、地號、持有比例變更時需要觸發面積重新計算
   - 建議在 Model 事件中自動更新

3. **邊界情況處理**：
   - 新建更新會時 area 應為 NULL 或 0
   - 刪除所有所有權人後，area 應為 0
   - 地號面積為 NULL 時應忽略計算

4. **向後相容**：
   - area 欄位仍在資料庫中保留
   - API 返回 area 欄位（計算值或快取值）
   - 現有前端代碼無需調整

5. **測試覆蓋**：
   - 確保所有計算邏輯有單元測試
   - 測試各種邊界情況

---

## 預期效果

調整完成後：
- ✅ 使用者無法手動輸入土地面積
- ✅ 土地面積由系統自動統計所有權人的地號面積
- ✅ 新增/修改/刪除所有權人或地號後，面積會自動更新或在下次查詢時計算
- ✅ 列表和詳情頁面顯示準確的土地面積統計值
- ✅ 資料準確性提升，消除人為輸入錯誤
- ✅ 系統可維護性提升，邏輯集中在後端

---

**文件版本**：1.0  
**建立日期**：2025-11-15  
**狀態**：規劃階段 - 待實施
