# 都市更新系統 - 會議管理模組資料庫設計

## 概述
基於 Point 58 的會議管理需求，設計完整的會議管理資料庫結構。本設計整合現有的都市更新基礎資料，提供完整的會議管理功能。

## 會議管理核心資料表

### 1. meetings (會議基本資料)
```sql
CREATE TABLE meetings (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    urban_renewal_id INT(11) UNSIGNED NOT NULL COMMENT '所屬更新會ID',
    meeting_name VARCHAR(255) NOT NULL COMMENT '會議名稱',
    meeting_type ENUM('會員大會', '理事會', '監事會', '臨時會議') NOT NULL DEFAULT '會員大會' COMMENT '會議類型',
    meeting_date DATE NOT NULL COMMENT '會議日期',
    meeting_time TIME NOT NULL COMMENT '會議時間',
    meeting_location TEXT NULL COMMENT '開會地點',

    -- 出席統計
    attendee_count INT(11) UNSIGNED DEFAULT 0 COMMENT '出席人數',
    calculated_total_count INT(11) UNSIGNED DEFAULT 0 COMMENT '納入計算總人數',
    observer_count INT(11) UNSIGNED DEFAULT 0 COMMENT '列席總人數',

    -- 成會門檻設定
    quorum_land_area_numerator INT(11) UNSIGNED DEFAULT 0 COMMENT '成會土地面積比例分子',
    quorum_land_area_denominator INT(11) UNSIGNED DEFAULT 1 COMMENT '成會土地面積比例分母',
    quorum_land_area DECIMAL(12,2) DEFAULT 0.00 COMMENT '成會土地面積(平方公尺)',
    quorum_building_area_numerator INT(11) UNSIGNED DEFAULT 0 COMMENT '成會建物面積比例分子',
    quorum_building_area_denominator INT(11) UNSIGNED DEFAULT 1 COMMENT '成會建物面積比例分母',
    quorum_building_area DECIMAL(12,2) DEFAULT 0.00 COMMENT '成會建物面積(平方公尺)',
    quorum_member_numerator INT(11) UNSIGNED DEFAULT 0 COMMENT '成會人數比例分子',
    quorum_member_denominator INT(11) UNSIGNED DEFAULT 1 COMMENT '成會人數比例分母',
    quorum_member_count INT(11) UNSIGNED DEFAULT 0 COMMENT '成會人數',

    -- 會議狀態
    meeting_status ENUM('draft', 'scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'draft' COMMENT '會議狀態',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,

    FOREIGN KEY (urban_renewal_id) REFERENCES urban_renewals(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_urban_renewal_id (urban_renewal_id),
    INDEX idx_meeting_date (meeting_date),
    INDEX idx_meeting_status (meeting_status),
    INDEX idx_meeting_date_status (meeting_date, meeting_status)
);
```

### 2. meeting_notices (會議通知單)
```sql
CREATE TABLE meeting_notices (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT(11) UNSIGNED NOT NULL COMMENT '會議ID',

    -- 發文資訊
    document_number_prefix VARCHAR(50) NULL COMMENT '發文字號前綴',
    document_number_middle VARCHAR(50) NULL COMMENT '字第',
    document_number_suffix VARCHAR(50) NULL COMMENT '號碼',
    document_number_postfix VARCHAR(50) NULL COMMENT '號後綴',

    -- 聯絡人資訊
    chairman_name VARCHAR(100) NULL COMMENT '理事長姓名',
    contact_person VARCHAR(100) NULL COMMENT '聯絡人姓名',
    contact_phone VARCHAR(20) NULL COMMENT '聯絡人電話',

    -- 附件
    attachment_path TEXT NULL COMMENT '附件路徑',
    attachment_filename VARCHAR(255) NULL COMMENT '附件檔名',

    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,

    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_meeting_id (meeting_id)
);
```

### 3. meeting_notice_descriptions (會議通知說明事項)
```sql
CREATE TABLE meeting_notice_descriptions (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    meeting_notice_id INT(11) UNSIGNED NOT NULL COMMENT '會議通知ID',
    sequence_number INT(11) UNSIGNED NOT NULL COMMENT '序號',
    description_text TEXT NOT NULL COMMENT '說明內容',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    FOREIGN KEY (meeting_notice_id) REFERENCES meeting_notices(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_meeting_notice_id (meeting_notice_id),
    INDEX idx_sequence (meeting_notice_id, sequence_number)
);
```

