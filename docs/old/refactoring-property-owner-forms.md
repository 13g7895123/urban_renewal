# Property Owner 表單重構計畫

## 執行摘要

本文件記錄了 Property Owner 新增/編輯頁面的重構計畫。經過詳細分析,兩個頁面的程式碼相似度高達 **75-80%**,透過元件化和邏輯抽取,可以削減約 **60%** 的程式碼量(從 2117 行減少到約 850 行)。

## 目錄

- [背景分析](#背景分析)
- [相似度分析](#相似度分析)
- [重構方案](#重構方案)
- [實作計畫](#實作計畫)
- [預期收益](#預期收益)
- [技術細節](#技術細節)

---

## 背景分析

### 相關檔案

| 檔案 | 路徑 | 行數 |
|------|------|------|
| Create 頁面 | `/frontend/pages/tables/urban-renewal/[id]/property-owners/create.vue` | 1091 行 |
| Edit 頁面 | `/frontend/pages/tables/urban-renewal/[id]/property-owners/[ownerId]/edit.vue` | 1026 行 |
| **總計** | | **2117 行** |

### 核心功能

兩個頁面都包含以下核心功能:
1. 所有權人基本資料表單
2. 地號管理(新增、刪除、列表顯示)
3. 建號管理(新增、刪除、列表顯示)
4. 縣市/行政區/段小段級聯選擇
5. 表單驗證與提交

---

## 相似度分析

### 整體相似度: **75-80%**

### 完全相同的部分 (100%)

| 模組 | 說明 | 行數估計 |
|------|------|----------|
| 基本資料表單 | 所有權人名稱、身分證字號、電話、地址等欄位 | ~150 行 |
| 地號新增 Modal | 地號選擇、持有比例輸入 | ~80 行 |
| 建號新增 Modal | 縣市/行政區/段小段級聯、建號輸入 | ~150 行 |
| 共用函數 | `addLand()`, `addBuilding()`, `removeLand()`, `removeBuilding()` 等 | ~200 行 |

### 細微差異的部分 (95% 相似)

| 模組 | Create 版本 | Edit 版本 | 差異 |
|------|------------|----------|------|
| 地號列表 | 簡單表頭 | 表頭 + 重新整理按鈕 | +10 行 |
| 建號列表 | 簡單表頭 | 表頭 + 重新整理按鈕 | +10 行 |
| 建號顯示 | 直接顯示 | 使用格式化函數 | ~5 行 |
| 地號顯示 | 直接顯示 | 使用格式化函數 | ~5 行 |
| Modal 測試按鈕 | 無 | 有「填入測試資料」 | +20 行 |

### 主要差異的部分

| 差異點 | Create | Edit |
|--------|--------|------|
| API 方法 | `POST /property-owners` | `PUT /property-owners/{id}` |
| 初始化 | 生成編號、載入基本資料 | 載入現有數據 |
| Loading 狀態 | 有進度條的 Loading Overlay | 無 Loading Overlay |
| 測試數據 | 統一在主表單 | 分散在各 Modal |
| 特有功能 | `generateOwnerCode()`, `getChineseLandNumber()` | `formatBuildingNumber()`, `reloadBuildings()` |

---

## 重構方案

### 目標檔案結構

```
frontend/
├── components/
│   └── PropertyOwner/
│       ├── BaseInfoForm.vue          # 基本資料表單 (新)
│       ├── LandTable.vue             # 地號列表 (新)
│       ├── BuildingTable.vue         # 建號列表 (新)
│       ├── LandModal.vue             # 地號新增 Modal (新)
│       └── BuildingModal.vue         # 建號新增 Modal (新)
├── composables/
│   ├── usePropertyOwnerForm.js       # 核心表單邏輯 (新)
│   ├── useLocationCascade.js         # 縣市/行政區/段小段 (新)
│   └── useLandBuilding.js            # 地號/建號管理 (新)
└── pages/
    └── tables/urban-renewal/[id]/property-owners/
        ├── create.vue                # 簡化到 ~150 行
        └── [ownerId]/edit.vue        # 簡化到 ~150 行
```

### 預期程式碼行數

| 模組 | 行數 |
|------|------|
| 共用元件 (5 個) | ~300 行 |
| Composables (3 個) | ~250 行 |
| Create 頁面 | ~150 行 |
| Edit 頁面 | ~150 行 |
| **總計** | **~850 行** |
| **削減比例** | **60%** |

---

## 實作計畫

### 方案 A: 漸進式重構 (推薦) ✅

採用分階段重構,每個階段都可獨立測試和驗證。

#### 階段 1: 抽取 Composable

**工作量**: 2-3 小時
**優先級**: 🔥 最高
**風險**: 低

**任務清單**:
- [ ] 創建 `composables/usePropertyOwnerForm.js`
- [ ] 移動共用的表單狀態 (`formData`, `landForm`, `buildingForm`)
- [ ] 移動共用的方法 (`addLand`, `addBuilding`, `removeLand`, `removeBuilding`)
- [ ] 移動縣市/行政區/段小段邏輯
- [ ] 在 Create 和 Edit 頁面中使用 composable
- [ ] 測試所有功能

**收益**:
- 立即減少約 200 行重複程式碼
- 邏輯集中管理,易於維護
- 為後續階段打下基礎

#### 階段 2: 抽取 Modal 元件

**工作量**: 3-4 小時
**優先級**: 🔥 高
**風險**: 低

**任務清單**:
- [ ] 創建 `components/PropertyOwner/LandModal.vue`
  - Props: `isOpen`, `availablePlots`, `showTestButton`
  - Events: `close`, `submit`
- [ ] 創建 `components/PropertyOwner/BuildingModal.vue`
  - Props: `isOpen`, `counties`, `showTestButton`
  - Events: `close`, `submit`
- [ ] 在兩個頁面中替換 Modal 為元件
- [ ] 測試 Modal 開關、表單提交、驗證

**收益**:
- 減少約 300 行重複程式碼
- Modal 邏輯獨立,易於測試
- 提高程式碼可讀性

#### 階段 3: 抽取列表元件

**工作量**: 2-3 小時
**優先級**: 🔶 中
**風險**: 低

**任務清單**:
- [ ] 創建 `components/PropertyOwner/LandTable.vue`
  - Props: `lands`, `showReloadButton`, `isReloading`
  - Events: `remove`, `reload`
- [ ] 創建 `components/PropertyOwner/BuildingTable.vue`
  - Props: `buildings`, `showReloadButton`, `isReloading`, `formatNumber`
  - Events: `remove`, `reload`
- [ ] 在兩個頁面中替換表格為元件
- [ ] 測試列表顯示、刪除、重新整理功能

**收益**:
- 減少約 150 行重複程式碼
- 表格邏輯獨立,易於擴展
- 統一顯示格式

#### 階段 4: 抽取基本資料表單

**工作量**: 2 小時
**優先級**: 🔶 中
**風險**: 低

**任務清單**:
- [ ] 創建 `components/PropertyOwner/BaseInfoForm.vue`
  - Props: `formData`, `urbanRenewalName`, `showTestButton`, `disabled`
  - Events: `update:formData`
- [ ] 在兩個頁面中替換基本資料表單為元件
- [ ] 測試表單輸入、驗證、自動生成編號

**收益**:
- 減少約 150 行重複程式碼
- 基本資料表單完全統一
- 易於添加新欄位

---

### 總工作量估計

| 階段 | 工作量 | 累計工作量 |
|------|--------|-----------|
| 階段 1 | 2-3 小時 | 2-3 小時 |
| 階段 2 | 3-4 小時 | 5-7 小時 |
| 階段 3 | 2-3 小時 | 7-10 小時 |
| 階段 4 | 2 小時 | 9-12 小時 |
| **總計** | **9-12 小時** | |

---

## 預期收益

### 量化收益

| 指標 | 重構前 | 重構後 | 改善 |
|------|--------|--------|------|
| 總程式碼行數 | 2117 行 | ~850 行 | -60% |
| 重複程式碼 | ~1500 行 | ~0 行 | -100% |
| 檔案數量 | 2 個 | 10 個 | +400% |
| 平均檔案大小 | 1058 行 | 85 行 | -92% |

### 質化收益

#### 1. 可維護性 📈
- ✅ 修改一處,兩個頁面同步更新
- ✅ Bug 修復效率提升
- ✅ 新增功能更容易

#### 2. 可測試性 🧪
- ✅ 元件獨立,易於單元測試
- ✅ Composable 邏輯可單獨測試
- ✅ 降低迴歸測試成本

#### 3. 可讀性 📖
- ✅ 每個檔案職責單一
- ✅ 程式碼結構清晰
- ✅ 新人上手更快

#### 4. 可擴展性 🚀
- ✅ 新增第三個頁面(如批次編輯)成本低
- ✅ 元件可在其他模組重用
- ✅ 更容易添加新功能

### 風險評估

| 風險 | 等級 | 緩解措施 |
|------|------|----------|
| 引入新 Bug | 🟡 中 | 每個階段完成後進行完整測試 |
| 時程延誤 | 🟢 低 | 採用漸進式,可隨時中斷 |
| 效能影響 | 🟢 低 | 元件化不影響效能,反而可能提升 |
| 團隊學習成本 | 🟢 低 | Composable 是 Vue 3 標準做法 |

---

## 技術細節

### 元件設計

#### 1. BaseInfoForm.vue

**用途**: 所有權人基本資料表單

**Props**:
```typescript
interface Props {
  formData: PropertyOwnerFormData
  urbanRenewalName?: string
  showTestButton?: boolean
  disabled?: boolean
}
```

**Events**:
```typescript
interface Events {
  'update:formData': (data: PropertyOwnerFormData) => void
}
```

**範例使用**:
```vue
<BaseInfoForm
  v-model:formData="formData"
  :urban-renewal-name="urbanRenewalName"
  :show-test-button="true"
  :disabled="loading"
/>
```

---

#### 2. LandModal.vue

**用途**: 地號新增彈窗

**Props**:
```typescript
interface Props {
  isOpen: boolean
  availablePlots: LandPlot[]
  showTestButton?: boolean
}
```

**Events**:
```typescript
interface Events {
  close: () => void
  submit: (landData: LandFormData) => void
}
```

**範例使用**:
```vue
<LandModal
  :is-open="showLandModal"
  :available-plots="availablePlots"
  :show-test-button="mode === 'edit'"
  @close="showLandModal = false"
  @submit="handleAddLand"
/>
```

---

#### 3. BuildingModal.vue

**用途**: 建號新增彈窗

**Props**:
```typescript
interface Props {
  isOpen: boolean
  counties: County[]
  showTestButton?: boolean
}
```

**Events**:
```typescript
interface Events {
  close: () => void
  submit: (buildingData: BuildingFormData) => void
}
```

**範例使用**:
```vue
<BuildingModal
  :is-open="showBuildingModal"
  :counties="counties"
  :show-test-button="mode === 'edit'"
  @close="showBuildingModal = false"
  @submit="handleAddBuilding"
/>
```

---

#### 4. LandTable.vue

**用途**: 地號列表顯示

**Props**:
```typescript
interface Props {
  lands: Land[]
  showReloadButton?: boolean
  isReloading?: boolean
  formatNumber?: boolean
}
```

**Events**:
```typescript
interface Events {
  remove: (index: number) => void
  reload?: () => void
}
```

**範例使用**:
```vue
<LandTable
  :lands="formData.lands"
  :show-reload-button="mode === 'edit'"
  :is-reloading="reloadingLands"
  :format-number="mode === 'edit'"
  @remove="removeLand"
  @reload="reloadLands"
/>
```

---

#### 5. BuildingTable.vue

**用途**: 建號列表顯示

**Props**:
```typescript
interface Props {
  buildings: Building[]
  showReloadButton?: boolean
  isReloading?: boolean
  formatNumber?: boolean
}
```

**Events**:
```typescript
interface Events {
  remove: (index: number) => void
  reload?: () => void
}
```

**範例使用**:
```vue
<BuildingTable
  :buildings="formData.buildings"
  :show-reload-button="mode === 'edit'"
  :is-reloading="reloadingBuildings"
  :format-number="mode === 'edit'"
  @remove="removeBuilding"
  @reload="reloadBuildings"
/>
```

---

### Composable 設計

#### 1. usePropertyOwnerForm.js

**用途**: 核心表單邏輯

**參數**:
```typescript
interface Options {
  mode: 'create' | 'edit'
  ownerId?: number
}
```

**返回值**:
```typescript
interface ReturnValue {
  // 狀態
  formData: Ref<PropertyOwnerFormData>
  landForm: Ref<LandFormData>
  buildingForm: Ref<BuildingFormData>
  loading: Ref<boolean>

  // 方法
  addLand: (land: Land) => void
  addBuilding: (building: Building) => void
  removeLand: (index: number) => void
  removeBuilding: (index: number) => void
  submit: () => Promise<void>

  // 條件性方法 (僅 edit 模式)
  reloadLands?: () => Promise<void>
  reloadBuildings?: () => Promise<void>
  formatLandNumber?: (land: Land) => string
  formatBuildingNumber?: (building: Building) => string
}
```

**範例使用**:
```vue
<script setup>
import { usePropertyOwnerForm } from '~/composables/usePropertyOwnerForm'

const route = useRoute()
const mode = route.name.includes('create') ? 'create' : 'edit'
const ownerId = route.params.ownerId

const {
  formData,
  landForm,
  buildingForm,
  loading,
  addLand,
  addBuilding,
  removeLand,
  removeBuilding,
  submit,
  reloadLands,
  reloadBuildings
} = usePropertyOwnerForm({ mode, ownerId })
</script>
```

---

#### 2. useLocationCascade.js

**用途**: 縣市/行政區/段小段級聯邏輯

**返回值**:
```typescript
interface ReturnValue {
  counties: Ref<County[]>
  districts: Ref<District[]>
  sections: Ref<Section[]>

  fetchCounties: () => Promise<void>
  fetchDistricts: (countyCode: string) => Promise<void>
  fetchSections: (countyCode: string, districtCode: string) => Promise<void>

  onCountyChange: (countyCode: string) => Promise<void>
  onDistrictChange: (districtCode: string) => Promise<void>
}
```

**範例使用**:
```vue
<script setup>
import { useLocationCascade } from '~/composables/useLocationCascade'

const {
  counties,
  districts,
  sections,
  onCountyChange,
  onDistrictChange
} = useLocationCascade()
</script>
```

---

#### 3. useLandBuilding.js

**用途**: 地號/建號管理邏輯

**返回值**:
```typescript
interface ReturnValue {
  lands: Ref<Land[]>
  buildings: Ref<Building[]>

  addLand: (land: Land) => void
  removeLand: (index: number) => void
  updateLand: (index: number, land: Land) => void

  addBuilding: (building: Building) => void
  removeBuilding: (index: number) => void
  updateBuilding: (index: number, building: Building) => void

  validateLand: (land: Land) => boolean
  validateBuilding: (building: Building) => boolean
}
```

---

## 型別定義

```typescript
// 所有權人表單資料
interface PropertyOwnerFormData {
  owner_name: string
  identity_number: string
  owner_code: string
  phone1: string
  phone2: string
  contact_address: string
  registered_address: string
  exclusion_type: string
  buildings: Building[]
  lands: Land[]
  notes: string
}

// 地號表單資料
interface LandFormData {
  plot_number: string
  total_area: number | string
  ownership_numerator: number | string
  ownership_denominator: number | string
}

// 建號表單資料
interface BuildingFormData {
  county: string
  district: string
  section: string
  building_number_main: string
  building_number_sub: string
  building_area: number | string
  ownership_numerator: number | string
  ownership_denominator: number | string
  building_address: string
}

// 地號資料
interface Land {
  plot_number: string
  plot_number_display?: string
  total_area: number
  ownership_numerator: number
  ownership_denominator: number
}

// 建號資料
interface Building {
  county: string
  district: string
  section: string
  location?: string
  building_number_main: string
  building_number_sub: string
  building_area: number
  ownership_numerator: number
  ownership_denominator: number
  building_address: string
}

// 縣市
interface County {
  id: number
  code: string
  name: string
}

// 行政區
interface District {
  id: number
  code: string
  name: string
  county_id: number
}

// 段小段
interface Section {
  id: number
  code: string
  name: string
  district_id: number
}

// 地號選項
interface LandPlot {
  id: number
  plot_number: string
  plot_number_display: string
  total_area: number
}
```

---

## 實作注意事項

### 1. 測試策略

每個階段完成後,必須進行以下測試:

#### 功能測試清單
- [ ] 基本資料輸入與驗證
- [ ] 地號新增、刪除、顯示
- [ ] 建號新增、刪除、顯示
- [ ] 縣市/行政區/段小段級聯選擇
- [ ] 表單提交(Create: POST, Edit: PUT)
- [ ] 錯誤處理與訊息顯示
- [ ] 測試資料填入功能
- [ ] 重新整理功能(僅 Edit)

#### 迴歸測試
- [ ] Create 頁面所有功能正常
- [ ] Edit 頁面所有功能正常
- [ ] 兩個頁面行為一致性

### 2. 程式碼品質

- ✅ 使用 TypeScript 型別定義
- ✅ 添加 JSDoc 註解
- ✅ 遵循 Vue 3 Composition API 最佳實踐
- ✅ 統一命名規範
- ✅ 錯誤處理要完整

### 3. 效能考量

- ✅ 避免不必要的重新渲染
- ✅ 使用 `computed` 而非 `watch` (當可行時)
- ✅ 大型列表使用虛擬滾動(如有需要)
- ✅ API 呼叫使用防抖(debounce)

### 4. 相容性

- ✅ 確保與現有 API 端點相容
- ✅ 保持資料格式一致
- ✅ 向後相容性(如有必要)

---

## 版本控制策略

### Git 分支策略

```
main
  └── feature/refactor-property-owner-forms
       ├── feature/stage-1-composables
       ├── feature/stage-2-modals
       ├── feature/stage-3-tables
       └── feature/stage-4-base-form
```

### Commit 訊息格式

```
refactor(property-owner): [階段] 簡短描述

詳細說明改動內容和原因

- 改動點 1
- 改動點 2

測試: 測試內容
```

**範例**:
```
refactor(property-owner): [Stage 1] 抽取核心表單邏輯到 composable

將 Create 和 Edit 頁面的共用邏輯抽取到 usePropertyOwnerForm composable

- 移動表單狀態管理
- 移動地號/建號新增/刪除邏輯
- 移動表單提交邏輯

測試: Create 和 Edit 頁面所有功能正常運作
```

---

## 後續優化建議

重構完成後,可以考慮以下優化:

### 1. 效能優化
- [ ] 實作虛擬滾動(如果地號/建號列表很長)
- [ ] 使用 `keep-alive` 快取元件狀態
- [ ] 優化 API 呼叫(批次處理、快取)

### 2. 使用者體驗
- [ ] 添加表單自動儲存(草稿)
- [ ] 改善 Loading 狀態顯示
- [ ] 添加操作確認對話框
- [ ] 支援鍵盤快捷鍵

### 3. 開發體驗
- [ ] 添加 Storybook 文件
- [ ] 編寫單元測試
- [ ] 添加 E2E 測試
- [ ] 自動化測試整合到 CI/CD

### 4. 功能擴展
- [ ] 支援批次匯入地號/建號
- [ ] 支援批次編輯多個所有權人
- [ ] 匯出功能
- [ ] 歷史記錄追蹤

---

## 參考資料

### Vue 3 相關
- [Vue 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)
- [Vue 3 Composables](https://vuejs.org/guide/reusability/composables.html)
- [Vue 3 Component Props](https://vuejs.org/guide/components/props.html)

### 重構模式
- [Refactoring Patterns](https://refactoring.guru/refactoring/techniques)
- [Component Design Patterns](https://www.patterns.dev/posts/vue-patterns/)

### 專案相關
- [Property Owner API 文件](../backend/docs/api/property-owners.md) (待建立)
- [Location API 文件](../backend/docs/api/locations.md) (待建立)

---

## 變更記錄

| 日期 | 版本 | 變更內容 | 作者 |
|------|------|---------|------|
| 2025-11-07 | 1.0.0 | 初始版本,完成分析和計畫 | Claude |

---

## 附錄

### A. 程式碼相似度詳細比較

請參考原始分析報告中的詳細比較表格。

### B. 風險評估矩陣

| 風險 | 機率 | 影響 | 等級 | 緩解措施 |
|------|------|------|------|----------|
| 引入新 Bug | 中 | 高 | 🟡 中 | 階段性測試、程式碼審查 |
| 時程延誤 | 低 | 中 | 🟢 低 | 漸進式重構、可中斷 |
| 效能下降 | 低 | 中 | 🟢 低 | 效能測試、優化 |
| 團隊抗拒 | 低 | 低 | 🟢 低 | 文件說明、知識分享 |

### C. 成功指標

- ✅ 程式碼行數減少 50% 以上
- ✅ 所有現有功能正常運作
- ✅ 無新增 Bug 或立即修復
- ✅ 程式碼審查通過
- ✅ 團隊成員能理解新架構

---

**文件版本**: 1.0.0
**最後更新**: 2025-11-07
**維護者**: 開發團隊
