# MeetingAttendanceModel 實作說明

## 問題描述
執行匯出簽到結果功能時，後端出現錯誤：
```
Class "App\Models\MeetingAttendanceModel" not found
```

## 原因分析
`MeetingAttendanceController` 控制器中使用了 `MeetingAttendanceModel`，但該 Model 檔案不存在。檢查後發現：
- 資料表 `meeting_attendances` 已在 migration 中建立
- 但對應的 Model 類別檔案遺漏未建立

## 解決方案
建立 `MeetingAttendanceModel.php` 檔案，實作完整的會議出席記錄管理功能。

## 檔案位置
`/backend/app/Models/MeetingAttendanceModel.php`

## Model 實作內容

### 基本設定
- **資料表**: `meeting_attendances`
- **主鍵**: `id`
- **時間戳記**: 啟用 (created_at, updated_at)
- **軟刪除**: 未啟用
- **回傳格式**: Array

### 允許的欄位 (Allowed Fields)
1. `meeting_id` - 會議ID
2. `property_owner_id` - 所有權人ID
3. `attendance_type` - 出席類型 (present/proxy/absent)
4. `proxy_person` - 代理人姓名
5. `check_in_time` - 簽到時間
6. `is_calculated` - 是否納入計算
7. `notes` - 備註

### 驗證規則 (Validation Rules)
- `meeting_id`: 必填、整數
- `property_owner_id`: 必填、整數
- `attendance_type`: 必填、必須為 present/proxy/absent 其中之一
- `proxy_person`: 非必填、最長100字元
- `is_calculated`: 非必填、必須為0或1
- `notes`: 非必填

### 主要方法

#### 1. `getMeetingAttendances($meetingId, $page, $perPage, $filters)`
取得會議出席記錄列表（分頁）
- 支援篩選條件：出席類型、搜尋關鍵字
- 自動 JOIN property_owners 表取得所有權人資訊
- 支援分頁功能
- 按所有權人代碼排序

#### 2. `getAttendanceWithOwnerInfo($attendanceId)`
取得單筆出席記錄並包含所有權人資訊
- 回傳包含 owner_name, owner_code, exclusion_type

#### 3. `getDetailedAttendanceStatistics($meetingId)`
取得會議出席詳細統計
- 總人數 (total_count)
- 親自出席人數 (present_count)
- 委託出席人數 (proxy_count)
- 未出席人數 (absent_count)
- 已出席人數 (attended_count)
- 納入計算總人數 (calculated_count)
- 納入計算已出席人數 (calculated_attended_count)

#### 4. `getAllMeetingAttendances($meetingId)`
取得會議的所有出席記錄（不分頁）
- 用於匯出功能
- 包含所有權人資訊
- 按所有權人代碼排序

#### 5. `checkAttendanceExists($meetingId, $propertyOwnerId)`
檢查出席記錄是否存在
- 避免重複建立記錄

#### 6. `upsertAttendance($data)`
批次建立或更新出席記錄
- 如果記錄存在則更新
- 如果記錄不存在則新增

#### 7. `getAttendanceRate($meetingId)`
取得會議的出席率
- 計算公式：(已出席人數 / 總人數) × 100%
- 回傳百分比（小數點後2位）

#### 8. `getCalculatedAttendanceRate($meetingId)`
取得會議的計算出席率（排除不納入計算的）
- 計算公式：(納入計算已出席人數 / 納入計算總人數) × 100%
- 回傳百分比（小數點後2位）

## 與 Controller 的整合

### MeetingAttendanceController 使用的方法
1. `getMeetingAttendances()` - index() 方法中使用
2. `getAttendanceWithOwnerInfo()` - checkIn(), update() 方法中使用
3. `getDetailedAttendanceStatistics()` - statistics(), export() 方法中使用
4. `where()`, `first()` - 各種查詢操作
5. `insert()`, `update()` - 建立和更新記錄

## 資料表結構參考

```sql
CREATE TABLE meeting_attendances (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    meeting_id INT(11) UNSIGNED NOT NULL,
    property_owner_id INT(11) UNSIGNED NOT NULL,
    attendance_type ENUM('present', 'proxy', 'absent') DEFAULT 'absent',
    proxy_person VARCHAR(100),
    check_in_time DATETIME,
    is_calculated TINYINT(1) DEFAULT 1,
    notes TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    UNIQUE KEY unique_meeting_owner (meeting_id, property_owner_id),
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (property_owner_id) REFERENCES property_owners(id) ON DELETE CASCADE
);
```

## 測試建議

1. **基本 CRUD 測試**:
   - 建立出席記錄
   - 讀取出席記錄
   - 更新出席記錄
   - 刪除出席記錄

2. **統計功能測試**:
   - 測試各種統計數據的正確性
   - 測試出席率計算

3. **查詢功能測試**:
   - 測試分頁功能
   - 測試篩選功能
   - 測試搜尋功能

4. **匯出功能測試**:
   - 測試 getAllMeetingAttendances() 是否正確回傳所有記錄
   - 測試與 Excel 匯出功能的整合

## 相關檔案

- Model: `/backend/app/Models/MeetingAttendanceModel.php`
- Controller: `/backend/app/Controllers/Api/MeetingAttendanceController.php`
- Migration: `/backend/app/Database/Migrations/2025-01-01-000008_CreateMeetingManagementTables.php`
- Routes: `/backend/app/Config/Routes.php`

## 完成狀態

✅ MeetingAttendanceModel 已建立
✅ 所有必要方法已實作
✅ 驗證規則已設定
✅ 與 Controller 整合完成
✅ PHP 語法檢查通過
✅ 已提交至 Git

## 後續建議

1. 建立單元測試確保 Model 方法正確運作
2. 加入更多統計方法（如：按日期統計、按類型統計）
3. 考慮加入快取機制提升查詢效能
4. 加入更詳細的錯誤處理和日誌記錄
