# 會議通知範本變數替換記錄

## 概述

本文件詳細記錄對 `meeting_notice_template.docx` 進行的變數替換工作。原始範本中包含硬編碼的範例資料，已全數替換為動態變數，使其能正確配合後端 `WordExportService::replaceMeetingNoticeVariables()` 方法進行資料填入。

**修改日期**：2025-11-09
**修改版本**：1.0.0
**修改人員**：Claude Code
**範本位置**：`backend/writable/templates/meeting_notice_template.docx`

---

## 修改摘要

### 修改原因

原始範本檔案中所有內容都是硬編碼的範例資料，不支援動態替換。需要將這些硬編碼內容替換為符合 PHPWord TemplateProcessor 格式的變數（`${變數名稱}`），以便後端能夠正確進行資料填入。

### 修改範圍

- 替換硬編碼值為變數：7 個主要項目
- 涉及變數總數：11 個
- 文檔中的變數位置：多個段落和欄位

### 修改方式

1. 解壓 .docx 檔案（ZIP 格式）到臨時目錄
2. 修改 `word/document.xml` 中的硬編碼內容
3. 將修改後的檔案重新壓縮為 .docx 格式
4. 驗證新範本中的變數完整性

---

## 完整替換清單

### 1. 所屬更新會名稱 - ${urban_renewal_name}

**原始硬編碼**：
```
臺北市南港區玉成段二小段435地號等17筆土地更新單元都市更新會
```

**替換為**：
```
${urban_renewal_name}
```

**出現位置**：
- 文件標題（第一段，置中粗體）
- 開會事由段落：「召開『...』會議」
- 副本段落：存查單位引用
- 其他引用處

**資料來源**：`urban_renewals.name`（透過 LEFT JOIN）

**必填**：✅ 是（匯出時驗證）

**備註**：此變數在文檔中出現多次，所有出現處都已替換

---

### 2. 會議名稱 - ${meeting_name}

**原始硬編碼**：
```
114年度第一屆第1次會員大會
```

**替換為**：
```
${meeting_name}
```

**出現位置**：
- 開會事由段落：更新會名稱後面的會議名稱部分

**資料來源**：`meetings.meeting_name`

**資料類型**：VARCHAR(255)

**必填**：✅ 是（匯出時驗證）

**預設值**：空字串

---

### 3. 會議日期與時間 - ${meeting_date} ${meeting_time}

**原始硬編碼**：
```
114年03月15日 (星期六) 14時00分
```

**替換為**：
```
${meeting_date} ${meeting_time}
```

**出現位置**：
- 開會時間欄位（「開會時間：」之後）

**資料來源**：
- `${meeting_date}` → `meetings.meeting_date`
  - 資料庫格式：DATE (YYYY-MM-DD)
  - 輸出格式：Y年m月d日（經過 `formatDate()` 格式化）
  - 範例：2025-11-09 → 2025年11月09日

- `${meeting_time}` → `meetings.meeting_time`
  - 資料庫格式：TIME (HH:MM:SS)
  - 輸出格式：保持原始格式

**必填**：
- meeting_date：✅ 是（匯出時驗證）
- meeting_time：❌ 否

**備註**：原始範本中包含「(星期六)」文字，此部分已移除，僅保留日期和時間變數

---

### 4. 開會地點 - ${meeting_location}

**原始硬編碼**：
```
臺北市立成德國民小學/仁愛樓一樓樂齡堂教室
```

**替換為**：
```
${meeting_location}
```

**出現位置**：
- 開會地點欄位（「開會地點：」之後）

**資料來源**：`meetings.meeting_location`

**資料類型**：TEXT

**必填**：❌ 否

**預設值**：空字串

---

### 5. 發文字號（組合變數）- ${notice_doc_number}${notice_word_number}${notice_mid_number}${notice_end_number}

**原始硬編碼**：
```
None
```

**替換為**：
```
${notice_doc_number}${notice_word_number}${notice_mid_number}${notice_end_number}
```

**出現位置**：
- 發文字號欄位（「發文字號：」之後）

**資料來源**：
- `${notice_doc_number}` → `meeting_notices.notice_doc_number`
  - 資料類型：VARCHAR
  - 必填：❌ 否
  - 範例：北市都更

- `${notice_word_number}` → `meeting_notices.notice_word_number`
  - 資料類型：VARCHAR
  - 必填：❌ 否
  - 固定值：通常為「字第」

- `${notice_mid_number}` → `meeting_notices.notice_mid_number`
  - 資料類型：VARCHAR
  - 必填：❌ 否
  - 範例：1140001

- `${notice_end_number}` → `meeting_notices.notice_end_number`
  - 資料類型：VARCHAR
  - 必填：❌ 否
  - 固定值：通常為「號」

**完整組合範例**：
```
北市都更字第1140001號
```

**預設值**：空字串（各子變數）