### 4. meeting_attendances (會議出席記錄)
```sql
CREATE TABLE meeting_attendances (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT(11) UNSIGNED NOT NULL COMMENT '會議ID',
    property_owner_id INT(11) UNSIGNED NOT NULL COMMENT '所有權人ID',
    attendance_type ENUM('present', 'proxy', 'absent') NOT NULL DEFAULT 'absent' COMMENT '出席類型',
    proxy_person VARCHAR(100) NULL COMMENT '代理人姓名',
    check_in_time DATETIME NULL COMMENT '簽到時間',
    is_calculated TINYINT(1) DEFAULT 1 COMMENT '是否納入計算',
    notes TEXT NULL COMMENT '備註',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (property_owner_id) REFERENCES property_owners(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_meeting_owner (meeting_id, property_owner_id),
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_property_owner_id (property_owner_id),
    INDEX idx_attendance_type (attendance_type),
    INDEX idx_check_in_time (check_in_time)
);
```

### 5. meeting_observers (會議列席者)
```sql
CREATE TABLE meeting_observers (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT(11) UNSIGNED NOT NULL COMMENT '會議ID',
    observer_name VARCHAR(100) NOT NULL COMMENT '列席者姓名',
    observer_title VARCHAR(100) NULL COMMENT '職稱',
    observer_organization VARCHAR(200) NULL COMMENT '所屬機關/單位',
    contact_phone VARCHAR(20) NULL COMMENT '聯絡電話',
    notes TEXT NULL COMMENT '備註',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_observer_name (observer_name)
);
```

### 6. voting_topics (投票議題)
```sql
CREATE TABLE voting_topics (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT(11) UNSIGNED NOT NULL COMMENT '會議ID',
    topic_number VARCHAR(20) NOT NULL COMMENT '議題編號',
    topic_title VARCHAR(500) NOT NULL COMMENT '議題標題',
    topic_description TEXT NULL COMMENT '議題描述',
    voting_method ENUM('simple_majority', 'absolute_majority', 'two_thirds_majority', 'unanimous')
        NOT NULL DEFAULT 'simple_majority' COMMENT '投票方式',

    -- 投票統計
    total_votes INT(11) UNSIGNED DEFAULT 0 COMMENT '總票數',
    agree_votes INT(11) UNSIGNED DEFAULT 0 COMMENT '同意票數',
    disagree_votes INT(11) UNSIGNED DEFAULT 0 COMMENT '不同意票數',
    abstain_votes INT(11) UNSIGNED DEFAULT 0 COMMENT '棄權票數',

    -- 面積權重投票統計
    total_land_area DECIMAL(12,2) DEFAULT 0.00 COMMENT '總土地面積',
    agree_land_area DECIMAL(12,2) DEFAULT 0.00 COMMENT '同意土地面積',
    disagree_land_area DECIMAL(12,2) DEFAULT 0.00 COMMENT '不同意土地面積',
    abstain_land_area DECIMAL(12,2) DEFAULT 0.00 COMMENT '棄權土地面積',

    total_building_area DECIMAL(12,2) DEFAULT 0.00 COMMENT '總建物面積',
    agree_building_area DECIMAL(12,2) DEFAULT 0.00 COMMENT '同意建物面積',
    disagree_building_area DECIMAL(12,2) DEFAULT 0.00 COMMENT '不同意建物面積',
    abstain_building_area DECIMAL(12,2) DEFAULT 0.00 COMMENT '棄權建物面積',

    voting_result ENUM('pending', 'passed', 'failed', 'withdrawn') DEFAULT 'pending' COMMENT '投票結果',
    voting_status ENUM('draft', 'active', 'closed') DEFAULT 'draft' COMMENT '議題狀態',

    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,

    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_topic_number (topic_number),
    INDEX idx_voting_status (voting_status),
    INDEX idx_voting_result (voting_result)
);
```

### 7. voting_records (個人投票記錄)
```sql
CREATE TABLE voting_records (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    voting_topic_id INT(11) UNSIGNED NOT NULL COMMENT '投票議題ID',
    property_owner_id INT(11) UNSIGNED NOT NULL COMMENT '所有權人ID',
    vote_choice ENUM('agree', 'disagree', 'abstain') NOT NULL COMMENT '投票選擇',
    vote_time DATETIME NULL COMMENT '投票時間',
    voter_name VARCHAR(100) NULL COMMENT '投票人姓名(快照)',

    -- 投票權重(快照資料)
    land_area_weight DECIMAL(12,2) DEFAULT 0.00 COMMENT '土地面積權重',
    building_area_weight DECIMAL(12,2) DEFAULT 0.00 COMMENT '建物面積權重',

    notes TEXT NULL COMMENT '備註',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    FOREIGN KEY (voting_topic_id) REFERENCES voting_topics(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (property_owner_id) REFERENCES property_owners(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_topic_owner (voting_topic_id, property_owner_id),
    INDEX idx_voting_topic_id (voting_topic_id),
    INDEX idx_property_owner_id (property_owner_id),
    INDEX idx_vote_choice (vote_choice),
    INDEX idx_vote_time (vote_time)
);
```

