# 會員簽到結果匯出功能實作說明

## 任務摘要
將 `/tables/meeting/[meetingId]/member-checkin` 頁面的匯出簽到結果功能，從前端 CSV 匯出改為透過後端輸出 Excel（.xlsx）格式，並確保不會有亂碼問題。

## 實作內容

### 1. 後端實作 (Backend)

#### 1.1 更新 MeetingAttendanceController.php
**檔案位置**: `/backend/app/Controllers/Api/MeetingAttendanceController.php`

**變更內容**:
- 新增 PhpSpreadsheet 相關的 use 語句
- 完整實作 `generateExcelReport()` 方法
- 修改 `export()` 方法，直接回傳 Excel 檔案供下載

**主要功能**:
1. 使用 PhpSpreadsheet 生成 Excel 檔案
2. 包含會議資訊（會議名稱、更新會、會議時間、匯出時間）
3. 包含出席統計（親自出席、委託出席、未出席、總人數）
4. 包含簽到明細表格（編號、所有權人姓名、出席狀態、委託代理人、報到時間）
5. 自動調整欄寬，設定邊框和標題樣式
6. 檔案名稱格式: `attendance_{meetingId}_{timestamp}.xlsx`

**防止亂碼措施**:
- PhpSpreadsheet 原生支援 UTF-8 編碼
- Excel 2007+ 的 .xlsx 格式本身就支援 Unicode
- 設定正確的 MIME type: `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`

#### 1.2 更新 Routes.php
**檔案位置**: `/backend/app/Config/Routes.php`

**新增路由**:
```php
$routes->post('(:num)/attendances/export', 'MeetingAttendanceController::export/$1');
```

完整的會議相關簽到路由:
- `GET /api/meetings/{id}/attendances` - 取得出席記錄
- `POST /api/meetings/{meetingId}/attendances/{ownerId}` - 會員報到
- `PUT /api/meetings/{meetingId}/attendances/{ownerId}` - 更新出席狀態
- `GET /api/meetings/{id}/attendances/statistics` - 取得出席統計
- `POST /api/meetings/{id}/attendances/export` - **匯出簽到結果** (新增)
- `POST /api/meetings/{id}/attendances/batch` - 批次報到

### 2. 前端實作 (Frontend)

#### 2.1 更新 member-checkin.vue
**檔案位置**: `/frontend/pages/tables/meeting/[meetingId]/member-checkin.vue`

**變更內容**:
- 移除原本的前端 CSV 生成邏輯
- 改為呼叫後端 API
- 使用 `fetch` API 呼叫 `POST /api/meetings/{meetingId}/attendances/export`
- 接收 Blob 格式的回應
- 使用 `window.URL.createObjectURL` 建立下載連結
- 自動觸發下載並清理資源

**優點**:
1. 資料處理在後端完成，前端只負責觸發下載
2. 支援更複雜的格式和樣式
3. 可以處理大量資料而不影響瀏覽器效能
4. Excel 格式比 CSV 更專業，支援多種樣式和格式

## API 使用方式

### 請求
```http
POST /api/meetings/{meetingId}/attendances/export
Content-Type: application/json

{
  "format": "excel"
}
```

### 回應
- Content-Type: `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`
- Content-Disposition: `attachment; filename="attendance_{meetingId}_{timestamp}.xlsx"`
- Body: Excel 檔案的二進位內容

## 測試建議

1. **正常情況測試**:
   - 開啟會員簽到頁面
   - 點擊「匯出簽到結果」按鈕
   - 確認檔案自動下載
   - 開啟 Excel 檔案檢查內容完整性

2. **中文編碼測試**:
   - 檢查會議名稱、所有權人姓名等中文是否正常顯示
   - 確認沒有亂碼

3. **資料完整性測試**:
   - 確認所有簽到記錄都有匯出
   - 確認統計數字正確
   - 確認時間格式正確

4. **邊界情況測試**:
   - 測試沒有任何簽到記錄的會議
   - 測試大量簽到記錄的會議（100+ 筆）

## 依賴套件

後端已安裝的套件:
- `phpoffice/phpspreadsheet: ^5.1` (已在 composer.json 中)

不需要額外安裝套件。

## 檔案儲存位置

匯出的 Excel 檔案暫存於:
- 路徑: `{backend}/writable/exports/`
- 檔案會在回應給客戶端後保留（可考慮建立定期清理機制）

## 後續優化建議

1. **檔案清理**: 建立 cron job 定期清理舊的匯出檔案
2. **權限控制**: 加入使用者權限檢查
3. **自訂範本**: 允許使用者自訂匯出格式
4. **多格式支援**: 未來可擴充支援 PDF 格式
5. **錯誤處理**: 加入更詳細的錯誤訊息和 logging

## 完成狀態

✅ 後端 Excel 匯出功能實作完成
✅ API 路由設定完成
✅ 前端呼叫 API 功能實作完成
✅ 中文亂碼問題已預防（使用 UTF-8 和 .xlsx 格式）
✅ 文件更新完成