---

### 6. 理事長姓名 - ${chairman_name}

**原始硬編碼**：
```
王文杞
```

**替換為**：
```
${chairman_name}
```

**出現位置**：
- 主持人欄位：「理事長 」後面的姓名
- 聯絡人及電話欄位：聯絡人姓名（第一個出現位置）

**資料來源**：`urban_renewals.chairman_name`

**資料類型**：VARCHAR(100)

**必填**：❌ 否

**預設值**：空字串

**備註**：此變數在文檔中出現 2 次，都已替換

---

### 7. 聯絡人電話 - ${contact_phone}

**原始硬編碼**：
```
0933734063
```

**替換為**：
```
${contact_phone}
```

**出現位置**：
- 聯絡人及電話欄位：電話號碼部分

**資料來源**：
- `meeting_notices.contact_phone`（優先）
- 或 `urban_renewals.chairman_phone`（備用）

**資料類型**：VARCHAR

**必填**：❌ 否

**預設值**：空字串

**範例**：
```
02-1234-5678
```

---

## 變數統計表

### 已替換變數（11 個）

| # | 變數名稱 | 出現次數 | 資料來源 | 必填 |
|---|---------|--------|---------|------|
| 1 | `${urban_renewal_name}` | 多次 | urban_renewals.name | ✅ |
| 2 | `${meeting_name}` | 1 次 | meetings.meeting_name | ✅ |
| 3 | `${meeting_date}` | 1 次 | meetings.meeting_date | ✅ |
| 4 | `${meeting_time}` | 1 次 | meetings.meeting_time | ❌ |
| 5 | `${meeting_location}` | 1 次 | meetings.meeting_location | ❌ |
| 6 | `${notice_doc_number}` | 1 次 | meeting_notices.notice_doc_number | ❌ |
| 7 | `${notice_word_number}` | 1 次 | meeting_notices.notice_word_number | ❌ |
| 8 | `${notice_mid_number}` | 1 次 | meeting_notices.notice_mid_number | ❌ |
| 9 | `${notice_end_number}` | 1 次 | meeting_notices.notice_end_number | ❌ |
| 10 | `${chairman_name}` | 2 次 | urban_renewals.chairman_name | ❌ |
| 11 | `${contact_phone}` | 1 次 | meeting_notices.contact_phone | ❌ |

---

### 文檔中未替換的變數（4 個）

以下變數在 `meeting-notice-export-data-mapping.md` 中有定義，但原始範本中沒有對應的硬編碼內容，因此未進行替換：

| # | 變數名稱 | 原因 | 備註 |
|---|---------|------|------|
| 1 | `${meeting_type}` | 原始範本不包含此欄位 | 會議類型：會員大會/理事會/監事會/臨時會議 |
| 2 | `${contact_name}` | 原始範本不包含此欄位 | 聯絡人姓名 |
| 3 | `${attachments}` | 原始範本不包含此欄位 | 附件說明 |
| 4 | `${descriptions}` | 原始範本不包含此欄位 | 發文說明（支援陣列自動編號） |

**建議**：
- 若需要使用這些變數，應在範本中添加對應的欄位或文本內容
- 可參考 `meeting-notice-export-data-mapping.md` 中的說明進行添加

---

## 完整變數映射表

| # | 變數 | 原始內容 | 狀態 | 資料來源 | 必填 | 說明 |
|---|-----|---------|------|---------|------|------|
| 1 | `${urban_renewal_name}` | 臺北市南港區玉成段二小段435地號等17筆土地更新單元都市更新會 | ✅ | urban_renewals.name | ✅ | 基本資訊 |
| 2 | `${meeting_name}` | 114年度第一屆第1次會員大會 | ✅ | meetings.meeting_name | ✅ | 基本資訊 |
| 3 | `${meeting_type}` | - | ⚠️ | meetings.meeting_type | ❌ | 未替換 |
| 4 | `${meeting_date}` | 114年03月15日 | ✅ | meetings.meeting_date | ✅ | 基本資訊 |
| 5 | `${meeting_time}` | 14時00分 | ✅ | meetings.meeting_time | ❌ | 基本資訊 |
| 6 | `${meeting_location}` | 臺北市立成德國民小學/仁愛樓一樓樂齡堂教室 | ✅ | meetings.meeting_location | ❌ | 基本資訊 |
| 7 | `${notice_doc_number}` | None | ✅ | meeting_notices.notice_doc_number | ❌ | 發文資訊 |
| 8 | `${notice_word_number}` | None | ✅ | meeting_notices.notice_word_number | ❌ | 發文資訊 |
| 9 | `${notice_mid_number}` | None | ✅ | meeting_notices.notice_mid_number | ❌ | 發文資訊 |
| 10 | `${notice_end_number}` | None | ✅ | meeting_notices.notice_end_number | ❌ | 發文資訊 |
| 11 | `${chairman_name}` | 王文杞 | ✅ | urban_renewals.chairman_name | ❌ | 聯絡資訊 |
| 12 | `${contact_name}` | - | ⚠️ | meeting_notices.contact_name | ❌ | 未替換 |
| 13 | `${contact_phone}` | 0933734063 | ✅ | meeting_notices.contact_phone | ❌ | 聯絡資訊 |
| 14 | `${attachments}` | - | ⚠️ | meeting_notices.attachments | ❌ | 未替換 |
| 15 | `${descriptions}` | - | ⚠️ | meeting_notices.descriptions | ❌ | 未替換 |

