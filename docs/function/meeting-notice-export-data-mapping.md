# 會議通知匯出功能 - 資料填入規則詳細說明

## 目錄

1. [概述](#概述)
2. [資料來源](#資料來源)
3. [資料流程](#資料流程)
4. [範本變數映射規則](#範本變數映射規則)
5. [資料處理邏輯](#資料處理邏輯)
6. [範例說明](#範例說明)

---

## 概述

會議通知匯出功能使用 PHPOffice/PHPWord 套件的 TemplateProcessor 引擎，將資料庫中的會議資料填入預定義的 Word 範本中。本文檔詳細說明每個範本變數的資料來源、處理邏輯和填入規則。

**核心服務檔案**：`backend/app/Services/WordExportService.php:116-157`

---

## 資料來源

### 1. 主要資料表

#### meetings 表
會議基本資料表，包含：
- `id`: 會議 ID
- `urban_renewal_id`: 所屬更新會 ID（外鍵）
- `meeting_name`: 會議名稱
- `meeting_type`: 會議類型（ENUM: '會員大會', '理事會', '監事會', '臨時會議'）
- `meeting_date`: 會議日期（DATE 格式：YYYY-MM-DD）
- `meeting_time`: 會議時間（TIME 格式：HH:MM:SS）
- `meeting_location`: 開會地點（TEXT）
- `meeting_status`: 會議狀態

#### urban_renewals 表
所屬更新會資料表，透過 JOIN 取得：
- `name`: 更新會名稱
- `chairman_name`: 理事長姓名
- `chairman_phone`: 理事長電話

#### meeting_notices 表（選用）
會議通知資料表，若存在則包含：
- `notice_doc_number`: 發文字號前綴
- `notice_word_number`: 「字第」文字
- `notice_mid_number`: 發文字號中間號碼
- `notice_end_number`: 「號」後綴
- `contact_name`: 聯絡人姓名
- `contact_phone`: 聯絡人電話
- `attachments`: 附件說明
- `descriptions`: 發文說明（可為陣列）

### 2. 資料查詢方法

**Controller**: `backend/app/Controllers/Api/MeetingController.php:668`
```php
$meeting = $this->meetingModel->getMeetingWithDetails($id);
```

**Model**: `backend/app/Models/MeetingModel.php:144-189`
```php
public function getMeetingWithDetails($meetingId)
{
    $meeting = $this->select('meetings.*,
                              urban_renewals.name as urban_renewal_name,
                              urban_renewals.chairman_name,
                              urban_renewals.chairman_phone')
                   ->join('urban_renewals', 'urban_renewals.id = meetings.urban_renewal_id', 'left')
                   ->where('meetings.id', $meetingId)
                   ->where('meetings.deleted_at', null)
                   ->first();
    // ... 額外統計資料處理
    return $meeting;
}
```

---

## 資料流程

```
使用者點擊「匯出會議通知」
    ↓
前端：useMeetings.js - exportMeetingNotice(id)
    ↓
發送請求：GET /api/meetings/{id}/export-notice
    ↓
後端：MeetingController::exportNotice($id)
    ↓
取得資料：MeetingModel::getMeetingWithDetails($id)
    ├─ SELECT meetings.*
    ├─ LEFT JOIN urban_renewals
    └─ WHERE meetings.id = $id AND deleted_at IS NULL
    ↓
生成文檔：WordExportService::exportMeetingNotice($meeting)
    ├─ 驗證必要欄位
    ├─ 載入範本：meeting_notice_template.docx
    ├─ 替換變數：replaceMeetingNoticeVariables()
    ├─ 生成檔名
    └─ 儲存檔案
    ↓
下載檔案：透過 HTTP Response 回傳
```

---

## 範本變數映射規則

### 基本資訊變數

#### ${urban_renewal_name} - 所屬更新會名稱

**資料來源**：
```php
// 位置：WordExportService.php:119
$templateProcessor->setValue('urban_renewal_name', $data['urban_renewal_name'] ?? '');
```

**資料庫欄位**：`urban_renewals.name`
**資料類型**：VARCHAR(255)
**必填**：✅ 是（匯出時驗證）
**預設值**：空字串
**範例**：
```
臺北市南港區玉成段二小段435地號等17筆土地更新單元都市更新會
```

**處理邏輯**：
1. 透過 LEFT JOIN 從 urban_renewals 表取得
2. 若資料不存在，使用空字串
3. 不進行任何格式轉換

---

#### ${meeting_name} - 會議名稱

**資料來源**：
```php
// 位置：WordExportService.php:120
$templateProcessor->setValue('meeting_name', $data['meeting_name'] ?? '');
```

**資料庫欄位**：`meetings.meeting_name`
**資料類型**：VARCHAR(255)
**必填**：✅ 是（匯出時驗證）
**預設值**：空字串
**範例**：
```
114年度第一屆第1次會員大會
```

**處理邏輯**：
1. 直接從 meetings 表讀取
2. 保持原始格式不做轉換

---

#### ${meeting_type} - 會議類型

**資料來源**：
```php
// 位置：WordExportService.php:121
$templateProcessor->setValue('meeting_type', $data['meeting_type'] ?? '');
```

**資料庫欄位**：`meetings.meeting_type`
**資料類型**：ENUM('會員大會', '理事會', '監事會', '臨時會議')
**必填**：❌ 否
**預設值**：空字串
**可能值**：
- 會員大會
- 理事會
- 監事會
- 臨時會議

**範例**：
```
會員大會
```

---

#### ${meeting_date} - 會議日期

**資料來源**：
```php
// 位置：WordExportService.php:124-126
if (isset($data['meeting_date'])) {
    $templateProcessor->setValue('meeting_date', $this->formatDate($data['meeting_date']));
}
```

**資料庫欄位**：`meetings.meeting_date`
**資料類型**：DATE (YYYY-MM-DD)
**必填**：✅ 是（匯出時驗證）
**預設值**：無
**輸出格式**：Y年m月d日（例如：2025年11月09日）

**處理邏輯**：
```php
// 位置：WordExportService.php:165-176
private function formatDate(string $date): string
{
    try {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date; // 轉換失敗，返回原始值
        }
        return date('Y年m月d日', $timestamp);
    } catch (\Exception $e) {
        return $date; // 發生異常，返回原始值
    }
}
```

**範例**：
- 資料庫值：`2025-11-09`
- 輸出結果：`2025年11月09日`

---

#### ${meeting_time} - 會議時間

**資料來源**：
```php
// 位置：WordExportService.php:127-129
if (isset($data['meeting_time'])) {
    $templateProcessor->setValue('meeting_time', $data['meeting_time']);
}
```

**資料庫欄位**：`meetings.meeting_time`
**資料類型**：TIME (HH:MM:SS)
**必填**：❌ 否
**預設值**：無
**輸出格式**：保持原始格式（通常為 HH:MM）

**範例**：
- 資料庫值：`14:00:00`
- 輸出結果：`14:00:00` 或 `14:00`（依資料庫儲存格式）

---

#### ${meeting_location} - 開會地點

**資料來源**：
```php
// 位置：WordExportService.php:132
$templateProcessor->setValue('meeting_location', $data['meeting_location'] ?? '');
```

**資料庫欄位**：`meetings.meeting_location`
**資料類型**：TEXT
**必填**：❌ 否
**預設值**：空字串
**範例**：
```
台北市南港區玉成街1號
```

---

### 發文資訊變數

#### ${notice_doc_number} - 發文字號前綴

**資料來源**：
```php
// 位置：WordExportService.php:135
$templateProcessor->setValue('notice_doc_number', $data['notice_doc_number'] ?? '');
```

**資料庫欄位**：`meeting_notices.notice_doc_number` 或 `meetings` 表擴充欄位
**資料類型**：VARCHAR
**必填**：❌ 否
**預設值**：空字串
**範例**：
```
北市都更
```

---

#### ${notice_word_number} - 字第

**資料來源**：
```php
// 位置：WordExportService.php:136
$templateProcessor->setValue('notice_word_number', $data['notice_word_number'] ?? '');
```

**資料庫欄位**：`meeting_notices.notice_word_number`
**資料類型**：VARCHAR
**必填**：❌ 否
**預設值**：空字串
**固定值**：通常為「字第」
**範例**：
```
字第
```

---

#### ${notice_mid_number} - 發文號碼

**資料來源**：
```php
// 位置：WordExportService.php:137
$templateProcessor->setValue('notice_mid_number', $data['notice_mid_number'] ?? '');
```

**資料庫欄位**：`meeting_notices.notice_mid_number`
**資料類型**：VARCHAR
**必填**：❌ 否
**預設值**：空字串
**範例**：
```
1140001
```

---

#### ${notice_end_number} - 號後綴

**資料來源**：
```php
// 位置：WordExportService.php:138
$templateProcessor->setValue('notice_end_number', $data['notice_end_number'] ?? '');
```

**資料庫欄位**：`meeting_notices.notice_end_number`
**資料類型**：VARCHAR
**必填**：❌ 否
**預設值**：空字串
**固定值**：通常為「號」
**範例**：
```
號
```

**完整發文字號組合範例**：
```
北市都更字第1140001號
```

---

### 聯絡資訊變數

#### ${chairman_name} - 理事長姓名

**資料來源**：
```php
// 位置：WordExportService.php:141
$templateProcessor->setValue('chairman_name', $data['chairman_name'] ?? '');
```

**資料庫欄位**：`urban_renewals.chairman_name`
**資料類型**：VARCHAR(100)
**必填**：❌ 否
**預設值**：空字串
**範例**：
```
王大明
```

---

#### ${contact_name} - 聯絡人姓名

**資料來源**：
```php
// 位置：WordExportService.php:142
$templateProcessor->setValue('contact_name', $data['contact_name'] ?? '');
```

**資料庫欄位**：`meeting_notices.contact_name` 或自訂欄位
**資料類型**：VARCHAR
**必填**：❌ 否
**預設值**：空字串
**範例**：
```
陳小明
```

---

#### ${contact_phone} - 聯絡人電話

**資料來源**：
```php
// 位置：WordExportService.php:143
$templateProcessor->setValue('contact_phone', $data['contact_phone'] ?? '');
```

**資料庫欄位**：`meeting_notices.contact_phone` 或 `urban_renewals.chairman_phone`
**資料類型**：VARCHAR
**必填**：❌ 否
**預設值**：空字串
**範例**：
```
02-1234-5678
```

---

#### ${attachments} - 附件說明

**資料來源**：
```php
// 位置：WordExportService.php:144
$templateProcessor->setValue('attachments', $data['attachments'] ?? '');
```

**資料庫欄位**：`meeting_notices.attachments`
**資料類型**：TEXT
**必填**：❌ 否
**預設值**：空字串
**範例**：
```
會議議程、投票單
```

---

#### ${descriptions} - 發文說明

**資料來源**：
```php
// 位置：WordExportService.php:147-156
if (isset($data['descriptions']) && is_array($data['descriptions'])) {
    $descriptionsText = '';
    foreach ($data['descriptions'] as $index => $desc) {
        $chineseNum = $this->getChineseNumber($index + 1);
        $descriptionsText .= $chineseNum . '、' . $desc . "\n";
    }
    $templateProcessor->setValue('descriptions', $descriptionsText);
} else {
    $templateProcessor->setValue('descriptions', $data['descriptions'] ?? '');
}
```

**資料庫欄位**：`meeting_notices.descriptions`
**資料類型**：TEXT 或 JSON
**必填**：❌ 否
**預設值**：空字串

**處理邏輯**：
1. 如果是陣列格式，自動加入中文數字編號
2. 使用 `getChineseNumber()` 方法轉換數字（1-10 為中文，11+ 為阿拉伯數字）
3. 每個項目後加入換行符號

**陣列範例**：
```php
[
    "請準時出席",
    "攜帶證件",
    "會議資料請於會前下載"
]
```

**輸出結果**：
```
一、請準時出席
二、攜帶證件
三、會議資料請於會前下載
```

**字串範例**：
```
一、請準時出席
二、攜帶證件
```

**輸出結果**：
```
一、請準時出席
二、攜帶證件
```

---

## 資料處理邏輯

### 1. 必要欄位驗證

**驗證時機**：匯出前
**驗證位置**：`WordExportService.php:51-60`

```php
$requiredFields = ['urban_renewal_name', 'meeting_name', 'meeting_date'];
foreach ($requiredFields as $field) {
    if (!isset($meetingData[$field]) || empty($meetingData[$field])) {
        return [
            'success' => false,
            'error' => "缺少必要欄位: {$field}"
        ];
    }
}
```

**必填欄位**：
1. `urban_renewal_name` - 所屬更新會名稱
2. `meeting_name` - 會議名稱
3. `meeting_date` - 會議日期

若缺少任一欄位，匯出將失敗並返回錯誤訊息。

---

### 2. 日期格式轉換

**函數**：`formatDate(string $date): string`
**位置**：`WordExportService.php:165-176`

**轉換規則**：
- 輸入格式：`YYYY-MM-DD`（例如：2025-11-09）
- 輸出格式：`Y年m月d日`（例如：2025年11月09日）

**錯誤處理**：
- 若 `strtotime()` 轉換失敗，返回原始值
- 若發生異常，捕獲後返回原始值

**範例**：
| 輸入 | 輸出 |
|------|------|
| 2025-11-09 | 2025年11月09日 |
| 2025-01-01 | 2025年01月01日 |
| 2025-12-31 | 2025年12月31日 |
| 無效日期 | 無效日期（原樣返回）|

---

### 3. 中文數字轉換

**函數**：`getChineseNumber(int $num): string`
**位置**：`WordExportService.php:184-191`

```php
private function getChineseNumber(int $num): string
{
    $chineseNumbers = ['', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十'];
    if ($num <= 10) {
        return $chineseNumbers[$num];
    }
    return (string)$num;
}
```

**轉換規則**：
- 1-10：轉換為中文數字
- 11+：保持阿拉伯數字

**範例**：
| 輸入 | 輸出 |
|------|------|
| 1 | 一 |
| 5 | 五 |
| 10 | 十 |
| 11 | 11 |
| 20 | 20 |

---

### 4. 檔名生成規則

**函數**：檔名生成
**位置**：`WordExportService.php:78-88`

```php
// 生成檔名：[更新會名稱]_[會議名稱]會議通知_YYYYmmdd.docx
$date = date('Ymd');
if (isset($meetingData['meeting_date'])) {
    $date = str_replace('-', '', $meetingData['meeting_date']);
}

$filename = $this->sanitizeFilename(
    $meetingData['urban_renewal_name'] . '_' .
    $meetingData['meeting_name'] .
    '會議通知_' . $date . '.docx'
);
```

**規則**：
1. 格式：`[更新會名稱]_[會議名稱]會議通知_YYYYmmdd.docx`
2. 日期來源：優先使用 `meeting_date`，否則使用當前日期
3. 日期格式：移除連字符（`-`）變成 `YYYYmmdd`
4. 特殊字元處理：透過 `sanitizeFilename()` 清理

**檔名清理規則**：
```php
// 位置：WordExportService.php:199-204
private function sanitizeFilename(string $filename): string
{
    // 移除或替換非法字元
    $filename = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $filename);
    return $filename;
}
```

**被替換的字元**：`/ \ : * ? " < > |` → `_`

**範例**：
```
原始：臺北市南港區玉成段二小段435地號等17筆土地更新單元都市更新會_114年度第一屆第1次會員大會會議通知_20251109.docx
```

---

### 5. 空值處理

**處理策略**：使用 Null 合併運算子 (`??`)

所有變數都使用以下模式：
```php
$templateProcessor->setValue('variable_name', $data['variable_name'] ?? '');
```

**規則**：
- 若資料不存在：使用空字串 `''`
- 不會輸出 `null` 或 `undefined` 到文檔中

---

## 範例說明

### 完整資料範例

假設資料庫中有以下資料：

**meetings 表**：
```sql
id: 1
urban_renewal_id: 5
meeting_name: '114年度第一屆第1次會員大會'
meeting_type: '會員大會'
meeting_date: '2025-11-09'
meeting_time: '14:00:00'
meeting_location: '台北市南港區玉成街1號'
```

**urban_renewals 表**：
```sql
id: 5
name: '臺北市南港區玉成段二小段435地號等17筆土地更新單元都市更新會'
chairman_name: '王大明'
chairman_phone: '02-2345-6789'
```

**meeting_notices 表** (假設存在)：
```sql
meeting_id: 1
notice_doc_number: '北市都更'
notice_word_number: '字第'
notice_mid_number: '1140001'
notice_end_number: '號'
contact_name: '陳小明'
contact_phone: '02-1234-5678'
attachments: '會議議程、投票單、委託書'
descriptions: ["請準時出席", "攜帶身分證件", "會議資料請於會前下載"]
```

### 匯出結果

**Word 文檔中的變數替換**：

```
${urban_renewal_name} → 臺北市南港區玉成段二小段435地號等17筆土地更新單元都市更新會
${meeting_name} → 114年度第一屆第1次會員大會
${meeting_type} → 會員大會
${meeting_date} → 2025年11月09日
${meeting_time} → 14:00:00
${meeting_location} → 台北市南港區玉成街1號
${notice_doc_number} → 北市都更
${notice_word_number} → 字第
${notice_mid_number} → 1140001
${notice_end_number} → 號
${chairman_name} → 王大明
${contact_name} → 陳小明
${contact_phone} → 02-1234-5678
${attachments} → 會議議程、投票單、委託書
${descriptions} → 一、請準時出席
二、攜帶身分證件
三、會議資料請於會前下載
```

**檔案名稱**：
```
臺北市南港區玉成段二小段435地號等17筆土地更新單元都市更新會_114年度第一屆第1次會員大會會議通知_20251109.docx
```

---

### 範本使用範例

**Word 範本內容**：
```
【會議通知】

發文字號：${notice_doc_number}${notice_word_number}${notice_mid_number}${notice_end_number}

主旨：${meeting_name}

說明：
${urban_renewal_name}訂於${meeting_date} ${meeting_time}
假${meeting_location}召開${meeting_type}，敬請準時出席。

${descriptions}

附件：${attachments}

此致
${chairman_name} 敬啟

聯絡人：${contact_name}
電話：${contact_phone}
```

**匯出後的文檔內容**：
```
【會議通知】

發文字號：北市都更字第1140001號

主旨：114年度第一屆第1次會員大會

說明：
臺北市南港區玉成段二小段435地號等17筆土地更新單元都市更新會訂於2025年11月09日 14:00:00
假台北市南港區玉成街1號召開會員大會，敬請準時出席。

一、請準時出席
二、攜帶身分證件
三、會議資料請於會前下載

附件：會議議程、投票單、委託書

此致
王大明 敬啟

聯絡人：陳小明
電話：02-1234-5678
```

---

## 資料表擴充建議

### 目前狀況

根據代碼分析，部分變數（如 `notice_doc_number`、`contact_name` 等）可能尚未在資料庫中實作對應欄位。

### 建議擴充方案

#### 方案一：在 meetings 表中新增欄位

```sql
ALTER TABLE meetings
ADD COLUMN notice_doc_number VARCHAR(50) NULL COMMENT '發文字號前綴',
ADD COLUMN notice_word_number VARCHAR(20) NULL DEFAULT '字第' COMMENT '字第',
ADD COLUMN notice_mid_number VARCHAR(50) NULL COMMENT '發文號碼',
ADD COLUMN notice_end_number VARCHAR(20) NULL DEFAULT '號' COMMENT '號後綴',
ADD COLUMN contact_name VARCHAR(100) NULL COMMENT '聯絡人姓名',
ADD COLUMN contact_phone VARCHAR(20) NULL COMMENT '聯絡人電話',
ADD COLUMN attachments TEXT NULL COMMENT '附件說明',
ADD COLUMN descriptions TEXT NULL COMMENT '發文說明';
```

#### 方案二：使用獨立的 meeting_notices 表

已有 `meeting_notices` 表的設計（見 `docs/sql/meeting-management.md:51-73`），建議完整實作並在 `getMeetingWithDetails()` 中加入 JOIN。

**Model 修改範例**：
```php
public function getMeetingWithDetails($meetingId)
{
    $meeting = $this->select('meetings.*,
                              urban_renewals.name as urban_renewal_name,
                              urban_renewals.chairman_name,
                              urban_renewals.chairman_phone,
                              meeting_notices.notice_doc_number,
                              meeting_notices.notice_word_number,
                              meeting_notices.notice_mid_number,
                              meeting_notices.notice_end_number,
                              meeting_notices.contact_name,
                              meeting_notices.contact_phone,
                              meeting_notices.attachments,
                              meeting_notices.descriptions')
                   ->join('urban_renewals', 'urban_renewals.id = meetings.urban_renewal_id', 'left')
                   ->join('meeting_notices', 'meeting_notices.meeting_id = meetings.id', 'left')
                   ->where('meetings.id', $meetingId)
                   ->where('meetings.deleted_at', null)
                   ->first();
    // ...
    return $meeting;
}
```

---

## 疑難排解

### 常見問題

#### Q1: 範本變數沒有被替換？

**可能原因**：
1. 範本中的變數格式不正確（必須是 `${variable_name}`）
2. 資料庫中的資料為 NULL
3. 欄位名稱不符合映射規則

**解決方法**：
1. 檢查範本變數格式
2. 檢查資料庫資料
3. 查看後端日誌：`backend/writable/logs/*.log`

#### Q2: 匯出的檔案無法開啟？

**可能原因**：
1. 範本檔案損壞
2. PHPWord 版本不相容
3. 權限問題

**解決方法**：
```bash
# 檢查範本檔案
ls -la backend/writable/templates/meeting_notice_template.docx

# 檢查匯出目錄權限
chmod 755 backend/writable/exports

# 檢查 PHPWord 版本
composer show phpoffice/phpword
```

#### Q3: 日期格式顯示不正確？

**可能原因**：
1. 資料庫日期格式錯誤
2. `strtotime()` 無法解析

**解決方法**：
- 確保資料庫日期格式為 `YYYY-MM-DD`
- 檢查 `formatDate()` 函數執行結果

#### Q4: 發文說明沒有自動編號？

**可能原因**：
- 資料格式不是陣列

**解決方法**：
確保 `descriptions` 欄位儲存為 JSON 陣列格式：
```json
["項目一", "項目二", "項目三"]
```

---

## 附錄

### 相關檔案清單

| 檔案路徑 | 用途 | 關鍵函數/方法 |
|---------|------|--------------|
| `backend/app/Services/WordExportService.php` | Word 匯出服務 | `exportMeetingNotice()`, `replaceMeetingNoticeVariables()` |
| `backend/app/Controllers/Api/MeetingController.php` | 匯出 API 控制器 | `exportNotice()` |
| `backend/app/Models/MeetingModel.php` | 會議資料模型 | `getMeetingWithDetails()` |
| `frontend/composables/useMeetings.js` | 前端 API 封裝 | `exportMeetingNotice()` |
| `frontend/pages/tables/meeting/[meetingId]/basic-info.vue` | 匯出按鈕頁面 | `exportMeetingNotice()` |
| `backend/writable/templates/meeting_notice_template.docx` | Word 範本檔案 | - |

### 資料庫表關聯圖

```
meetings
├── urban_renewal_id → urban_renewals.id
│   ├── name (urban_renewal_name)
│   ├── chairman_name
│   └── chairman_phone
└── id → meeting_notices.meeting_id (選用)
    ├── notice_doc_number
    ├── notice_word_number
    ├── notice_mid_number
    ├── notice_end_number
    ├── contact_name
    ├── contact_phone
    ├── attachments
    └── descriptions
```

### PHPWord TemplateProcessor 特性

**支援的變數格式**：
- 標準變數：`${variable_name}`
- 圖片變數：`${image_variable}`（需額外處理）
- 表格變數：支援動態行（需額外設定）

**不支援**：
- 條件判斷（if/else）
- 迴圈（需手動處理）
- 複雜運算

**替換規則**：
- 大小寫敏感
- 完全匹配
- 一次性替換（不可重複使用同一變數）

---

## 更新記錄

| 日期 | 版本 | 說明 | 作者 |
|------|------|------|------|
| 2025-11-08 | 1.0.0 | 初版建立，完整說明資料填入規則 | Claude |

---

## 參考資料

- [PHPWord 官方文件](https://phpword.readthedocs.io/)
- [TemplateProcessor 文件](https://phpword.readthedocs.io/en/latest/templates-processing.html)
- [會議通知匯出功能說明](./meeting-notice-export.md)
- [會議管理資料表設計](../sql/meeting-management.md)
