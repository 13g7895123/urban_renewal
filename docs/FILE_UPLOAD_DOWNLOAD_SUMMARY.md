# 都市更新管理系統 - 檔案上傳與下載功能彙整

## 📋 功能總覽

本文件彙整系統中所有檔案上傳（匯入）與下載（匯出）功能，包含檔案命名規則、所在頁面及 API 端點。

---

## 1. 所有權人資料管理

### 1.1 匯入所有權人資料

**功能說明**：批次匯入所有權人資料

**所在頁面**：
- 路徑：`/tables/urban-renewal/[id]/property-owners`
- 檔案：`frontend/pages/tables/urban-renewal/[id]/property-owners/index.vue`

**API 端點**：
- `POST /api/urban-renewals/{id}/property-owners/import`
- Controller: `PropertyOwnerController::import()`

**檔案格式**：Excel (.xlsx, .xls)

**檔案命名規則**：
- 無固定命名要求（使用者自行上傳）

**權限需求**：需要該更新會的存取權限

---

### 1.2 匯出所有權人資料

**功能說明**：匯出所有權人清單為 Excel 檔案

**所在頁面**：
- 路徑：`/tables/urban-renewal/[id]/property-owners`
- 檔案：`frontend/pages/tables/urban-renewal/[id]/property-owners/index.vue`

**API 端點**：
- `GET /api/urban-renewals/{id}/property-owners/export`
- Controller: `PropertyOwnerController::export()`

**檔案格式**：Excel (.xlsx)

**檔案命名規則**：
```
所有權人_{更新會名稱}_{YYYYMMDD}.xlsx
範例：所有權人_信義區更新會_20260113.xlsx
```

**權限需求**：需要該更新會的存取權限

---

### 1.3 下載所有權人匯入範本

**功能說明**：下載所有權人資料匯入範本

**所在頁面**：
- 路徑：`/tables/urban-renewal/[id]/property-owners`（點擊「匯入」按鈕後的彈窗）
- 檔案：`frontend/pages/tables/urban-renewal/[id]/property-owners/index.vue`

**API 端點**：
- `GET /api/property-owners/template`
- Controller: `PropertyOwnerController::downloadTemplate()`

**檔案格式**：Excel (.xlsx)

**檔案命名規則**：
```
所有權人匯入範本_{YYYYMMDD}.xlsx
範例：所有權人匯入範本_20260113.xlsx
```

**範本內容**：
- 標題列：所有權人編號、所有權人名稱、身分證字號、電話1、電話2、聯絡地址、戶籍地址、排除類型、備註
- 包含一筆範例資料（灰色背景，不會被匯入）

**權限需求**：需登入

---

## 2. 會議文件管理

### 2.1 上傳會議文件

**功能說明**：上傳會議相關文件（議程、簽到表、報告等）

**所在頁面**：
- 尚未實作完整的文件管理頁面
- 預計路徑：`/tables/meeting/[meetingId]/documents`

**API 端點**：
- `POST /api/documents/upload`
- Controller: `DocumentController::upload()`

**檔案格式**：多種格式（PDF, Word, Excel, 圖片等）

**儲存路徑**：`writable/uploads/documents/`

**權限需求**：
- 角色：admin, chairman, member
- 需要該會議所屬更新會的存取權限

---

### 2.2 下載會議文件

**功能說明**：下載已上傳的會議文件

**所在頁面**：
- 尚未實作完整的文件管理頁面
- 預計路徑：`/tables/meeting/[meetingId]/documents`

**API 端點**：
- `GET /api/documents/{id}/download`
- Controller: `DocumentController::download()`

**檔案命名規則**：
- 使用原始檔案名稱

**權限需求**：需要該會議所屬更新會的存取權限

---

## 3. 會議通知與簽到冊

### 3.1 匯出會議通知

**功能說明**：匯出會議通知文件（Word 格式）

**所在頁面**：
- 路徑：`/tables/meeting/[meetingId]/basic-info`
- 檔案：`frontend/pages/tables/meeting/[meetingId]/basic-info.vue`

**API 端點**：
- `GET /api/meetings/{id}/export-notice`
- Controller: `MeetingController::exportNotice()`

**檔案格式**：Word (.docx)

**檔案命名規則**：
```
會議通知_{會議名稱}_{YYYYMMDD}.docx
（由 WordExportService 產生）
```

**權限需求**：需要該會議所屬更新會的存取權限

---

### 3.2 匯出簽到冊

**功能說明**：匯出會議簽到冊（Word 格式），支援匿名模式

**所在頁面**：
- 路徑：`/tables/meeting/[meetingId]/basic-info`
- 檔案：`frontend/pages/tables/meeting/[meetingId]/basic-info.vue`

**API 端點**：
- `GET /api/meetings/{id}/export-signature-book?anonymous={true|false}`
- Controller: `MeetingController::exportSignatureBook()`

**檔案格式**：Word (.docx)

**檔案命名規則**：
```
簽到冊_{會議名稱}_{YYYYMMDD}.docx
簽到冊_匿名_{會議名稱}_{YYYYMMDD}.docx （匿名模式）
```

**匯出選項**：
1. 一般簽到冊（顯示所有權人姓名）
2. 匿名簽到冊（隱藏所有權人姓名）

**權限需求**：需要該會議所屬更新會的存取權限

---

## 4. 會議報到記錄

### 4.1 匯出報到結果

**功能說明**：匯出會議報到統計資料

**所在頁面**：
- 路徑：`/tables/meeting/[meetingId]/member-checkin`
- 檔案：`frontend/pages/tables/meeting/[meetingId]/member-checkin.vue`

**API 端點**：
- `POST /api/meetings/{id}/attendances/export`
- Controller: `MeetingAttendanceController::export()`