**圖例**：
- ✅ = 已替換
- ⚠️ = 未替換

---

## 驗證結果

### 驗證指令

```bash
unzip -p backend/writable/templates/meeting_notice_template.docx word/document.xml \
  | grep -o '\${[^}]*}' | sort -u
```

### 驗證輸出

```
${chairman_name}
${contact_phone}
${meeting_date}
${meeting_location}
${meeting_name}
${meeting_time}
${notice_doc_number}
${notice_end_number}
${notice_mid_number}
${notice_word_number}
${urban_renewal_name}
```

**驗證結果**：✅ 通過

已確認新範本中包含全部 11 個變數，與替換清單一致。

---

## 修改技術細節

### 修改流程

1. **解壓**
   ```bash
   unzip meeting_notice_template.docx -d /tmp/template_work
   ```

2. **修改 XML**
   - 編輯 `word/document.xml`
   - 使用 sed 進行文本替換
   - 共執行 7 次替換操作

3. **重新打包**
   ```bash
   python3 -c "
   import zipfile, os
   with zipfile.ZipFile('new.docx', 'w', zipfile.ZIP_DEFLATED) as z:
       for root, dirs, files in os.walk('/tmp/template_work'):
           for f in files:
               z.write(os.path.join(root, f), os.path.relpath(...))
   "
   ```

4. **驗證**
   - 檢查新檔案完整性
   - 驗證所有變數正確替換

### 影響範圍

- **修改檔案**：`backend/writable/templates/meeting_notice_template.docx`
- **相關服務**：`backend/app/Services/WordExportService.php`
- **相關控制器**：`backend/app/Controllers/Api/MeetingController.php`
- **相關模型**：`backend/app/Models/MeetingModel.php`

---

## 注意事項

### 1. 新增變數時的注意項

若要在範本中新增 `${contact_name}`、`${meeting_type}`、`${attachments}` 或 `${descriptions}` 等變數，需要：

1. 在 Word 範本中手動編輯，添加這些變數
2. 確保格式為 `${變數名稱}`（大小寫敏感）
3. 在 `WordExportService.php` 中已有對應的 `setValue()` 呼叫
4. 完成後進行驗證測試

### 2. 與後端程式碼的對應

根據 `meeting-notice-export-data-mapping.md`，以下後端程式碼已支援這些變數：

- **位置**：`backend/app/Services/WordExportService.php:116-157`
- **方法**：`replaceMeetingNoticeVariables($templateProcessor, $data)`

### 3. PHPWord TemplateProcessor 規則

- 變數格式：`${variable_name}`（必須完全匹配）
- 大小寫敏感：`${Urban_Renewal_Name}` 和 `${urban_renewal_name}` 不同
- 一次性替換：不可重複使用同一變數
- 空值處理：未設置的變數會顯示空字串

### 4. 測試建議

修改後建議進行以下測試：

```bash
# 1. 驗證範本檔案完整性
unzip -t backend/writable/templates/meeting_notice_template.docx

# 2. 驗證變數完整性
unzip -p backend/writable/templates/meeting_notice_template.docx word/document.xml \
  | grep -o '\${[^}]*}' | sort -u

# 3. 匯出測試
# 透過前端匯出會議通知，確認所有變數正確填入
```

---

## 相關文件參考

- [會議通知匯出功能 - 資料填入規則詳細說明](./meeting-notice-export-data-mapping.md)
- [會議管理資料表設計](../sql/meeting-management.md)
- [Word 匯出服務](../../backend/app/Services/WordExportService.php)
- [會議 API 控制器](../../backend/app/Controllers/Api/MeetingController.php)
- [會議資料模型](../../backend/app/Models/MeetingModel.php)

---

## 更新記錄

| 日期 | 版本 | 說明 | 作者 |
|------|------|------|------|
| 2025-11-09 | 1.0.0 | 初版建立，記錄範本變數替換工作 | Claude Code |

---

## 附註

本文件與 `meeting-notice-export-data-mapping.md` 配合使用。後者說明資料如何進入系統和填入規則，本文件說明範本本身的變數替換情況。

若有任何關於範本變數或匯出功能的疑問，請參考相關文件或聯絡開發團隊。
