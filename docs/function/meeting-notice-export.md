# 會議通知匯出功能說明

## 功能概述

會議通知匯出功能允許使用者將會議資料匯出為 Word 格式的會議通知文件（.docx），使用預定義的範本自動填入會議相關資訊。

## 技術架構

### 後端技術

- **PHP 套件**: PHPOffice/PHPWord v1.4.0
- **範本引擎**: TemplateProcessor
- **Service 層**: WordExportService

### 前端技術

- **Composable**: useMeetings
- **下載機制**: Fetch API + Blob

## 檔案結構

```
backend/
├── app/
│   ├── Controllers/Api/
│   │   └── MeetingController.php        # exportNotice() 方法
│   └── Services/
│       └── WordExportService.php        # Word 匯出服務
└── writable/
    ├── templates/
    │   └── meeting_notice_template.docx # 範本檔案
    └── exports/                         # 匯出檔案暫存目錄

frontend/
├── composables/
│   └── useMeetings.js                   # exportMeetingNotice() 函數
└── pages/tables/meeting/[meetingId]/
    └── basic-info.vue                   # 匯出按鈕與功能

source/
└── word/
    └── [範本檔案].docx                  # 原始範本檔案
```

## API 規格

### Endpoint

```
GET /api/meetings/{id}/export-notice
```

### 請求參數

| 參數 | 類型 | 位置 | 必填 | 說明 |
|------|------|------|------|------|
| id | integer | path | 是 | 會議 ID |

### 請求標頭

```
Authorization: Bearer {token}
```

### 回應

**成功回應 (200)**

- Content-Type: `application/vnd.openxmlformats-officedocument.wordprocessingml.document`
- Content-Disposition: `attachment; filename="[更新會名稱]_[會議名稱]會議通知_YYYYmmdd.docx"`
- 回傳 Word 檔案的二進位資料

**錯誤回應**

```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND|FORBIDDEN|EXPORT_ERROR|INTERNAL_ERROR",
    "message": "錯誤訊息"
  }
}
```

## 範本變數

範本檔案中可使用以下變數（使用 `${變數名稱}` 格式）：

### 基本資訊

| 變數名稱 | 說明 | 範例 |
|----------|------|------|
| `${urban_renewal_name}` | 所屬更新會名稱 | 臺北市南港區玉成段二小段435地號等17筆土地更新單元都市更新會 |
| `${meeting_name}` | 會議名稱 | 114年度第一屆第1次會員大會 |
| `${meeting_type}` | 會議類型 | 會員大會 |
| `${meeting_date}` | 會議日期 | 2025年11月09日 |
| `${meeting_time}` | 會議時間 | 14:00 |
| `${meeting_location}` | 開會地點 | 台北市南港區玉成街1號 |

### 發文資訊

| 變數名稱 | 說明 | 範例 |
|----------|------|------|
| `${notice_doc_number}` | 發文字號 | 北市都更 |
| `${notice_word_number}` | 字第 | 字第 |
| `${notice_mid_number}` | 中間號碼 | 1140001 |
| `${notice_end_number}` | 號 | 號 |

### 聯絡資訊

| 變數名稱 | 說明 | 範例 |
|----------|------|------|
| `${chairman_name}` | 理事長姓名 | 王大明 |
| `${contact_name}` | 聯絡人姓名 | 陳小明 |
| `${contact_phone}` | 聯絡人電話 | 02-1234-5678 |
| `${attachments}` | 附件 | 會議議程、投票單 |
| `${descriptions}` | 發文說明 | 一、請準時出席\n二、攜帶證件 |

## 使用說明

### 後端使用

#### 1. 安裝 PHPWord

```bash
composer require phpoffice/phpword
```

#### 2. 準備範本檔案

將 Word 範本放置於 `backend/writable/templates/meeting_notice_template.docx`

#### 3. 呼叫 Service

```php
use App\Services\WordExportService;

$wordExportService = new WordExportService();
$result = $wordExportService->exportMeetingNotice($meetingData);

if ($result['success']) {
    $filepath = $result['filepath'];
    $filename = $result['filename'];
    // 處理檔案下載
} else {
    // 處理錯誤
    echo $result['error'];
}
```

### 前端使用

#### 1. 引入 Composable

```javascript
const { exportMeetingNotice } = useMeetings()
```

#### 2. 呼叫匯出函數