**檔案格式**：
- Excel (.xlsx) - 預設
- PDF (.pdf) - 可選
- JSON - 可選

**檔案命名規則**：
```
報到結果_{會議名稱}_{YYYYMMDD}.xlsx
報到結果_{會議名稱}_{YYYYMMDD}.pdf
```

**匯出內容**：
- 會議基本資訊
- 報到統計（總人數、實到人數、未到人數）
- 詳細報到名單（編號、姓名、報到時間、報到狀態）

**權限需求**：需要該會議所屬更新會的存取權限

---

## 5. 投票記錄

### 5.1 匯出投票結果

**功能說明**：匯出議題投票結果及統計資料

**所在頁面**：
- 路徑：`/tables/meeting/[meetingId]/voting-topics`
- 檔案：`frontend/pages/tables/meeting/[meetingId]/voting-topics/index.vue`

**API 端點**：
- `GET /api/voting/{topicId}/export?format={xlsx|csv}`
- Controller: `VotingController::export()`

**檔案格式**：
- Excel (.xlsx) - 預設
- CSV (.csv) - 可選

**檔案命名規則**：
```
投票結果_{議題名稱}_{YYYYMMDD}.xlsx
voting_records_{topicId}.csv （CSV 格式）
```

**匯出內容**：
- 議題基本資訊
- 投票統計（同意、不同意、棄權人數及面積）
- 詳細投票記錄（所有權人姓名、持有面積、投票選擇）
- 投票通過狀態

**權限需求**：需要該會議所屬更新會的存取權限

---

### 5.2 匯出投票單（預留功能）

**功能說明**：匯出議題投票單

**所在頁面**：
- 路徑：`/tables/meeting/[meetingId]/voting-topics/[topicId]/basic-info`
- 檔案：`frontend/pages/tables/meeting/[meetingId]/voting-topics/[topicId]/basic-info.vue`

**實作狀態**：🚧 尚未實作（TODO）

**預計功能**：
- 產生可供列印的投票單
- 包含議題內容、投票選項、簽名欄位等

---

## 📊 檔案命名規則統整表

| 功能 | 命名規則 | 範例 |
|------|----------|------|
| 匯出所有權人 | `所有權人_{更新會名稱}_{YYYYMMDD}.xlsx` | `所有權人_信義區更新會_20260113.xlsx` |
| 匯入範本 | `所有權人匯入範本_{YYYYMMDD}.xlsx` | `所有權人匯入範本_20260113.xlsx` |
| 會議通知 | `會議通知_{會議名稱}_{YYYYMMDD}.docx` | `會議通知_第一次會員大會_20260113.docx` |
| 簽到冊 | `簽到冊_{會議名稱}_{YYYYMMDD}.docx` | `簽到冊_第一次會員大會_20260113.docx` |
| 簽到冊（匿名） | `簽到冊_匿名_{會議名稱}_{YYYYMMDD}.docx` | `簽到冊_匿名_第一次會員大會_20260113.docx` |
| 報到結果 | `報到結果_{會議名稱}_{YYYYMMDD}.xlsx` | `報到結果_第一次會員大會_20260113.xlsx` |
| 投票結果 | `投票結果_{議題名稱}_{YYYYMMDD}.xlsx` | `投票結果_章程修訂_20260113.xlsx` |
| 投票記錄（CSV） | `voting_records_{topicId}.csv` | `voting_records_123.csv` |

---

## 🔒 權限控制總覽

### 權限層級

1. **需登入**：所有匯入匯出功能皆需登入
2. **更新會存取權限**：
   - Admin 角色：可存取所有更新會
   - 一般使用者：只能存取所屬企業管理的更新會

### 角色限制

- **文件上傳**：限 admin, chairman, member 角色
- **其他功能**：所有已登入且有權限的使用者

---

## 🛠️ 技術細節

### 後端實作

#### Excel 處理
- 使用套件：`phpoffice/phpspreadsheet`
- 服務類別：`App\Services\ExcelExportService`

#### Word 文件處理
- 使用套件：`phpoffice/phpword`（推測）
- 服務類別：`App\Services\WordExportService`

#### 儲存路徑
- 上傳文件：`writable/uploads/documents/`
- 暫存匯出檔案：`writable/uploads/temp/`

### 前端實作

#### 下載處理方式
```javascript
// 使用 fetch API 下載檔案
const response = await fetch(url, {
  headers: { 'Authorization': `Bearer ${token}` }
})
const blob = await response.blob()
const a = document.createElement('a')
a.href = URL.createObjectURL(blob)
a.download = filename
a.click()
```

#### 上傳處理方式
```javascript
// 使用 FormData 上傳檔案
const formData = new FormData()
formData.append('file', file)
await post('/api/endpoint', formData)
```

---

## 📝 備註

1. **日期格式**：所有檔名中的日期格式統一為 `YYYYMMDD`（例如：20260113）
2. **編碼**：CSV 檔案使用 UTF-8 with BOM 編碼，確保 Excel 正確顯示中文
3. **檔案大小限制**：建議檢查後端 `php.ini` 設定中的 `upload_max_filesize` 和 `post_max_size`
4. **暫存檔案清理**：建議定期清理暫存匯出檔案
5. **錯誤處理**：所有匯入功能皆有驗證機制，並回傳詳細的錯誤訊息

---

## 🚧 待實作功能

1. **會議文件管理頁面**：完整的文件上傳/下載/刪除介面
2. **投票單匯出**：產生可列印的投票單
3. **批次報到匯入**：透過 Excel 批次匯入報到記錄
4. **多語系檔名支援**：支援英文檔名選項

---

**文件版本**：1.0  
**最後更新**：2026-01-13  
**維護者**：開發團隊
