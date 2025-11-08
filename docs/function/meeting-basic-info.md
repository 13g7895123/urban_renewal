# 會議基本資料頁面功能說明

## 頁面路徑
- **新增會議**: `/tables/meeting/new/basic-info`
- **編輯會議**: `/tables/meeting/[meetingId]/basic-info`

## 頁面檔案
`frontend/pages/tables/meeting/[meetingId]/basic-info.vue`

## 功能概述
此頁面用於建立新會議或編輯現有會議的基本資料，包括會議資訊、成會條件、列席者、會議通知單資訊等。

---

## 主要功能

### 1. 會議基本資訊

#### 頁面佈局
```
┌─────────────────────────────────────────────────────────┐
│ 所屬更新會（單一列，全寬）                                │
├───────────────────┬─────────────────┬──────────────────┤
│ 會議類型           │ 會議日期時間     │                   │
├───────────────────┴─────────────────┴──────────────────┤
│ 會議名稱（單一列，全寬，僅新增模式）                      │
├─────────────────────────────────────────────────────────┤
│ 開會地址（單一列，全寬）                                  │
├───────────────────┬─────────────────┬──────────────────┤
│ 出席人數(R)        │ 納入計算總人數(R)│ 列席總人數        │
│                   │ ☑ 排除所有權人   │                   │
├───────────────────┴─────────────────┴──────────────────┤
│ 成會條件設定...                                          │
└─────────────────────────────────────────────────────────┘

註：(R) 表示 readonly 欄位
```

#### 1.1 所屬更新會（下拉選單）
- **佈局**: 單一列，全寬
- **類型**: 下拉選單 (USelectMenu)
- **資料來源**: API `/api/urban-renewals`
- **權限控制**: 僅顯示該公司有權限的更新會項目
- **新增模式**: 可從下拉選單選擇所屬更新會
- **編輯模式**: 唯讀顯示，不可修改
- **實作細節**:
  - 使用 `useUrbanRenewal` composable 載入資料
  - 選項格式: `{ label: name, value: id, name: name }`
  - 儲存時傳送 `urban_renewal_id` 和 `renewal_group`

#### 1.2 會議類型（下拉選單）
- **佈局**: 與會議日期時間同列（左側）
- **類型**: 下拉選單 (USelectMenu)
- **選項**:
  - 會員大會
  - 理監事會
  - 公聽會
- **預設值**: 會員大會
- **新增模式**: 可從下拉選單選擇
- **編輯模式**: 唯讀顯示，不可修改
- **實作細節**:
  - 選項儲存在 `meetingTypeOptions` ref
  - 選中的值儲存在 `meetingType` ref
  - 儲存時傳送 `meeting_type` 欄位

#### 1.3 會議日期時間（日期時間選擇器）
- **佈局**: 與會議類型同列（右側）
- **類型**: 日期時間選擇器 (`<input type="datetime-local">`)
- **必填**: 是
- **格式**: `YYYY-MM-DDTHH:mm` (ISO 8601 格式)
- **範例**: `2025-12-15T14:00` (2025年12月15日 下午2:00)
- **UI 特性**:
  - 原生瀏覽器日期時間選擇器
  - 支援日曆選擇日期
  - 支援時鐘選擇時間
  - 自動驗證日期時間格式
- **樣式**:
  ```css
  focus:ring-green-500 focus:border-green-500
  ```

#### 1.4 會議名稱
- **佈局**: 單一列，全寬
- **類型**: 文字輸入框 (UInput)
- **僅新增模式顯示**: 編輯模式不顯示此欄位
- **必填**: 是
- **範例**: 114年度第一屆第3次會員大會

#### 1.5 開會地址
- **佈局**: 單一列，全寬
- **類型**: 文字輸入框 (UInput)
- **必填**: 是
- **自動帶入**: 選擇更新會後，自動填入該更新會的設立地址
- **資料來源**: `selectedUrbanRenewal.address`
- **範例**: 台北市南港區玉成街1號
- **說明**: 可手動修改自動帶入的地址

#### 1.6 出席人數
- **佈局**: 與納入計算總人數、列席總人數同列（左側）
- **類型**: 數字輸入框 (UInput)
- **模式**: 唯讀 (readonly)
- **自動帶入**: 選擇更新會後，自動填入該更新會的所有權人數
- **資料來源**: `selectedUrbanRenewal.member_count`
- **樣式**: 灰色背景 (`bg-gray-50`)
- **說明**: 由系統自動計算，無法手動輸入

