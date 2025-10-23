# 資料模型文件 - 都更計票系統

**版本**: 1.0.0
**建立日期**: 2025-10-08
**狀態**: Draft

## 目錄

1. [概述](#概述)
2. [實體關係圖](#實體關係圖)
3. [核心實體定義](#核心實體定義)
4. [資料完整性規則](#資料完整性規則)
5. [索引策略](#索引策略)
6. [驗證規則](#驗證規則)

---

## 概述

本文件定義都更計票系統的完整資料模型，包含 18 個核心資料表及其關聯關係。系統採用關聯式資料庫設計，遵循第三正規化（3NF）原則，確保資料一致性和完整性。

### 資料庫技術規格

- **資料庫管理系統**: MySQL 5.7+
- **字元編碼**: UTF-8 (utf8mb4)
- **時區**: UTC+8 (台灣時區)
- **軟刪除機制**: 所有主要資料表均支援軟刪除 (`deleted_at` 欄位)

---

## 實體關係圖

### 核心實體清單

系統包含以下 18 個核心資料表：

#### 1. 基礎資料模組
- `urban_renewals` - 都市更新會
- `property_owners` - 所有權人
- `counties` - 縣市
- `districts` - 鄉鎮市區
- `sections` - 地段

#### 2. 地籍資料模組
- `land_plots` - 土地資料
- `buildings` - 建物資料
- `owner_land_ownerships` - 土地持分關係
- `owner_building_ownerships` - 建物持分關係

#### 3. 會議管理模組
- `meetings` - 會議
- `meeting_attendances` - 會員報到
- `meeting_documents` - 會議文件

#### 4. 投票系統模組
- `voting_topics` - 投票議題
- `voting_records` - 投票記錄

#### 5. 使用者與系統模組
- `users` - 使用者
- `user_sessions` - 使用者 Session
- `system_settings` - 系統設定
- `notifications` - 通知

### 主要關聯關係

```
urban_renewals (1) ──────< (N) land_plots
urban_renewals (1) ──────< (N) buildings
urban_renewals (1) ──────< (N) property_owners
urban_renewals (1) ──────< (N) meetings
urban_renewals (1) ──────< (N) notifications

property_owners (N) ><───< (M) land_plots [透過 owner_land_ownerships]
property_owners (N) ><───< (M) buildings [透過 owner_building_ownerships]
property_owners (1) ──────< (N) meeting_attendances
property_owners (1) ──────< (N) voting_records

meetings (1) ──────< (N) meeting_attendances
meetings (1) ──────< (N) meeting_documents
meetings (1) ──────< (N) voting_topics

voting_topics (1) ──────< (N) voting_records

counties (1) ──────< (N) districts
districts (1) ──────< (N) sections

users (1) ──────< (N) user_sessions
users (1) ──────< (N) notifications
```

---

## 核心實體定義

### 1. urban_renewals (都市更新會)

**用途說明**: 儲存都市更新會的基本資料，是整個系統的核心實體。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| name | VARCHAR(255) | NOT NULL | - | 更新會名稱 |
| area | DECIMAL(10,2) | NOT NULL | - | 土地總面積 (平方公尺) |
| member_count | INT(11) UNSIGNED | NOT NULL | - | 所有權人總數 |
| chairman_name | VARCHAR(100) | NOT NULL | - | 理事長姓名 |
| chairman_phone | VARCHAR(20) | NOT NULL | - | 理事長電話 |
| address | TEXT | NULL | NULL | 設立地址 |
| representative | VARCHAR(100) | NULL | NULL | 負責人 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |
| deleted_at | DATETIME | NULL | NULL | 刪除時間 (軟刪除) |

**索引**:
- PRIMARY KEY: `id`
- INDEX: `name`
- INDEX: `created_at`

**驗證規則**:
- `name`: 必填，長度 1-255 字元
- `area`: 必填，大於 0
- `member_count`: 必填，大於等於 0
- `chairman_name`: 必填，長度 1-100 字元
- `chairman_phone`: 必填，符合電話格式

---

### 2. land_plots (土地資料)

**用途說明**: 儲存都市更新會範圍內的土地地籍資料。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| urban_renewal_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 所屬更新會 ID |
| county | VARCHAR(10) | NOT NULL | - | 縣市代碼 |
| district | VARCHAR(10) | NOT NULL | - | 行政區代碼 |
| section | VARCHAR(10) | NOT NULL | - | 段小段代碼 |
| land_number_main | VARCHAR(10) | NOT NULL | - | 地號母號 |
| land_number_sub | VARCHAR(10) | NULL | NULL | 地號子號 |
| land_area | DECIMAL(12,2) | NULL | NULL | 土地面積 (平方公尺) |
| is_representative | TINYINT(1) | NOT NULL | 0 | 是否為代表號 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |
| deleted_at | DATETIME | NULL | NULL | 刪除時間 (軟刪除) |

**外鍵關聯**:
- `urban_renewal_id` → `urban_renewals(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- INDEX: `urban_renewal_id`
- INDEX: `county, district, section`
- INDEX: `is_representative`
- INDEX: `created_at`

**驗證規則**:
- `urban_renewal_id`: 必填，必須存在於 urban_renewals 表
- `county`: 必填，必須為有效的縣市代碼
- `district`: 必填，必須為有效的行政區代碼
- `section`: 必填，必須為有效的段小段代碼
- `land_number_main`: 必填
- `land_area`: 選填，若有值則必須大於 0

---

### 3. buildings (建物資料)

**用途說明**: 儲存都市更新會範圍內的建物資料。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| urban_renewal_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 所屬更新會 ID |
| county | VARCHAR(10) | NOT NULL | - | 縣市代碼 |
| district | VARCHAR(10) | NOT NULL | - | 行政區代碼 |
| section | VARCHAR(10) | NOT NULL | - | 段小段代碼 |
| building_number_main | VARCHAR(10) | NOT NULL | - | 建號母號 |
| building_number_sub | VARCHAR(10) | NOT NULL | '000' | 建號子號 |
| building_area | DECIMAL(12,2) | NULL | NULL | 建物總面積 (平方公尺) |
| building_address | VARCHAR(255) | NULL | NULL | 建物門牌 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |
| deleted_at | DATETIME | NULL | NULL | 刪除時間 (軟刪除) |

**外鍵關聯**:
- `urban_renewal_id` → `urban_renewals(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- INDEX: `urban_renewal_id`
- INDEX: `building_number_main, building_number_sub`
- INDEX: `county, district, section`

**驗證規則**:
- `urban_renewal_id`: 必填，必須存在於 urban_renewals 表
- `county`: 必填，必須為有效的縣市代碼
- `district`: 必填，必須為有效的行政區代碼
- `section`: 必填，必須為有效的段小段代碼
- `building_number_main`: 必填
- `building_area`: 選填，若有值則必須大於 0

---

### 4. property_owners (所有權人)

**用途說明**: 儲存都市更新會的所有權人基本資料。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| urban_renewal_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 所屬更新會 ID |
| owner_code | VARCHAR(20) | UNIQUE, NOT NULL | - | 所有權人編號 (自動產生) |
| name | VARCHAR(100) | NOT NULL | - | 所有權人姓名 |
| id_number | VARCHAR(20) | NULL | NULL | 身分證字號 |
| phone1 | VARCHAR(20) | NULL | NULL | 電話 1 |
| phone2 | VARCHAR(20) | NULL | NULL | 電話 2 |
| contact_address | TEXT | NULL | NULL | 聯絡地址 |
| household_address | TEXT | NULL | NULL | 戶籍地址 |
| exclusion_type | ENUM | NULL | NULL | 排除計算類型 |
| notes | TEXT | NULL | NULL | 備註 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |
| deleted_at | DATETIME | NULL | NULL | 刪除時間 (軟刪除) |

**ENUM 值 (exclusion_type)**:
- '法院囑託查封'
- '假扣押'
- '假處分'
- '破產登記'
- '未經繼承'

**外鍵關聯**:
- `urban_renewal_id` → `urban_renewals(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- UNIQUE KEY: `owner_code`
- INDEX: `urban_renewal_id`
- INDEX: `name`

**驗證規則**:
- `urban_renewal_id`: 必填，必須存在於 urban_renewals 表
- `owner_code`: 自動產生，唯一值
- `name`: 必填，長度 1-100 字元
- `id_number`: 選填，若有值則必須符合身分證格式
- `exclusion_type`: 選填，必須為列舉值之一

---

### 5. owner_land_ownerships (土地持分關係)

**用途說明**: 記錄所有權人與土地之間的持分關係。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| property_owner_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 所有權人 ID |
| land_plot_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 土地 ID |
| ownership_numerator | INT(11) UNSIGNED | NOT NULL | - | 持有比例分子 |
| ownership_denominator | INT(11) UNSIGNED | NOT NULL | - | 持有比例分母 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |
| deleted_at | DATETIME | NULL | NULL | 刪除時間 (軟刪除) |

**外鍵關聯**:
- `property_owner_id` → `property_owners(id)` ON DELETE CASCADE ON UPDATE CASCADE
- `land_plot_id` → `land_plots(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- UNIQUE KEY: `property_owner_id, land_plot_id`
- INDEX: `property_owner_id`
- INDEX: `land_plot_id`

**驗證規則**:
- `property_owner_id`: 必填，必須存在於 property_owners 表
- `land_plot_id`: 必填，必須存在於 land_plots 表
- `ownership_numerator`: 必填，大於 0
- `ownership_denominator`: 必填，大於 0
- 同一所有權人與土地組合不可重複

---

### 6. owner_building_ownerships (建物持分關係)

**用途說明**: 記錄所有權人與建物之間的持分關係。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| property_owner_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 所有權人 ID |
| building_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 建物 ID |
| ownership_numerator | INT(11) UNSIGNED | NOT NULL | - | 持有比例分子 |
| ownership_denominator | INT(11) UNSIGNED | NOT NULL | - | 持有比例分母 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |
| deleted_at | DATETIME | NULL | NULL | 刪除時間 (軟刪除) |

**外鍵關聯**:
- `property_owner_id` → `property_owners(id)` ON DELETE CASCADE ON UPDATE CASCADE
- `building_id` → `buildings(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- UNIQUE KEY: `property_owner_id, building_id`
- INDEX: `property_owner_id`
- INDEX: `building_id`

**驗證規則**:
- `property_owner_id`: 必填，必須存在於 property_owners 表
- `building_id`: 必填，必須存在於 buildings 表
- `ownership_numerator`: 必填，大於 0
- `ownership_denominator`: 必填，大於 0
- 同一所有權人與建物組合不可重複

---

### 7. meetings (會議)

**用途說明**: 儲存都市更新會的會議基本資料和法定人數設定。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| urban_renewal_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 所屬更新會 ID |
| meeting_name | VARCHAR(255) | NOT NULL | - | 會議名稱 |
| meeting_type | ENUM | NOT NULL | '會員大會' | 會議類型 |
| meeting_date | DATE | NOT NULL | - | 會議日期 |
| meeting_time | TIME | NOT NULL | - | 會議時間 |
| meeting_location | TEXT | NULL | NULL | 開會地點 |
| attendee_count | INT(11) UNSIGNED | NOT NULL | 0 | 出席人數 |
| calculated_total_count | INT(11) UNSIGNED | NOT NULL | 0 | 納入計算總人數 |
| observer_count | INT(11) UNSIGNED | NOT NULL | 0 | 列席總人數 |
| quorum_land_area_numerator | INT(11) UNSIGNED | NOT NULL | 0 | 成會土地面積比例分子 |
| quorum_land_area_denominator | INT(11) UNSIGNED | NOT NULL | 1 | 成會土地面積比例分母 |
| quorum_land_area | DECIMAL(12,2) | NOT NULL | 0.00 | 成會土地面積 (平方公尺) |
| quorum_building_area_numerator | INT(11) UNSIGNED | NOT NULL | 0 | 成會建物面積比例分子 |
| quorum_building_area_denominator | INT(11) UNSIGNED | NOT NULL | 1 | 成會建物面積比例分母 |
| quorum_building_area | DECIMAL(12,2) | NOT NULL | 0.00 | 成會建物面積 (平方公尺) |
| quorum_member_numerator | INT(11) UNSIGNED | NOT NULL | 0 | 成會人數比例分子 |
| quorum_member_denominator | INT(11) UNSIGNED | NOT NULL | 1 | 成會人數比例分母 |
| quorum_member_count | INT(11) UNSIGNED | NOT NULL | 0 | 成會人數 |
| meeting_status | ENUM | NOT NULL | 'draft' | 會議狀態 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |
| deleted_at | DATETIME | NULL | NULL | 刪除時間 (軟刪除) |

**ENUM 值 (meeting_type)**:
- '會員大會'
- '理事會'
- '監事會'
- '臨時會議'

**ENUM 值 (meeting_status)**:
- 'draft' (草稿)
- 'scheduled' (已排程)
- 'in_progress' (進行中)
- 'completed' (已完成)
- 'cancelled' (已取消)

**外鍵關聯**:
- `urban_renewal_id` → `urban_renewals(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- INDEX: `urban_renewal_id`
- INDEX: `meeting_date`
- INDEX: `meeting_status`
- INDEX: `meeting_date, meeting_status`

**狀態轉換規則**:
```
draft → scheduled → in_progress → completed
  ↓         ↓            ↓
cancelled  cancelled  cancelled
```

**驗證規則**:
- `urban_renewal_id`: 必填，必須存在於 urban_renewals 表
- `meeting_name`: 必填，長度 1-255 字元
- `meeting_date`: 必填，必須為有效日期
- `meeting_time`: 必填，必須為有效時間
- `quorum_*_denominator`: 必須大於 0
- `meeting_status`: 必須按照狀態轉換規則變更

---

### 8. meeting_attendances (會員報到)

**用途說明**: 記錄會員在特定會議的出席報到資料。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| meeting_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 會議 ID |
| property_owner_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 所有權人 ID |
| attendance_type | ENUM | NOT NULL | 'absent' | 出席類型 |
| proxy_person | VARCHAR(100) | NULL | NULL | 代理人姓名 |
| check_in_time | DATETIME | NULL | NULL | 簽到時間 |
| is_calculated | TINYINT(1) | NOT NULL | 1 | 是否納入計算 |
| notes | TEXT | NULL | NULL | 備註 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |

**ENUM 值 (attendance_type)**:
- 'present' (親自出席)
- 'proxy' (委託出席)
- 'absent' (缺席)

**外鍵關聯**:
- `meeting_id` → `meetings(id)` ON DELETE CASCADE ON UPDATE CASCADE
- `property_owner_id` → `property_owners(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- UNIQUE KEY: `meeting_id, property_owner_id`
- INDEX: `meeting_id`
- INDEX: `property_owner_id`
- INDEX: `attendance_type`
- INDEX: `check_in_time`

**驗證規則**:
- `meeting_id`: 必填，必須存在於 meetings 表
- `property_owner_id`: 必填，必須存在於 property_owners 表
- 同一會議同一所有權人不可重複報到
- `attendance_type` 為 'proxy' 時，`proxy_person` 必填

---

### 9. meeting_documents (會議文件)

**用途說明**: 儲存與會議相關的文件資料。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| meeting_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 會議 ID |
| document_type | ENUM | NOT NULL | - | 文件類型 |
| document_name | VARCHAR(255) | NOT NULL | - | 文件名稱 |
| file_path | TEXT | NOT NULL | - | 檔案路徑 |
| file_name | VARCHAR(255) | NOT NULL | - | 原始檔名 |
| file_size | INT(11) UNSIGNED | NOT NULL | - | 檔案大小 (bytes) |
| mime_type | VARCHAR(100) | NULL | NULL | MIME 類型 |
| uploaded_by | INT(11) UNSIGNED | NULL | NULL | 上傳者 ID (未來擴展) |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |
| deleted_at | DATETIME | NULL | NULL | 刪除時間 (軟刪除) |

**ENUM 值 (document_type)**:
- 'agenda' (議程)
- 'minutes' (會議紀錄)
- 'attendance' (簽到表)
- 'notice' (通知)
- 'other' (其他)

**外鍵關聯**:
- `meeting_id` → `meetings(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- INDEX: `meeting_id`
- INDEX: `document_type`

**驗證規則**:
- `meeting_id`: 必填，必須存在於 meetings 表
- `document_name`: 必填，長度 1-255 字元
- `file_size`: 必填，必須小於系統設定的檔案大小限制
- `mime_type`: 必須為系統允許的檔案類型

---

### 10. voting_topics (投票議題)

**用途說明**: 儲存會議的投票議題資料和統計結果。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| meeting_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 會議 ID |
| topic_number | VARCHAR(20) | NOT NULL | - | 議題編號 |
| topic_title | VARCHAR(500) | NOT NULL | - | 議題標題 |
| topic_description | TEXT | NULL | NULL | 議題描述 |
| voting_method | ENUM | NOT NULL | 'simple_majority' | 投票方式 |
| total_votes | INT(11) UNSIGNED | NOT NULL | 0 | 總票數 |
| agree_votes | INT(11) UNSIGNED | NOT NULL | 0 | 同意票數 |
| disagree_votes | INT(11) UNSIGNED | NOT NULL | 0 | 不同意票數 |
| abstain_votes | INT(11) UNSIGNED | NOT NULL | 0 | 棄權票數 |
| total_land_area | DECIMAL(12,2) | NOT NULL | 0.00 | 總土地面積 |
| agree_land_area | DECIMAL(12,2) | NOT NULL | 0.00 | 同意土地面積 |
| disagree_land_area | DECIMAL(12,2) | NOT NULL | 0.00 | 不同意土地面積 |
| abstain_land_area | DECIMAL(12,2) | NOT NULL | 0.00 | 棄權土地面積 |
| total_building_area | DECIMAL(12,2) | NOT NULL | 0.00 | 總建物面積 |
| agree_building_area | DECIMAL(12,2) | NOT NULL | 0.00 | 同意建物面積 |
| disagree_building_area | DECIMAL(12,2) | NOT NULL | 0.00 | 不同意建物面積 |
| abstain_building_area | DECIMAL(12,2) | NOT NULL | 0.00 | 棄權建物面積 |
| voting_result | ENUM | NOT NULL | 'pending' | 投票結果 |
| voting_status | ENUM | NOT NULL | 'draft' | 議題狀態 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |
| deleted_at | DATETIME | NULL | NULL | 刪除時間 (軟刪除) |

**ENUM 值 (voting_method)**:
- 'simple_majority' (簡單多數)
- 'absolute_majority' (絕對多數)
- 'two_thirds_majority' (三分之二多數)
- 'unanimous' (全體一致)

**ENUM 值 (voting_result)**:
- 'pending' (待定)
- 'passed' (通過)
- 'failed' (不通過)
- 'withdrawn' (撤回)

**ENUM 值 (voting_status)**:
- 'draft' (草稿)
- 'active' (進行中)
- 'closed' (已關閉)

**外鍵關聯**:
- `meeting_id` → `meetings(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- INDEX: `meeting_id`
- INDEX: `topic_number`
- INDEX: `voting_status`
- INDEX: `voting_result`

**狀態轉換規則**:
```
draft → active → closed
```

**投票方式判定規則**:
- **simple_majority** (簡單多數): 同意票數 > 不同意票數
- **absolute_majority** (絕對多數): 同意票數 > 應出席數 ÷ 2
- **two_thirds_majority** (三分之二多數): 同意票數 ≥ 應出席數 × 2/3
- **unanimous** (全體一致): 同意票數 = 應出席數

**驗證規則**:
- `meeting_id`: 必填，必須存在於 meetings 表
- `topic_number`: 必填，在同一會議內不可重複
- `topic_title`: 必填，長度 1-500 字元
- 只有在 `voting_status` 為 'active' 時可以投票
- 投票統計欄位自動計算，不可手動修改

---

### 11. voting_records (投票記錄)

**用途說明**: 記錄所有權人對特定議題的投票記錄。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| voting_topic_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 投票議題 ID |
| property_owner_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 所有權人 ID |
| vote_choice | ENUM | NOT NULL | - | 投票選擇 |
| vote_time | DATETIME | NULL | NULL | 投票時間 |
| voter_name | VARCHAR(100) | NULL | NULL | 投票人姓名 (快照) |
| land_area_weight | DECIMAL(12,2) | NOT NULL | 0.00 | 土地面積權重 |
| building_area_weight | DECIMAL(12,2) | NOT NULL | 0.00 | 建物面積權重 |
| notes | TEXT | NULL | NULL | 備註 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |

**ENUM 值 (vote_choice)**:
- 'agree' (同意)
- 'disagree' (不同意)
- 'abstain' (棄權)

**外鍵關聯**:
- `voting_topic_id` → `voting_topics(id)` ON DELETE CASCADE ON UPDATE CASCADE
- `property_owner_id` → `property_owners(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- UNIQUE KEY: `voting_topic_id, property_owner_id`
- INDEX: `voting_topic_id`
- INDEX: `property_owner_id`
- INDEX: `vote_choice`
- INDEX: `vote_time`

**驗證規則**:
- `voting_topic_id`: 必填，必須存在於 voting_topics 表
- `property_owner_id`: 必填，必須存在於 property_owners 表
- 同一議題同一所有權人只能投票一次
- 只有已報到的所有權人可以投票
- 只有在議題狀態為 'active' 時可以投票或修改投票

---

### 12. users (使用者)

**用途說明**: 儲存系統使用者的帳號資料和權限設定。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| username | VARCHAR(100) | UNIQUE, NOT NULL | - | 使用者帳號 |
| email | VARCHAR(255) | UNIQUE | NULL | 電子信箱 |
| password_hash | VARCHAR(255) | NOT NULL | - | 密碼雜湊值 |
| role | ENUM | NOT NULL | 'member' | 使用者角色 |
| urban_renewal_id | INT(11) UNSIGNED | NULL, FOREIGN KEY | NULL | 所屬更新會 ID |
| property_owner_id | INT(11) UNSIGNED | NULL, FOREIGN KEY | NULL | 關聯所有權人 ID |
| full_name | VARCHAR(100) | NULL | NULL | 真實姓名 |
| phone | VARCHAR(20) | NULL | NULL | 聯絡電話 |
| is_active | TINYINT(1) | NOT NULL | 1 | 帳號是否啟用 |
| last_login_at | DATETIME | NULL | NULL | 最後登入時間 |
| login_attempts | INT(11) UNSIGNED | NOT NULL | 0 | 登入嘗試次數 |
| locked_until | DATETIME | NULL | NULL | 帳號鎖定至 |
| password_reset_token | VARCHAR(255) | NULL | NULL | 密碼重設令牌 |
| password_reset_expires | DATETIME | NULL | NULL | 密碼重設令牌過期時間 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |
| deleted_at | DATETIME | NULL | NULL | 刪除時間 (軟刪除) |

**ENUM 值 (role)**:
- 'admin' (管理員)
- 'chairman' (理事長)
- 'member' (會員)
- 'observer' (列席者)

**外鍵關聯**:
- `urban_renewal_id` → `urban_renewals(id)` ON DELETE SET NULL ON UPDATE CASCADE
- `property_owner_id` → `property_owners(id)` ON DELETE SET NULL ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- UNIQUE KEY: `username`
- UNIQUE KEY: `email`
- INDEX: `role`
- INDEX: `is_active`
- INDEX: `urban_renewal_id`
- INDEX: `last_login_at`

**驗證規則**:
- `username`: 必填，唯一，長度 3-100 字元
- `email`: 選填，若有值則必須符合電子信箱格式且唯一
- `password_hash`: 必填，使用 bcrypt 加密
- `login_attempts`: 達到系統設定的最大嘗試次數時鎖定帳號
- `locked_until`: 帳號鎖定時間到期後自動解鎖

---

### 13. user_sessions (使用者 Session)

**用途說明**: 儲存使用者的登入 Session 資料。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| user_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 使用者 ID |
| session_token | VARCHAR(255) | UNIQUE, NOT NULL | - | 會話令牌 |
| refresh_token | VARCHAR(255) | NULL | NULL | 刷新令牌 |
| expires_at | DATETIME | NOT NULL | - | 會話過期時間 |
| refresh_expires_at | DATETIME | NULL | NULL | 刷新令牌過期時間 |
| ip_address | VARCHAR(45) | NULL | NULL | 登入 IP 位址 |
| user_agent | TEXT | NULL | NULL | 瀏覽器用戶代理 |
| device_info | JSON | NULL | NULL | 裝置資訊 |
| is_active | TINYINT(1) | NOT NULL | 1 | 會話是否有效 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| last_activity_at | DATETIME | NULL | NULL | 最後活動時間 |

**外鍵關聯**:
- `user_id` → `users(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- UNIQUE KEY: `session_token`
- INDEX: `user_id`
- INDEX: `expires_at`
- INDEX: `is_active`
- INDEX: `last_activity_at`
- INDEX: `user_id, is_active`

**驗證規則**:
- `user_id`: 必填，必須存在於 users 表
- `session_token`: 自動產生，唯一，使用 JWT 格式
- `expires_at`: 必填，根據系統設定的 session_timeout 計算
- 過期的 session 自動設為 `is_active = 0`

---

### 14. system_settings (系統設定)

**用途說明**: 儲存系統的全域設定參數。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| setting_key | VARCHAR(100) | UNIQUE, NOT NULL | - | 設定項目鍵值 |
| setting_value | LONGTEXT | NULL | NULL | 設定項目值 |
| setting_type | ENUM | NOT NULL | 'string' | 設定資料類型 |
| category | VARCHAR(50) | NOT NULL | 'general' | 設定分類 |
| title | VARCHAR(255) | NULL | NULL | 設定項目名稱 |
| description | TEXT | NULL | NULL | 設定項目說明 |
| validation_rules | TEXT | NULL | NULL | 驗證規則 (JSON 格式) |
| is_public | TINYINT(1) | NOT NULL | 0 | 是否為公開設定 |
| is_editable | TINYINT(1) | NOT NULL | 1 | 是否可編輯 |
| display_order | INT(11) | NOT NULL | 0 | 顯示順序 |
| created_by | INT(11) UNSIGNED | NULL, FOREIGN KEY | NULL | 建立者 ID |
| updated_by | INT(11) UNSIGNED | NULL, FOREIGN KEY | NULL | 更新者 ID |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |

**ENUM 值 (setting_type)**:
- 'string' (字串)
- 'number' (數字)
- 'boolean' (布林值)
- 'json' (JSON 物件)
- 'encrypted' (加密資料)

**外鍵關聯**:
- `created_by` → `users(id)` ON DELETE SET NULL ON UPDATE CASCADE
- `updated_by` → `users(id)` ON DELETE SET NULL ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- UNIQUE KEY: `setting_key`
- INDEX: `category`
- INDEX: `is_public`
- INDEX: `is_editable`
- INDEX: `category, display_order`

**預設設定項目**:
- `app_name`: 應用程式名稱
- `app_version`: 系統版本
- `maintenance_mode`: 維護模式
- `session_timeout`: 會話逾時時間
- `max_login_attempts`: 最大登入嘗試次數
- `account_lockout_time`: 帳號鎖定時間
- `voting_result_threshold`: 投票通過門檻
- `meeting_quorum_threshold`: 會議成會門檻
- `file_upload_max_size`: 檔案上傳大小限制
- `allowed_file_types`: 允許上傳檔案類型

**驗證規則**:
- `setting_key`: 必填，唯一
- `setting_value`: 必須符合 `setting_type` 定義的資料類型
- `is_editable` 為 0 的設定不可修改

---

### 15. notifications (通知)

**用途說明**: 儲存系統通知資料。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| user_id | INT(11) UNSIGNED | NULL, FOREIGN KEY | NULL | 接收者用戶 ID |
| notification_type | ENUM | NOT NULL | - | 通知類型 |
| priority | ENUM | NOT NULL | 'normal' | 通知優先級 |
| title | VARCHAR(255) | NOT NULL | - | 通知標題 |
| message | TEXT | NOT NULL | - | 通知內容 |
| related_type | VARCHAR(50) | NULL | NULL | 相關資源類型 |
| related_id | INT(11) UNSIGNED | NULL | NULL | 相關資源 ID |
| action_url | TEXT | NULL | NULL | 點擊後導向的 URL |
| action_text | VARCHAR(50) | NULL | NULL | 操作按鈕文字 |
| metadata | JSON | NULL | NULL | 額外的通知元資料 |
| is_read | TINYINT(1) | NOT NULL | 0 | 是否已讀 |
| read_at | DATETIME | NULL | NULL | 已讀時間 |
| is_global | TINYINT(1) | NOT NULL | 0 | 是否為全域通知 |
| urban_renewal_id | INT(11) UNSIGNED | NULL, FOREIGN KEY | NULL | 限定更新會範圍 |
| expires_at | DATETIME | NULL | NULL | 通知過期時間 |
| send_email | TINYINT(1) | NOT NULL | 0 | 是否發送郵件通知 |
| email_sent_at | DATETIME | NULL | NULL | 郵件發送時間 |
| send_sms | TINYINT(1) | NOT NULL | 0 | 是否發送簡訊通知 |
| sms_sent_at | DATETIME | NULL | NULL | 簡訊發送時間 |
| created_by | INT(11) UNSIGNED | NULL, FOREIGN KEY | NULL | 通知建立者 ID |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |
| deleted_at | DATETIME | NULL | NULL | 刪除時間 (軟刪除) |

**ENUM 值 (notification_type)**:
- 'meeting_notice' (會議通知)
- 'meeting_reminder' (會議提醒)
- 'voting_start' (投票開始)
- 'voting_end' (投票結束)
- 'voting_reminder' (投票提醒)
- 'check_in_reminder' (報到提醒)
- 'system_maintenance' (系統維護)
- 'user_account' (使用者帳號)
- 'document_upload' (文件上傳)
- 'report_ready' (報表就緒)
- 'permission_changed' (權限變更)
- 'system_alert' (系統警示)

**ENUM 值 (priority)**:
- 'low' (低)
- 'normal' (一般)
- 'high' (高)
- 'urgent' (緊急)

**外鍵關聯**:
- `user_id` → `users(id)` ON DELETE CASCADE ON UPDATE CASCADE
- `urban_renewal_id` → `urban_renewals(id)` ON DELETE CASCADE ON UPDATE CASCADE
- `created_by` → `users(id)` ON DELETE SET NULL ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- INDEX: `user_id`
- INDEX: `notification_type`
- INDEX: `priority`
- INDEX: `is_read`
- INDEX: `is_global`
- INDEX: `urban_renewal_id`
- INDEX: `expires_at`
- INDEX: `created_at`
- INDEX: `user_id, is_read`
- INDEX: `related_type, related_id`

**驗證規則**:
- `title`: 必填，長度 1-255 字元
- `message`: 必填
- `is_global` 為 1 時，`user_id` 可為 NULL (表示全域通知)
- 過期的通知不再顯示於列表中

---

### 16. counties (縣市)

**用途說明**: 儲存台灣縣市資料。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| code | VARCHAR(10) | UNIQUE, NOT NULL | - | 縣市代碼 |
| name | VARCHAR(50) | NOT NULL | - | 縣市名稱 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |

**索引**:
- PRIMARY KEY: `id`
- UNIQUE KEY: `code`

**驗證規則**:
- `code`: 必填，唯一
- `name`: 必填

---

### 17. districts (鄉鎮市區)

**用途說明**: 儲存台灣鄉鎮市區資料。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| county_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 所屬縣市 ID |
| code | VARCHAR(10) | NOT NULL | - | 行政區代碼 |
| name | VARCHAR(50) | NOT NULL | - | 行政區名稱 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |

**外鍵關聯**:
- `county_id` → `counties(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- INDEX: `county_id`

**驗證規則**:
- `county_id`: 必填，必須存在於 counties 表
- `code`: 必填
- `name`: 必填

---

### 18. sections (地段)

**用途說明**: 儲存台灣地段資料。

**資料表結構**:

| 欄位名稱 | 資料類型 | 約束 | 預設值 | 說明 |
|---------|---------|------|--------|------|
| id | INT(11) UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | - | 主鍵 |
| district_id | INT(11) UNSIGNED | NOT NULL, FOREIGN KEY | - | 所屬行政區 ID |
| code | VARCHAR(10) | NOT NULL | - | 地段代碼 |
| name | VARCHAR(100) | NOT NULL | - | 地段名稱 |
| created_at | DATETIME | NULL | NULL | 建立時間 |
| updated_at | DATETIME | NULL | NULL | 更新時間 |

**外鍵關聯**:
- `district_id` → `districts(id)` ON DELETE CASCADE ON UPDATE CASCADE

**索引**:
- PRIMARY KEY: `id`
- INDEX: `district_id`

**驗證規則**:
- `district_id`: 必填，必須存在於 districts 表
- `code`: 必填
- `name`: 必填

---

## 資料完整性規則

### 參照完整性 (Referential Integrity)

1. **外鍵約束**: 所有外鍵關聯均設定 `ON DELETE` 和 `ON UPDATE` 動作
   - **CASCADE**: 主表記錄刪除/更新時，從表記錄也隨之刪除/更新
   - **SET NULL**: 主表記錄刪除時，從表外鍵欄位設為 NULL
   - **RESTRICT**: 禁止刪除有關聯記錄的主表資料

2. **軟刪除機制**: 主要業務資料表使用 `deleted_at` 欄位實現軟刪除
   - 刪除操作僅設定 `deleted_at` 時間戳，不實際移除記錄
   - 查詢時需排除 `deleted_at IS NOT NULL` 的記錄
   - 可實現資料恢復和審計追蹤功能

### 唯一性約束 (Uniqueness Constraints)

1. **單欄位唯一性**:
   - `property_owners.owner_code`: 所有權人編號必須唯一
   - `users.username`: 使用者帳號必須唯一
   - `users.email`: 電子信箱必須唯一
   - `system_settings.setting_key`: 系統設定鍵必須唯一

2. **複合欄位唯一性**:
   - `owner_land_ownerships(property_owner_id, land_plot_id)`: 同一所有權人與土地組合唯一
   - `owner_building_ownerships(property_owner_id, building_id)`: 同一所有權人與建物組合唯一
   - `meeting_attendances(meeting_id, property_owner_id)`: 同一會議同一所有權人只能報到一次
   - `voting_records(voting_topic_id, property_owner_id)`: 同一議題同一所有權人只能投票一次

### 業務邏輯約束

1. **會議狀態轉換**:
   - 會議狀態必須按照 `draft → scheduled → in_progress → completed` 順序轉換
   - 任何狀態均可轉換為 `cancelled`
   - 已完成或已取消的會議不可再次修改狀態

2. **投票規則**:
   - 只有已報到且 `is_calculated = 1` 的所有權人可以投票
   - 只有在議題狀態為 `active` 時可以投票或修改投票
   - 議題關閉後，投票記錄不可再修改

3. **持分比例**:
   - `ownership_numerator` (分子) 必須大於 0
   - `ownership_denominator` (分母) 必須大於 0
   - 建議在應用層驗證同一地籍的所有持分總和是否合理

4. **會議法定人數**:
   - 所有 `quorum_*_denominator` 欄位必須大於 0
   - 法定人數設定必須在會議開始前確定

---

## 索引策略

### 主鍵索引 (Primary Key Index)

所有資料表均使用自動遞增的整數 `id` 作為主鍵，提供最佳的查詢和關聯效能。

### 外鍵索引 (Foreign Key Index)

所有外鍵欄位均建立索引，加速關聯查詢：
- `urban_renewal_id`
- `property_owner_id`
- `land_plot_id`
- `building_id`
- `meeting_id`
- `voting_topic_id`
- `user_id`

### 唯一索引 (Unique Index)

確保資料唯一性的欄位建立唯一索引：
- `property_owners.owner_code`
- `users.username`
- `users.email`
- `system_settings.setting_key`
- `user_sessions.session_token`

### 複合索引 (Composite Index)

針對常用的多欄位查詢建立複合索引：
- `land_plots(county, district, section)`: 地籍查詢
- `buildings(county, district, section)`: 建物查詢
- `meetings(meeting_date, meeting_status)`: 會議列表查詢
- `notifications(user_id, is_read)`: 未讀通知查詢
- `user_sessions(user_id, is_active)`: 有效 Session 查詢

### 時間戳索引 (Timestamp Index)

針對時間範圍查詢建立索引：
- `created_at`: 建立時間
- `updated_at`: 更新時間
- `meeting_date`: 會議日期
- `check_in_time`: 簽到時間
- `vote_time`: 投票時間
- `expires_at`: 過期時間

### 狀態索引 (Status Index)

針對狀態篩選查詢建立索引：
- `meeting_status`: 會議狀態
- `voting_status`: 投票狀態
- `attendance_type`: 出席類型
- `is_active`: 是否啟用
- `is_read`: 是否已讀

---

## 驗證規則

### 資料格式驗證

1. **電話號碼**:
   - 格式: 09\d{8} 或 0\d{1,2}-\d{6,8}
   - 範例: 0912345678, 02-12345678

2. **身分證字號**:
   - 格式: [A-Z]\d{9}
   - 範例: A123456789
   - 需驗證檢查碼

3. **電子信箱**:
   - 格式: RFC 5322 標準
   - 範例: user@example.com

4. **日期時間**:
   - 日期格式: YYYY-MM-DD
   - 時間格式: HH:MM:SS
   - 日期時間格式: YYYY-MM-DD HH:MM:SS

### 數值範圍驗證

1. **面積欄位**: 必須大於 0
   - `land_area`, `building_area`
   - 建議範圍: 0.01 ~ 999999.99 平方公尺

2. **持分比例**:
   - `ownership_numerator` > 0
   - `ownership_denominator` > 0
   - 建議 `ownership_numerator` ≤ `ownership_denominator`

3. **法定人數設定**:
   - 所有 `quorum_*_denominator` > 0
   - `quorum_*_numerator` ≤ `quorum_*_denominator`

4. **檔案大小**:
   - `file_size` 必須在系統設定的限制內
   - 預設限制: 10 MB (10485760 bytes)

### 字串長度驗證

| 欄位類型 | 最小長度 | 最大長度 | 備註 |
|---------|---------|---------|------|
| name, full_name | 1 | 100 | 人名、單位名稱 |
| username | 3 | 100 | 使用者帳號 |
| email | 5 | 255 | 電子信箱 |
| phone | 10 | 20 | 電話號碼 |
| title | 1 | 255 | 標題 |
| description | 0 | 65535 | 描述文字 (TEXT) |

### 業務規則驗證

1. **會議規則**:
   - 會議日期不可早於今天
   - 同一更新會同一時間不可有多場進行中的會議
   - 會議開始前必須設定法定人數

2. **報到規則**:
   - 只能為所屬更新會的所有權人建立報到記錄
   - 同一會議同一所有權人不可重複報到
   - `attendance_type` 為 'proxy' 時，`proxy_person` 必填

3. **投票規則**:
   - 只有已報到的所有權人可以投票
   - 同一議題同一所有權人只能投票一次
   - 議題狀態為 'closed' 後不可再投票或修改投票

4. **文件規則**:
   - 檔案類型必須在允許清單內
   - 檔案大小不可超過系統設定的限制
   - 檔案路徑必須存在且可讀取

---

## 資料保留與清理政策

### 資料保留期限

1. **永久保留**:
   - `urban_renewals`: 都市更新會資料
   - `property_owners`: 所有權人資料
   - `land_plots`, `buildings`: 地籍資料
   - `meetings`: 會議記錄
   - `voting_topics`, `voting_records`: 投票記錄

2. **有限期保留** (建議 5 年):
   - `meeting_documents`: 會議文件
   - `notifications`: 通知記錄
   - `meeting_logs`: 會議操作日誌

3. **短期保留** (建議 1 年):
   - `user_sessions`: 使用者 Session
   - `system_setting_history`: 系統設定變更歷史

### 定期清理任務

1. **過期 Session 清理**:
   - 清理 `expires_at < NOW()` 的 `user_sessions` 記錄
   - 建議每日執行

2. **過期通知清理**:
   - 清理 `expires_at < NOW()` 且 `created_at < NOW() - INTERVAL 30 DAY` 的 `notifications`
   - 建議每週執行

3. **舊日誌清理**:
   - 清理 `created_at < NOW() - INTERVAL 1 YEAR` 的 `meeting_logs`
   - 建議每月執行

4. **臨時檔案清理**:
   - 清理孤立的 `meeting_documents` (關聯的會議已刪除)
   - 建議每月執行

---

## 版本歷史

| 版本 | 日期 | 說明 | 作者 |
|-----|------|------|------|
| 1.0.0 | 2025-10-08 | 初始版本，定義完整資料模型 | System |

---

## 附錄

### A. 資料表大小估算

假設一個典型的都市更新會：
- 所有權人: 500 人
- 土地筆數: 300 筆
- 建物筆數: 400 筆
- 會議: 每年 12 場，共計 5 年 = 60 場
- 投票議題: 每場會議 10 個議題 = 600 個議題
- 投票記錄: 600 議題 × 500 人 = 300,000 筆

預估資料表大小：
- `property_owners`: ~250 KB
- `land_plots`: ~150 KB
- `buildings`: ~200 KB
- `owner_land_ownerships`: ~300 KB
- `owner_building_ownerships`: ~400 KB
- `meetings`: ~30 KB
- `voting_topics`: ~300 KB
- `voting_records`: ~60 MB
- **總計**: 約 65-70 MB (含索引約 100-120 MB)

### B. 效能優化建議

1. **查詢優化**:
   - 避免 SELECT *，只查詢需要的欄位
   - 使用 LIMIT 限制結果數量
   - 善用索引，避免全表掃描
   - 使用 EXPLAIN 分析查詢計畫

2. **索引優化**:
   - 定期執行 OPTIMIZE TABLE 整理索引
   - 監控慢查詢日誌，針對性建立索引
   - 避免過多索引影響寫入效能

3. **快取策略**:
   - 使用 Redis 快取熱門查詢結果
   - 快取地區資料 (counties, districts, sections)
   - 快取系統設定 (system_settings)

4. **分頁查詢**:
   - 使用 LIMIT + OFFSET 或游標分頁
   - 避免深度分頁 (OFFSET 過大)
   - 考慮使用 ID 範圍分頁

### C. 備份與還原策略

1. **完整備份**: 每日凌晨執行
   ```bash
   mysqldump -u root -p --single-transaction urban_renewal > backup_full_$(date +%Y%m%d).sql
   ```

2. **增量備份**: 每小時執行 Binary Log 備份
   ```bash
   mysqlbinlog --stop-never mysql-bin.000001 > incremental_backup.sql
   ```

3. **還原測試**: 每週執行備份還原測試

4. **異地備份**: 將備份檔案上傳至雲端儲存

---

**文件結束**