```javascript
const handleExport = async (meetingId) => {
  const response = await exportMeetingNotice(meetingId)

  if (response.success) {
    // 檔案會自動下載
    showSuccess('匯出成功', '會議通知已成功匯出')
  } else {
    showError('匯出失敗', response.error?.message || '無法匯出會議通知')
  }
}
```

#### 3. UI 按鈕範例

```vue
<UButton
  v-if="selectedMeeting"
  color="blue"
  @click="exportMeetingNotice"
  :loading="isExporting"
>
  <Icon name="heroicons:document-arrow-down" class="w-4 h-4 mr-2" />
  匯出會議通知
</UButton>
```

## 檔名規則

匯出的檔案會依照以下規則命名：

```
[更新會名稱]_[會議名稱]會議通知_YYYYmmdd.docx
```

**範例**：
```
臺北市南港區玉成段二小段435地號等17筆土地更新單元都市更新會_114年度第一屆第1次會員大會會議通知_20251109.docx
```

## 權限控制

- **系統管理員 (admin)**: 可匯出所有會議通知
- **企業管理者 (company_manager)**: 只能匯出自己所屬更新會的會議通知
- **一般使用者**: 無法匯出

## 錯誤處理

### 常見錯誤

| 錯誤代碼 | 說明 | 解決方案 |
|----------|------|----------|
| NOT_FOUND | 會議不存在 | 確認會議 ID 是否正確 |
| FORBIDDEN | 無權限匯出 | 確認使用者權限 |
| EXPORT_ERROR | 匯出失敗 | 檢查範本檔案是否存在 |
| FILE_NOT_FOUND | 匯出檔案不存在 | 檢查寫入權限 |
| INTERNAL_ERROR | 系統錯誤 | 查看後端日誌 |

### 除錯方式

1. **檢查範本檔案**
   ```bash
   ls -la backend/writable/templates/meeting_notice_template.docx
   ```

2. **檢查目錄權限**
   ```bash
   chmod 755 backend/writable/templates
   chmod 755 backend/writable/exports
   ```

3. **查看後端日誌**
   ```bash
   tail -f backend/writable/logs/*.log
   ```

4. **測試 API**
   ```bash
   curl -H "Authorization: Bearer {token}" \
        http://localhost:4002/api/meetings/1/export-notice \
        --output test.docx
   ```

## 效能優化

### 檔案清理

自動清理舊的匯出檔案（預設 7 天）：

```php
$wordExportService = new WordExportService();
$deletedCount = $wordExportService->cleanOldExports(7);
```

建議設定 Cron Job 定期執行：

```cron
0 2 * * * cd /path/to/backend && php spark cleanup:exports
```

### 快取策略

- 匯出檔案暫存於 `writable/exports/` 目錄
- 使用者下載後可選擇立即刪除或保留一段時間
- 建議設定檔案最大保留時間避免佔用空間

## 範本製作指南

### 1. 建立 Word 範本

使用 Microsoft Word 或 LibreOffice Writer 建立範本文件

### 2. 插入變數

在需要替換的位置插入變數，格式為：`${變數名稱}`

**範例**：
```
會議通知

主旨：${meeting_name}

說明：
${urban_renewal_name}訂於${meeting_date} ${meeting_time}
假${meeting_location}召開${meeting_type}，敬請準時出席。

此致
${chairman_name} 敬啟

聯絡人：${contact_name}
電話：${contact_phone}
```

### 3. 儲存為 .docx

確保儲存為 Word 2007+ 格式（.docx），不支援舊版 .doc 格式

### 4. 測試範本

上傳範本後，建立測試會議並匯出，檢查變數是否正確替換

## 注意事項

1. **範本變數命名**: 變數名稱必須與資料庫欄位對應
2. **中文處理**: 確保範本使用 UTF-8 編碼
3. **特殊字元**: 檔名中的特殊字元會自動轉換為底線
4. **檔案大小**: 建議範本檔案大小不超過 5MB
5. **同步問題**: 修改範本後需清除舊的匯出檔案

## 更新記錄

| 日期 | 版本 | 說明 |
|------|------|------|
| 2025-11-08 | 1.0.0 | 初版建立，完成基本匯出功能 |

## 相關文件

- [PHPWord 官方文件](https://phpword.readthedocs.io/)
- [會議管理功能說明](./meeting-basic-info.md)
- [API 規格文件](../../specs/001-view/contracts/meetings.yaml)