#### 1.7 納入計算總人數（含排除選項）
- **佈局**: 與出席人數、列席總人數同列（中間）
- **類型**: 數字輸入框 (UInput) + 勾選框 (checkbox)
- **模式**: 唯讀 (readonly)
- **自動帶入**: 選擇更新會後，自動填入該更新會的所有權人數
- **資料來源**: `selectedUrbanRenewal.member_count`
- **樣式**: 灰色背景 (`bg-gray-50`)
- **計算邏輯**:
  - 未勾選「排除所有權人不列計」: `totalCountedAttendees = baseAttendees`
  - 勾選「排除所有權人不列計」: `totalCountedAttendees = baseAttendees - 1`
  - 使用 Vue computed property 自動計算
- **排除選項**:
  - **勾選框文字**: 排除所有權人不列計
  - **狀態**: `excludeOwnerFromCount` (ref)
  - **樣式**: 綠色勾選框 (`text-green-600 focus:ring-green-500`)
  - **功能**: 勾選時，納入計算總人數自動減 1

#### 1.8 列席總人數
- **佈局**: 與出席人數、納入計算總人數同列（右側）
- **類型**: 數字輸入框 (UInput, type="number")
- **必填**: 否
- **預設值**: 0
- **說明**: 可手動輸入列席人數

---

### 2. 成會條件設定

#### 2.1 土地面積條件
- **成會土地面積比例分子**: 數字輸入
- **成會土地面積比例分母**: 數字輸入
- **成會土地面積(平方公尺)**: 數字輸入

#### 2.2 建物面積條件
- **成會建物面積比例分子**: 數字輸入
- **成會建物面積比例分母**: 數字輸入
- **成會建物面積(平方公尺)**: 數字輸入

#### 2.3 人數條件
- **成會人數比例分子**: 數字輸入
- **成會人數比例分母**: 數字輸入
- **成會人數**: 數字輸入

---

### 3. 列席者管理

#### 3.1 列席者表格
- **功能**: 管理會議列席者資訊
- **欄位**:
  - 姓名 (name)
  - 職稱 (title)
  - 聯絡電話 (phone)
- **操作**:
  - **新增列席者**: 點擊「新增列席者」按鈕
  - **刪除列席者**: 點擊該列的「刪除」按鈕
- **顯示**: 最大高度 48 (max-h-48)，超過時可捲動

---

### 4. 會議通知單資訊

#### 4.1 發文字號
包含四個欄位：
- **發文字號**: 文字輸入
- **字第**: 文字輸入
- **oooooo**: 文字輸入（中間編號）
- **號**: 文字輸入

#### 4.2 聯絡資訊
- **理事長姓名**: 文字輸入
- **聯絡人姓名**: 文字輸入
- **聯絡人電話**: 文字輸入
- **附件**: 文字輸入

#### 4.3 發文說明
- **類型**: 動態列表
- **格式**: 一、二、三...（中文數字編號）
- **操作**:
  - **新增說明**: 點擊「新增說明」按鈕
  - **刪除說明**: 點擊該項的「刪除」按鈕
- **實作細節**:
  - 使用 `getChineseNumber()` 函數轉換數字為中文
  - 支援 1-10 的中文數字轉換

---

## 頁面操作按鈕

### 主要操作

#### 1. 填入測試資料（僅新增模式）
- **顯示條件**: `!selectedMeeting`
- **功能**: 自動填入預設測試資料
- **圖示**: heroicons:beaker
- **包含資料**:
  - 選擇第一個可用的更新會
  - 會議名稱：114年度第一屆第3次會員大會
  - 會議日期時間：2025-12-15T14:00
  - 會議地點：台北市南港區玉成街1號
  - 成會條件數據（土地、建物、人數比例）
  - 列席者範例資料（2筆）
  - 會議通知單資訊（發文字號、聯絡人、說明事項）

#### 2. 匯出簽到冊（僅編輯模式）
- **顯示條件**: `selectedMeeting`
- **功能**: 匯出會議簽到冊文件
- **圖示**: heroicons:document-arrow-down
- **狀態**: TODO - 待實作

#### 3. 匯出會議通知（僅編輯模式）
- **顯示條件**: `selectedMeeting`
- **功能**: 匯出會議通知單文件
- **圖示**: heroicons:document-arrow-down
- **狀態**: TODO - 待實作

#### 4. 回上一頁
- **功能**: 返回會議列表頁面
- **路徑**: `/tables/meeting`
- **樣式**: outline 變體

