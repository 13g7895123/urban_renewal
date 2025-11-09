# 議題管理功能說明

## 功能概述

議題管理頁面 (`/tables/issue`) 提供了一個完整的會議議題管理介面,讓使用者能夠選擇特定的更新會和會議日期,並管理該會議的所有議題。

## 頁面路徑

- 前端路徑: `/tables/issue`
- 檔案位置: `frontend/pages/tables/issue.vue`

## 主要功能

### 1. 更新會選擇

#### 功能描述
- 從系統中所有已建立的更新會中選擇一個
- 下拉選單顯示更新會的詳細資訊,包括:
  - 更新會名稱
  - 理事長姓名
  - 所有權人數

#### 資料來源
- API 端點: `GET /urban-renewals`
- 使用 composable: `useUrbanRenewal()`
- 回傳格式:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "更新會名稱",
      "area": 1000,
      "member_count": 50,
      "chairman_name": "理事長姓名",
      "chairman_phone": "0912345678"
    }
  ]
}
```

#### 互動行為
- 選擇更新會後,會自動觸發會議列表的載入
- 更新會選擇後不顯示額外資訊卡片,保持介面簡潔

### 2. 會議日期選擇

#### 功能描述
- **重要**: 此下拉選單的資料來源為所選更新會的會議項目
- 必須先選擇更新會後,才能選擇會議日期
- 會議選單會根據所選的更新會動態更新
- 下拉選單顯示會議資訊,包括:
  - 會議日期
  - 會議名稱

#### 資料來源
- API 端點: `GET /meetings?urban_renewal_id={id}`
- 使用 composable: `useMeetings()`
- **資料篩選**: 只顯示屬於所選更新會的會議
- 回傳格式:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "urban_renewal_id": 1,
      "meeting_name": "第一次會議",
      "meeting_date": "2024-12-01",
      "location": "會議地點",
      "status": "pending"
    }
  ]
}
```

#### 互動行為
- 未選擇更新會時,此下拉選單呈現禁用狀態
- 選擇更新會後,自動載入該更新會的所有會議
- 改選其他更新會時,會清空目前選擇並重新載入新的會議列表
- 選擇會議後,會在下方顯示會議資訊卡片

#### 選擇後顯示資訊
當選擇會議後,會在下方顯示資訊卡片,包含:
- 會議名稱
- 會議日期 (格式化為中文日期,包含星期)
- 會議地點
- 會議狀態

#### 會議狀態說明
- `pending`: 待開始
- `in_progress`: 進行中
- `completed`: 已結束
- `cancelled`: 已取消

### 3. 議題列表

#### 功能描述
- 顯示選定會議的所有議題
- 提供議題的新增、編輯、刪除功能

#### 資料來源
- API 端點: `GET /meetings/{meetingId}/topics`
- 回傳格式:
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "meeting_id": 1,
      "title": "議題標題",
      "description": "議題描述"
    }
  ]
}
```

#### 議題操作

**新增議題**
- 點擊「新增議題」按鈕
- 導航至: `/tables/meeting/{meetingId}/voting-topics/create`

**查看議題**
- 點擊議題卡片
- 導航至: `/tables/meeting/{meetingId}/voting-topics/{topicId}`

**編輯議題**
- 點擊議題的「編輯」按鈕
- 導航至: `/tables/meeting/{meetingId}/voting-topics/{topicId}/edit`

**刪除議題**
- 點擊議題的「刪除」按鈕
- 顯示確認對話框
- 確認後執行刪除: `DELETE /meetings/{meetingId}/topics/{topicId}`

## 頁面佈局

### 結構說明

頁面採用垂直排列的單欄式佈局,所有元素由上至下依序排列:

1. **第一區域**: 更新會選擇
   - 下拉選單 (USelectMenu)
   - 選擇後不顯示額外資訊卡片

2. **第二區域**: 會議日期選擇
   - 下拉選單 (USelectMenu)
   - 資料來源為所選更新會的會議項目
   - 選擇後顯示會議資訊卡片（包含會議名稱、日期、地點、狀態）

3. **第三區域**: 議題列表
   - 當未選擇會議時顯示提示訊息
   - 選擇會議後顯示該會議的所有議題
   - 提供新增、編輯、刪除議題功能

### 佈局特色

- **簡潔明瞭**: 單欄垂直排列,一列一個項目
- **邏輯順序**: 由上至下依照使用流程排列
- **響應式設計**: 所有螢幕尺寸都使用相同的垂直佈局
- **資訊精簡**: 僅在會議選擇後顯示必要的會議資訊

## 使用流程

1. 進入頁面後自動載入所有更新會
2. 使用者從下拉選單選擇一個更新會
3. 系統自動載入該更新會的所有會議
4. 使用者從會議列表中選擇一個會議日期
5. 系統自動載入該會議的所有議題
6. 使用者可以進行議題的增刪查改操作

## 權限控制

- 需要登入才能存取 (middleware: `auth`)
- 所有已登入使用者都可以使用此功能

## 相關檔案

### 前端檔案
- `frontend/pages/tables/issue.vue` - 主頁面元件
- `frontend/composables/useUrbanRenewal.js` - 更新會 API composable
- `frontend/composables/useMeetings.js` - 會議 API composable
- `frontend/composables/useApi.js` - 通用 API composable
- `frontend/composables/useAlert.js` - 提示訊息 composable

### 後端 API 端點
- `GET /urban-renewals` - 取得所有更新會
- `GET /meetings?urban_renewal_id={id}` - 取得特定更新會的會議列表
- `GET /meetings/{meetingId}/topics` - 取得特定會議的議題列表
- `DELETE /meetings/{meetingId}/topics/{topicId}` - 刪除議題

## 樣式說明

### 下拉選單背景色問題

由於專案使用了 `force-light.css` 來強制淺色模式,部分 dark mode 相關的樣式會被設為透明。為了確保下拉選單有白色背景,使用了以下方式:

```vue
<div style="background-color: white !important;">
  <USelectMenu
    :ui="{
      base: 'relative block w-full disabled:cursor-not-allowed disabled:opacity-75 focus:outline-none border-0',
      background: 'bg-white dark:bg-white',
      color: 'text-gray-900 dark:text-gray-900'
    }"
    :ui-menu="{ background: 'bg-white' }"
  />
</div>
```

### 響應式設計

- 使用 Tailwind CSS 垂直佈局系統
- 所有螢幕尺寸採用相同的單欄垂直排列
- 每個區域獨立成一列,由上至下排列
- 所有卡片和元件都支援行動裝置顯示
- 使用 `space-y` 類別提供適當的垂直間距

## 注意事項

1. **資料載入順序**: 必須先選擇更新會,才能選擇會議;必須先選擇會議,才能查看議題
2. **會議資料來源**: 會議日期下拉選單的資料來源為所選更新會的會議項目,不同的更新會有不同的會議列表
3. **自動清除**: 當改選更新會時,會自動清除已選擇的會議和議題,並重新載入新的會議列表
4. **資訊顯示**: 選擇更新會後不顯示額外資訊卡片,僅在選擇會議後才顯示會議資訊卡片
5. **錯誤處理**: 所有 API 呼叫都有錯誤處理,失敗時會顯示友善的錯誤訊息
6. **載入狀態**: 各個資料載入階段都有對應的 loading 狀態顯示

## 未來擴充建議

1. 增加搜尋和篩選功能
2. 支援批次操作議題
3. 增加議題排序功能
4. 支援議題匯出功能
5. 增加議題範本功能
