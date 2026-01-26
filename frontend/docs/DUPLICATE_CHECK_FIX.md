# 前端重複資料檢查修復報告

## 修復目的
防止使用者在新增所有權人時，重複新增相同的地號或建號，避免後端產生 "Duplicate entry" 錯誤。

---

## 修改的檔案

### 1. **frontend/composables/useLandBuilding.js**

#### 修改內容：

**addLand() 函數 - 新增地號重複檢查**
```javascript
const addLand = (lands, landData, availablePlots = []) => {
  // ✅ 新增：檢查是否已存在相同的地號
  const isDuplicate = lands.some(land => land.plot_number === landData.plot_number)
  
  if (isDuplicate) {
    throw new Error(`地號 ${landData.plot_number} 已經存在，請勿重複新增`)
  }
  
  // ... 原有邏輯
}
```

**addBuilding() 函數 - 新增建號重複檢查**
```javascript
const addBuilding = (buildings, buildingData, locationData) => {
  // ✅ 新增：檢查是否已存在相同的建號
  const isDuplicate = buildings.some(building =>
    building.county === buildingData.county &&
    building.district === buildingData.district &&
    building.section === buildingData.section &&
    building.building_number_main === buildingData.building_number_main &&
    building.building_number_sub === buildingData.building_number_sub
  )
  
  if (isDuplicate) {
    const buildingNumber = buildingData.building_number_sub 
      ? `${buildingData.building_number_main}-${buildingData.building_number_sub}`
      : buildingData.building_number_main
    throw new Error(`建號 ${buildingNumber} 已經存在，請勿重複新增`)
  }
  
  // ... 原有邏輯
}
```

**影響範圍：**
- ✅ Property Owner Create 頁面
- ✅ Property Owner Edit 頁面
- ✅ 所有使用 useLandBuilding composable 的地方

---

### 2. **frontend/composables/usePropertyOwnerForm.js**

#### 修改內容：

**addLand() 函數 - 錯誤處理**
```javascript
const addLand = () => {
  try {
    addLandHelper(formData.lands, landForm, availablePlots.value)
    resetLandForm(landForm)
    showAddLandModal.value = false
  } catch (error) {
    // ✅ 新增：捕捉並顯示錯誤訊息
    showError('新增失敗', error.message || '無法新增地號')
  }
}
```

**addBuilding() 函數 - 錯誤處理**
```javascript
const addBuilding = () => {
  try {
    addBuildingHelper(...)
    // ... 清理邏輯
  } catch (error) {
    // ✅ 新增：捕捉並顯示錯誤訊息
    showError('新增失敗', error.message || '無法新增建號')
  }
}
```

**影響範圍：**
- ✅ 所有權人新增頁面
- ✅ 所有權人編輯頁面

---

### 3. **frontend/pages/tables/urban-renewal/[id]/basic-info.vue**

#### 修改內容：

**addLandPlot() 函數 - 更新會地號重複檢查**
```javascript
const addLandPlot = () => {
  if (!canAddLandPlot.value) return

  // ... 準備資料
  
  // ✅ 新增：檢查是否已存在相同的地號
  const isDuplicate = landPlots.value.some(plot => {
    const existingCounty = plot.county
    const existingDistrict = plot.district
    const existingSection = plot.section
    const existingMain = plot.landNumberMain || plot.land_number_main
    const existingSub = plot.landNumberSub || plot.land_number_sub || ''

    const newCounty = selectedCounty?.code || landForm.county
    const newDistrict = selectedDistrict?.code || landForm.district
    const newSection = selectedSection?.code || landForm.section
    const newMain = landForm.landNumberMain
    const newSub = landForm.landNumberSub || ''

    return existingCounty === newCounty &&
           existingDistrict === newDistrict &&
           existingSection === newSection &&
           existingMain === newMain &&
           existingSub === newSub
  })

  if (isDuplicate) {
    showError('新增失敗', `地號 ${fullLandNumber} 已經存在，請勿重複新增`)
    return
  }
  
  // ... 原有新增邏輯
}
```

**影響範圍：**
- ✅ 更新會基本資料頁面的地號管理

---

## 檢查覆蓋率

### ✅ 已檢查並修復的頁面