## 支援資料表

### 8. meeting_documents (會議文件管理)
```sql
CREATE TABLE meeting_documents (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT(11) UNSIGNED NOT NULL COMMENT '會議ID',
    document_type ENUM('agenda', 'minutes', 'attendance', 'notice', 'other') NOT NULL COMMENT '文件類型',
    document_name VARCHAR(255) NOT NULL COMMENT '文件名稱',
    file_path TEXT NOT NULL COMMENT '檔案路徑',
    file_name VARCHAR(255) NOT NULL COMMENT '原始檔名',
    file_size INT(11) UNSIGNED NOT NULL COMMENT '檔案大小(bytes)',
    mime_type VARCHAR(100) NULL COMMENT 'MIME類型',
    uploaded_by INT(11) UNSIGNED NULL COMMENT '上傳者ID(未來擴展)',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,

    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_document_type (document_type)
);
```

### 9. meeting_logs (會議操作日誌)
```sql
CREATE TABLE meeting_logs (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT(11) UNSIGNED NOT NULL COMMENT '會議ID',
    action_type ENUM('create', 'update', 'delete', 'check_in', 'vote', 'export') NOT NULL COMMENT '操作類型',
    action_description TEXT NOT NULL COMMENT '操作描述',
    related_table VARCHAR(50) NULL COMMENT '相關資料表',
    related_id INT(11) UNSIGNED NULL COMMENT '相關記錄ID',
    operator_info JSON NULL COMMENT '操作者資訊',
    ip_address VARCHAR(45) NULL COMMENT 'IP位址',
    user_agent TEXT NULL COMMENT '瀏覽器資訊',
    created_at DATETIME NULL,

    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_meeting_id (meeting_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at),
    INDEX idx_related_table_id (related_table, related_id)
);
```

## 資料關聯架構

```
urban_renewals (更新會)
    ├── meetings (會議)
    │   ├── meeting_notices (會議通知)
    │   │   └── meeting_notice_descriptions (通知說明)
    │   ├── meeting_attendances (出席記錄)
    │   ├── meeting_observers (列席者)
    │   ├── voting_topics (投票議題)
    │   │   └── voting_records (投票記錄)
    │   ├── meeting_documents (會議文件)
    │   └── meeting_logs (操作日誌)
    └── property_owners (所有權人)
        ├── meeting_attendances (出席記錄)
        └── voting_records (投票記錄)
```

## 查詢效能優化建議

### 複合索引設計
```sql
-- 會議管理頁面主要查詢優化
ALTER TABLE meetings ADD INDEX idx_renewal_date_status (urban_renewal_id, meeting_date, meeting_status);

-- 出席統計查詢優化
ALTER TABLE meeting_attendances ADD INDEX idx_meeting_attendance_calc (meeting_id, attendance_type, is_calculated);

-- 投票統計查詢優化
ALTER TABLE voting_records ADD INDEX idx_topic_vote_time (voting_topic_id, vote_choice, vote_time);

-- 文件管理查詢優化
ALTER TABLE meeting_documents ADD INDEX idx_meeting_type_deleted (meeting_id, document_type, deleted_at);
```

### 統計資料維護建議
```sql
-- 建議使用觸發器自動更新統計資料
-- 出席人數統計觸發器
DELIMITER $$
CREATE TRIGGER update_meeting_attendance_count
AFTER INSERT ON meeting_attendances
FOR EACH ROW
BEGIN
    UPDATE meetings
    SET attendee_count = (
        SELECT COUNT(*) FROM meeting_attendances
        WHERE meeting_id = NEW.meeting_id AND attendance_type IN ('present', 'proxy')
    ),
    calculated_total_count = (
        SELECT COUNT(*) FROM meeting_attendances
        WHERE meeting_id = NEW.meeting_id AND is_calculated = 1
    )
    WHERE id = NEW.meeting_id;
END$$
DELIMITER ;

-- 投票統計觸發器
DELIMITER $$
CREATE TRIGGER update_voting_statistics
AFTER INSERT ON voting_records
FOR EACH ROW
BEGIN
    UPDATE voting_topics vt
    SET
        total_votes = (SELECT COUNT(*) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id),
        agree_votes = (SELECT COUNT(*) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'agree'),
        disagree_votes = (SELECT COUNT(*) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'disagree'),
        abstain_votes = (SELECT COUNT(*) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'abstain'),
        agree_land_area = (SELECT COALESCE(SUM(land_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'agree'),
        disagree_land_area = (SELECT COALESCE(SUM(land_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'disagree'),
        abstain_land_area = (SELECT COALESCE(SUM(land_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'abstain'),
        agree_building_area = (SELECT COALESCE(SUM(building_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'agree'),
        disagree_building_area = (SELECT COALESCE(SUM(building_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'disagree'),
        abstain_building_area = (SELECT COALESCE(SUM(building_area_weight), 0) FROM voting_records WHERE voting_topic_id = NEW.voting_topic_id AND vote_choice = 'abstain')
    WHERE id = NEW.voting_topic_id;
END$$
DELIMITER ;
```