#### 5. 儲存
- **功能**: 儲存會議基本資料
- **圖示**: heroicons:check
- **Loading 狀態**: 儲存中顯示載入動畫
- **成功後**: 1.5 秒後自動跳轉回會議列表

---

## API 整合

### 使用的 Composables
```javascript
const { getMeeting, createMeeting, updateMeeting } = useMeetings()
const { getUrbanRenewals } = useUrbanRenewal()
const { showSuccess, showError } = useSweetAlert()
```

### API 端點

#### 1. 載入更新會列表
- **端點**: `GET /api/urban-renewals`
- **時機**: 頁面載入時 (onMounted)
- **用途**: 填充「所屬更新會」下拉選單

#### 2. 載入會議資料
- **端點**: `GET /api/meetings/:id`
- **時機**: 編輯模式時 (meetingId !== 'new')
- **用途**: 載入現有會議資料

#### 3. 建立會議
- **端點**: `POST /api/meetings`
- **時機**: 新增模式儲存時
- **傳送資料**:
  ```javascript
  {
    urban_renewal_id: number,
    renewal_group: string,
    meeting_name: string,
    meeting_type: string,
    meeting_datetime: string,
    meeting_location: string,
    attendees: number,
    total_counted_attendees: number,
    exclude_owner_from_count: boolean,
    total_observers: number,
    land_area_ratio_numerator: number,
    land_area_ratio_denominator: number,
    total_land_area: number,
    building_area_ratio_numerator: number,
    building_area_ratio_denominator: number,
    total_building_area: number,
    people_ratio_numerator: number,
    people_ratio_denominator: number,
    total_people_count: number,
    observers: array,
    notice_doc_number: string,
    notice_word_number: string,
    notice_mid_number: string,
    notice_end_number: string,
    chairman_name: string,
    contact_name: string,
    contact_phone: string,
    attachments: string,
    descriptions: array
  }
  ```

#### 4. 更新會議
- **端點**: `PUT /api/meetings/:id`
- **時機**: 編輯模式儲存時
- **傳送資料**: 同建立會議（不含 urban_renewal_id, renewal_group, meeting_name, meeting_type）
- **包含新增欄位**:
  - `attendees`: 出席人數
  - `total_counted_attendees`: 納入計算總人數
  - `exclude_owner_from_count`: 是否排除所有權人不列計

---

## 權限控制

### 頁面權限
```javascript
definePageMeta({
  middleware: ['auth', 'company-manager']
})
```

- **auth**: 需要登入
- **company-manager**: 需要公司管理者權限

### 資料權限
- 所屬更新會下拉選單僅顯示該公司有權限的更新會項目
- 由後端 API 根據使用者身分過濾資料

---

## 資料驗證

### 必填欄位（新增模式）
- 所屬更新會
- 會議名稱
- 會議類型
- 會議日期時間
- 開會地點

### 數字欄位驗證
- 成會條件相關欄位自動轉換為數字
- 使用 `parseInt()` 或 `parseFloat()` 處理
- 無效值自動設為 0

---

## 載入狀態

### Loading 指示器
```html
<div v-if="isLoading" class="flex justify-center items-center py-12">
  <Icon name="heroicons:arrow-path" class="w-12 h-12 text-green-500 animate-spin" />
  <p class="text-gray-600">載入中...</p>
</div>
```

### Loading 狀態控制
- 載入會議資料時顯示
- 儲存資料時顯示
- 按鈕 disabled 狀態同步

---

## 錯誤處理

### SweetAlert 提示
- **成功**: `showSuccess(title, message)`
- **失敗**: `showError(title, message)`

### 錯誤情境
1. 載入更新會列表失敗 - 靜默處理，下拉選單為空
2. 載入會議資料失敗 - 顯示錯誤訊息，嘗試使用 mock 資料
3. 儲存會議失敗 - 顯示錯誤訊息，不跳轉頁面

---

## 更新記錄

### 2025-11-08

#### 版本 1.3 - 自動帶入與排除選項
1. **開會地點改為開會地址**
   - 名稱從「開會地點」改為「開會地址」
   - 改為單一列顯示（全寬）
   - 選擇更新會後自動帶入該更新會的設立地址

2. **出席人數與納入計算總人數調整**
   - 佈局：改為三欄並列（出席人數、納入計算總人數、列席總人數）
   - 出席人數：改為 readonly，選擇更新會後自動帶入所有權人數
   - 納入計算總人數：改為 readonly，使用 computed property 自動計算