| 頁面 | 路徑 | 功能 | 狀態 |
|------|------|------|------|
| 所有權人新增 | `/tables/urban-renewal/[id]/property-owners/create` | 新增地號、建號 | ✅ 已修復 |
| 所有權人編輯 | `/tables/urban-renewal/[id]/property-owners/[ownerId]/edit` | 新增地號、建號 | ✅ 已修復 |
| 更新會基本資料 | `/tables/urban-renewal/[id]/basic-info` | 新增地號 | ✅ 已修復 |

### ✅ 已檢查但不需修復的頁面

| 頁面 | 路徑 | 原因 |
|------|------|------|
| 共有部分新增 | `/tables/urban-renewal/[id]/joint-common-areas/create` | 單一建號新增，無陣列累加 |
| 共有部分列表 | `/tables/urban-renewal/[id]/joint-common-areas/index` | 僅顯示，無新增功能 |
| 所有權人列表 | `/tables/urban-renewal/[id]/property-owners/index` | 僅顯示，無新增功能 |

---

## 使用者體驗改善

### 修復前
```
使用者新增地號：台北市大安區段0001
→ 不小心再次新增：台北市大安區段0001
→ 前端允許加入陣列
→ 送出到後端
→ 後端報錯："Duplicate entry '7-1' for key 'unique_owner_land'"
→ 使用者看到 500 錯誤
```

### 修復後
```
使用者新增地號：台北市大安區段0001
→ 嘗試再次新增：台北市大安區段0001
→ 前端即時檢查發現重複
→ 顯示錯誤訊息："地號 台北市大安區段0001 已經存在，請勿重複新增"
→ Modal 不關閉，使用者可以修改
→ 避免無效的 API 請求
```

---

## 測試建議

### 測試案例 1：所有權人新增 - 重複地號
1. 進入所有權人新增頁面
2. 點擊「新增地號」
3. 選擇地號 0001，填入持分資料，送出
4. 再次點擊「新增地號」
5. 選擇相同地號 0001
6. **預期結果**：顯示錯誤訊息「地號 0001 已經存在，請勿重複新增」

### 測試案例 2：所有權人新增 - 重複建號
1. 進入所有權人新增頁面
2. 點擊「新增建號」
3. 填入縣市、行政區、段、建號主號 0001、建號副號 0，送出
4. 再次點擊「新增建號」
5. 填入相同的建號資料
6. **預期結果**：顯示錯誤訊息「建號 0001-0 已經存在，請勿重複新增」

### 測試案例 3：更新會基本資料 - 重複地號
1. 進入更新會基本資料頁面
2. 在地號區塊新增地號：台北市/大安區/段/0001
3. 嘗試再次新增相同地號
4. **預期結果**：顯示錯誤訊息「地號 台北市大安區段0001 已經存在，請勿重複新增」

### 測試案例 4：填入測試資料
1. 進入所有權人新增頁面
2. 點擊「填入測試資料」按鈕
3. 多次點擊「新增地號」的「填入測試資料」
4. **預期結果**：如果隨機選到相同地號，應顯示錯誤訊息

---

## 後端防禦層

即使前端已檢查，後端仍有以下防禦機制：

### PropertyOwnerRepository::syncLands()
```php
// 去重：確保同一個 land_plot_id 只出現一次
$uniqueLands = [];
foreach ($lands as $land) {
    $landId = $land->getLandPlotId();
    if (!isset($uniqueLands[$landId])) {
        $uniqueLands[$landId] = $land;
    } else {
        log_message('warning', "Duplicate land_plot_id {$landId} for owner {$ownerId}, skipping duplicate");
    }
}
```

**雙重保護機制**：
- ✅ 前端：立即反饋，改善使用者體驗
- ✅ 後端：最後防線，避免資料庫錯誤

---

## 總結

✅ **完成修復的功能點**
1. 所有權人新增/編輯頁面 - 地號重複檢查
2. 所有權人新增/編輯頁面 - 建號重複檢查
3. 更新會基本資料頁面 - 地號重複檢查
4. 統一的錯誤提示訊息
5. 後端去重邏輯作為最後防線

✅ **改善的使用者體驗**
- 即時錯誤提示
- 避免無效的 API 請求
- 清楚的錯誤訊息指引

✅ **系統穩定性提升**
- 避免資料庫約束違反錯誤
- 前後端雙重驗證
- 詳細的錯誤日誌記錄