## 資料完整性約束

### 業務邏輯約束
```sql
-- 成會比例約束
ALTER TABLE meetings ADD CONSTRAINT chk_quorum_denominators
CHECK (quorum_land_area_denominator > 0 AND quorum_building_area_denominator > 0 AND quorum_member_denominator > 0);

-- 投票統計約束
ALTER TABLE voting_topics ADD CONSTRAINT chk_vote_counts
CHECK (total_votes >= (agree_votes + disagree_votes + abstain_votes));

-- 面積權重約束
ALTER TABLE voting_records ADD CONSTRAINT chk_area_weights
CHECK (land_area_weight >= 0 AND building_area_weight >= 0);
```

## 常用查詢範例

### 會議列表查詢（支援 Point 58 需求）
```sql
SELECT
    m.id,
    m.meeting_name,
    ur.name as renewal_group_name,
    CONCAT(DATE_FORMAT(m.meeting_date, '%Y年%m月%d日'), ' ', TIME_FORMAT(m.meeting_time, '%p%h:%i:%s')) as meeting_datetime,
    m.attendee_count,
    m.calculated_total_count,
    m.observer_count,
    COUNT(vt.id) as voting_topic_count
FROM meetings m
JOIN urban_renewals ur ON m.urban_renewal_id = ur.id
LEFT JOIN voting_topics vt ON m.id = vt.meeting_id AND vt.deleted_at IS NULL
WHERE m.deleted_at IS NULL
GROUP BY m.id
ORDER BY m.meeting_date DESC, m.meeting_time DESC;
```

### 會議出席統計查詢
```sql
SELECT
    m.meeting_name,
    COUNT(ma.id) as total_invited,
    SUM(CASE WHEN ma.attendance_type IN ('present', 'proxy') THEN 1 ELSE 0 END) as attended,
    SUM(CASE WHEN ma.attendance_type = 'absent' THEN 1 ELSE 0 END) as absent,
    SUM(CASE WHEN ma.is_calculated = 1 THEN 1 ELSE 0 END) as calculated_count,
    -- 出席率計算
    ROUND(SUM(CASE WHEN ma.attendance_type IN ('present', 'proxy') THEN 1 ELSE 0 END) * 100.0 / COUNT(ma.id), 2) as attendance_rate
FROM meetings m
LEFT JOIN meeting_attendances ma ON m.id = ma.meeting_id
WHERE m.id = ?
GROUP BY m.id;
```

### 投票議題統計查詢
```sql
SELECT
    vt.topic_number,
    vt.topic_title,
    vt.total_votes,
    vt.agree_votes,
    vt.disagree_votes,
    vt.abstain_votes,
    -- 票數比例
    ROUND(vt.agree_votes * 100.0 / NULLIF(vt.total_votes, 0), 2) as agree_percentage,
    -- 面積權重統計
    vt.agree_land_area,
    vt.total_land_area,
    ROUND(vt.agree_land_area * 100.0 / NULLIF(vt.total_land_area, 0), 2) as land_area_percentage,
    vt.voting_result
FROM voting_topics vt
WHERE vt.meeting_id = ? AND vt.deleted_at IS NULL
ORDER BY vt.topic_number;
```

## 安全性建議

### 1. 敏感資料保護
- 個人身分證字號應加密存儲
- 投票記錄應有完整的審計追蹤
- 會議文件應有存取權限控制

### 2. 資料完整性保護
- 關鍵統計資料應有快照機制
- 投票結果確定後應鎖定，防止篡改
- 重要操作應有操作日誌記錄

### 3. 效能優化
- 大型會議的出席和投票統計應考慮快取機制
- 歷史會議資料可考慮分表存儲
- 文件上傳應有大小和類型限制

這個設計完整支援 Point 58 描述的所有會議管理功能，同時保持與現有系統的良好整合性。