3. **新增「排除所有權人不列計」功能**
   - 在納入計算總人數欄位下方加入勾選框
   - 勾選時，納入計算總人數自動減 1
   - 使用 Vue computed property 實現響應式計算
   - 實作細節：
     ```javascript
     const totalCountedAttendees = computed(() => {
       if (excludeOwnerFromCount.value) {
         return Math.max(0, baseAttendees.value - 1)
       }
       return baseAttendees.value
     })
     ```

4. **選擇更新會自動帶入資料**
   - 使用 watch 監聽 `selectedUrbanRenewal` 變化
   - 自動帶入：
     - 開會地址 (`address`)
     - 出席人數 (`member_count`)
     - 納入計算總人數基數 (`member_count`)
   - 僅在新增模式（非編輯模式）時自動帶入

#### 版本 1.2 - 佈局優化與日期時間選擇器
1. **頁面佈局調整**
   - **所屬更新會**: 改為單一列顯示（全寬）
   - **會議類型 + 會議日期時間**: 合併為同一列（兩欄）
   - **會議名稱**: 改為單一列顯示（全寬，僅新增模式）
   - 目的: 提升表單視覺層次和使用體驗

2. **會議日期時間改為日期時間選擇器**
   - 從文字輸入框改為 `<input type="datetime-local">`
   - 提供原生瀏覽器日期時間選擇器
   - 自動驗證日期時間格式
   - 格式: `YYYY-MM-DDTHH:mm` (ISO 8601)
   - 樣式: 綠色焦點環 (`focus:ring-green-500`)

#### 版本 1.1 - 下拉選單整合
1. **所屬更新會改為下拉選單**
   - 從文字輸入框改為 USelectMenu
   - 整合 `/api/urban-renewals` API
   - 新增 `urbanRenewalOptions` 和 `selectedUrbanRenewal` 狀態管理
   - 編輯模式保持唯讀顯示

2. **會議類型改為下拉選單**
   - 從唯讀顯示改為可選擇的下拉選單（新增模式）
   - 選項：會員大會、理監事會、公聽會
   - 預設值：會員大會
   - 編輯模式保持唯讀顯示

---

## 技術細節

### 關鍵 Refs
```javascript
// 更新會選項
const urbanRenewalOptions = ref([])
const selectedUrbanRenewal = ref(null)

// 會議類型選項
const meetingTypeOptions = ref(['會員大會', '理監事會', '公聽會'])
const meetingType = ref('會員大會')

// 出席人數相關
const attendees = ref(0)
const baseAttendees = ref(0) // 原始所有權人數
const excludeOwnerFromCount = ref(false)

// Computed: 納入計算總人數（根據勾選狀態）
const totalCountedAttendees = computed(() => {
  if (excludeOwnerFromCount.value) {
    return Math.max(0, baseAttendees.value - 1)
  }
  return baseAttendees.value
})

// 表單欄位
const renewalGroup = ref('')
const meetingName = ref('')
const meetingDateTime = ref('')
const meetingLocation = ref('')
const totalObservers = ref(0)
// ... 其他欄位
```

### Watch 監聽
```javascript
// 監聽選擇更新會的變化
watch(selectedUrbanRenewal, (newValue) => {
  if (newValue && !selectedMeeting.value) {
    // 自動帶入開會地址
    meetingLocation.value = newValue.address || ''

    // 自動帶入所有權人數
    const memberCount = newValue.member_count || 0
    attendees.value = memberCount
    baseAttendees.value = memberCount
  }
})
```

### 輔助函數
```javascript
// 重置表單
resetFormFields()

// 列席者管理
addObserver()
removeObserver(index)

// 發文說明管理
addDescription()
removeDescription(index)

// 中文數字轉換
getChineseNumber(num)

// 測試資料填充
fillMeetingTestData()
```

---

## 待開發功能

1. ✅ 所屬更新會下拉選單整合
2. ✅ 會議類型下拉選單
3. ✅ 會議日期時間選擇器
4. ✅ 頁面佈局優化
5. ✅ 選擇更新會後自動帶入資料
6. ✅ 排除所有權人不列計功能
7. ⏳ 匯出簽到冊功能
8. ⏳ 匯出會議通知功能
9. ⏳ 表單驗證提示
10. ⏳ 自動儲存草稿

---

## 相關文件
- [會議管理 API 文件](../api/meetings.md)
- [更新會管理 API 文件](../api/urban-renewals.md)
- [權限管理文件](../auth/permissions.md)
