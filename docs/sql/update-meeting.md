# 都市更新系統完整資料庫結構設計

## 概述
根據Point 56的需求分析，都市更新系統需要支援所有權人管理，包括土地和建物的持有關係。本文件基於現有的urban_renewals和land_plots表，設計完整的資料庫結構。

## 現有資料表分析

### 1. urban_renewals (更新會基本資料)
```sql
CREATE TABLE urban_renewals (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL COMMENT '更新會名稱',
    area DECIMAL(10,2) NOT NULL COMMENT '土地面積(平方公尺)',
    member_count INT(11) UNSIGNED NOT NULL COMMENT '所有權人數',
    chairman_name VARCHAR(100) NOT NULL COMMENT '理事長姓名',
    chairman_phone VARCHAR(20) NOT NULL COMMENT '理事長電話',
    address TEXT NULL COMMENT '設立地址',
    representative VARCHAR(100) NULL COMMENT '負責人',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL
);
```

### 2. land_plots (地號資料)
```sql
CREATE TABLE land_plots (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    urban_renewal_id INT(11) UNSIGNED NOT NULL COMMENT '更新會ID',
    county VARCHAR(10) NOT NULL COMMENT '縣市代碼',
    district VARCHAR(10) NOT NULL COMMENT '行政區代碼',
    section VARCHAR(10) NOT NULL COMMENT '段小段代碼',
    land_number_main VARCHAR(10) NOT NULL COMMENT '地號母號',
    land_number_sub VARCHAR(10) NULL COMMENT '地號子號',
    land_area DECIMAL(12,2) NULL COMMENT '土地面積(平方公尺)',
    is_representative TINYINT(1) DEFAULT 0 NOT NULL COMMENT '是否為代表號',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,

    FOREIGN KEY (urban_renewal_id) REFERENCES urban_renewals(id) ON DELETE CASCADE ON UPDATE CASCADE
);
```

## 新增所有權人管理所需資料表

### 3. property_owners (所有權人基本資料)
```sql
CREATE TABLE property_owners (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    urban_renewal_id INT(11) UNSIGNED NOT NULL COMMENT '所屬更新會ID',
    owner_code VARCHAR(20) NOT NULL COMMENT '所有權人編號(自動產生)',
    name VARCHAR(100) NOT NULL COMMENT '所有權人名稱',
    id_number VARCHAR(20) NULL COMMENT '身分證字號',
    phone1 VARCHAR(20) NULL COMMENT '電話1',
    phone2 VARCHAR(20) NULL COMMENT '電話2',
    contact_address TEXT NULL COMMENT '聯絡地址',
    household_address TEXT NULL COMMENT '戶籍地址',
    exclusion_type ENUM('法院囑託查封', '假扣押', '假處分', '破產登記', '未經繼承') NULL COMMENT '排除計算類型',
    notes TEXT NULL COMMENT '備註',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,

    UNIQUE KEY unique_owner_code (owner_code),
    FOREIGN KEY (urban_renewal_id) REFERENCES urban_renewals(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_urban_renewal_id (urban_renewal_id),
    INDEX idx_owner_code (owner_code),
    INDEX idx_name (name)
);
```

### 4. buildings (建物基本資料)
```sql
CREATE TABLE buildings (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    urban_renewal_id INT(11) UNSIGNED NOT NULL COMMENT '所屬更新會ID',
    county VARCHAR(10) NOT NULL COMMENT '縣市',
    district VARCHAR(10) NOT NULL COMMENT '行政區',
    section VARCHAR(10) NOT NULL COMMENT '段小段',
    building_number_main VARCHAR(10) NOT NULL COMMENT '建號母號',
    building_number_sub VARCHAR(10) DEFAULT '000' COMMENT '建號子號',
    building_area DECIMAL(12,2) NULL COMMENT '建物總面積(平方公尺)',
    building_address VARCHAR(255) NULL COMMENT '建物門牌',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,

    FOREIGN KEY (urban_renewal_id) REFERENCES urban_renewals(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_urban_renewal_id (urban_renewal_id),
    INDEX idx_building_number (building_number_main, building_number_sub),
    INDEX idx_location (county, district, section)
);
```

### 5. building_land_relations (建物與地號關聯)
```sql
CREATE TABLE building_land_relations (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    building_id INT(11) UNSIGNED NOT NULL COMMENT '建物ID',
    land_plot_id INT(11) UNSIGNED NOT NULL COMMENT '地號ID',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    FOREIGN KEY (building_id) REFERENCES buildings(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (land_plot_id) REFERENCES land_plots(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_building_land (building_id, land_plot_id),
    INDEX idx_building_id (building_id),
    INDEX idx_land_plot_id (land_plot_id)
);
```

### 6. owner_land_ownership (所有權人土地持有關係)
```sql
CREATE TABLE owner_land_ownership (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    property_owner_id INT(11) UNSIGNED NOT NULL COMMENT '所有權人ID',
    land_plot_id INT(11) UNSIGNED NOT NULL COMMENT '地號ID',
    ownership_numerator INT(11) UNSIGNED NOT NULL COMMENT '持有比例分子',
    ownership_denominator INT(11) UNSIGNED NOT NULL COMMENT '持有比例分母',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,

    FOREIGN KEY (property_owner_id) REFERENCES property_owners(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (land_plot_id) REFERENCES land_plots(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_owner_land (property_owner_id, land_plot_id),
    INDEX idx_property_owner_id (property_owner_id),
    INDEX idx_land_plot_id (land_plot_id)
);
```

### 7. owner_building_ownership (所有權人建物持有關係)
```sql
CREATE TABLE owner_building_ownership (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    property_owner_id INT(11) UNSIGNED NOT NULL COMMENT '所有權人ID',
    building_id INT(11) UNSIGNED NOT NULL COMMENT '建物ID',
    ownership_numerator INT(11) UNSIGNED NOT NULL COMMENT '持有比例分子',
    ownership_denominator INT(11) UNSIGNED NOT NULL COMMENT '持有比例分母',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,

    FOREIGN KEY (property_owner_id) REFERENCES property_owners(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (building_id) REFERENCES buildings(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_owner_building (property_owner_id, building_id),
    INDEX idx_property_owner_id (property_owner_id),
    INDEX idx_building_id (building_id)
);
```

## 資料關聯圖

```
urban_renewals (更新會)
    ├── property_owners (所有權人)
    │   ├── owner_land_ownership (土地持有關係)
    │   └── owner_building_ownership (建物持有關係)
    ├── land_plots (地號)
    │   ├── building_land_relations (建物地號關聯)
    │   └── owner_land_ownership (土地持有關係)
    └── buildings (建物)
        ├── building_land_relations (建物地號關聯)
        └── owner_building_ownership (建物持有關係)
```

## 核心功能支援

### 1. 所有權人編號自動產生
- 格式建議：{更新會ID}-{流水號4位數}
- 例如：1-0001, 1-0002

### 2. 地號與建號格式
- 地號：{縣市}-{行政區}-{段小段}-{母號}-{子號}
- 建號：{縣市}-{行政區}-{段小段}-{母號}-{子號}

### 3. 持有比例計算
- 支援分數表示：分子/分母
- 可計算實際持有面積：土地面積 × (分子/分母)

### 4. 排除計算功能
- 支援五種排除類型
- 影響總面積和人數統計

## 查詢範例

### 查詢某更新會所有權人及其持有總面積
```sql
SELECT
    po.owner_code,
    po.name,
    COALESCE(SUM(lp.land_area * (olo.ownership_numerator / olo.ownership_denominator)), 0) as total_land_area,
    COALESCE(SUM(b.building_area * (obo.ownership_numerator / obo.ownership_denominator)), 0) as total_building_area
FROM property_owners po
LEFT JOIN owner_land_ownership olo ON po.id = olo.property_owner_id AND olo.deleted_at IS NULL
LEFT JOIN land_plots lp ON olo.land_plot_id = lp.id AND lp.deleted_at IS NULL
LEFT JOIN owner_building_ownership obo ON po.id = obo.property_owner_id AND obo.deleted_at IS NULL
LEFT JOIN buildings b ON obo.building_id = b.id AND b.deleted_at IS NULL
WHERE po.urban_renewal_id = ? AND po.deleted_at IS NULL
GROUP BY po.id, po.owner_code, po.name;
```

### 查詢某地號的所有所有權人
```sql
SELECT
    po.owner_code,
    po.name,
    olo.ownership_numerator,
    olo.ownership_denominator,
    (olo.ownership_numerator / olo.ownership_denominator) as ownership_ratio,
    lp.land_area * (olo.ownership_numerator / olo.ownership_denominator) as owned_area
FROM owner_land_ownership olo
JOIN property_owners po ON olo.property_owner_id = po.id
JOIN land_plots lp ON olo.land_plot_id = lp.id
WHERE lp.id = ?
AND olo.deleted_at IS NULL
AND po.deleted_at IS NULL
AND lp.deleted_at IS NULL;
```

## 建議索引優化

### 複合索引
```sql
-- 所有權人查詢優化
ALTER TABLE property_owners ADD INDEX idx_renewal_name (urban_renewal_id, name);

-- 持有關係查詢優化
ALTER TABLE owner_land_ownership ADD INDEX idx_owner_land_active (property_owner_id, land_plot_id, deleted_at);
ALTER TABLE owner_building_ownership ADD INDEX idx_owner_building_active (property_owner_id, building_id, deleted_at);

-- 地理位置查詢優化
ALTER TABLE land_plots ADD INDEX idx_location_full (county, district, section, land_number_main, land_number_sub);
ALTER TABLE buildings ADD INDEX idx_location_full (county, district, section, building_number_main, building_number_sub);
```

## 資料完整性約束

### 1. 觸發器建議
- 所有權人編號自動生成
- 更新會成員數自動更新
- 軟刪除級聯處理

### 2. 檢查約束
- 持有比例分子分母必須大於0
- 面積必須大於0
- 身分證字號格式驗證

這個資料庫結構設計支援Point 56描述的所有功能需求，包括所有權人管理、土地建物關聯、持有比例計算等核心功能。

## 後端架構師評估報告 (Point 59)

### 執行摘要

基於Point 59需求，本報告對都市更新會管理系統進行全面的後端架構評估。經過深入分析現有的資料庫設計文檔、CodeIgniter 4框架實作、以及系統整合度，發現系統整體架構良好但存在關鍵功能缺口，需要立即實施會議管理模組以完善整體業務流程。

### 1. 現狀分析

#### 1.1 已實作架構評估 ✅

**現有資料表結構 (已實作)**:
- `urban_renewals`: 更新會基本資料 - **設計完善**
- `land_plots`: 地號資料與關聯 - **設計完善**

**CodeIgniter 4框架實作品質**:
- 標準MVC架構: `Model-Controller-View` 分離良好
- 資料驗證規則: 完整的輸入驗證與錯誤訊息
- 軟刪除支援: 符合審計要求
- RESTful API設計: 標準化API端點
- 外鍵約束: 資料完整性保護

#### 1.2 設計但未實作架構 ⚠️

**設計完成但待實作的資料表**:
```sql
- property_owners          (所有權人基本資料)
- buildings               (建物基本資料)
- building_land_relations (建物與地號關聯)
- owner_land_ownership    (所有權人土地持有關係)
- owner_building_ownership(所有權人建物持有關係)
```

#### 1.3 重大功能缺口 🚨

**會議管理系統完全缺失**:
- 無會議管理相關資料表設計
- 無會議類型管理 (成立大會、會員大會等)
- 無出席管理與議決記錄
- 無會議通知與文件管理
- 無決議追蹤與執行狀態

### 2. 關鍵問題識別

#### 2.1 關鍵級問題 (Critical - 立即處理)

**問題1: 會議管理系統完全缺失**
- **影響**: 核心業務流程無法數位化
- **風險**: 法規合規性問題、人工作業效率低落
- **優先級**: P0 - 立即實作

**問題2: 所有權人管理系統未實作**
- **影響**: 無法進行所有權關係管理
- **風險**: 會議出席率計算、決議效力確認困難
- **優先級**: P0 - 會議系統前置需求

#### 2.2 高優先級問題 (High - 2週內處理)

**問題3: 資料一致性檢查不足**
```sql
-- 缺失的約束檢查
- 持有比例總和 ≤ 1.0 的檢查
- 更新會成員數自動更新機制
- 會議出席人數與資格驗證
```

**問題4: 性能優化策略不完整**
- 缺乏複合查詢的專用索引
- 沒有查詢緩存策略
- 大數據量處理優化不足

#### 2.3 中等優先級問題 (Medium - 1個月內處理)

**問題5: API安全性加強**
- JWT Token管理策略
- API Rate Limiting
- 敏感資料加密處理

### 3. 會議管理系統資料表設計建議

基於法規需求與業務流程，建議新增以下核心資料表：

#### 3.1 會議管理核心表

```sql
-- 會議基本資料
CREATE TABLE meetings (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    urban_renewal_id INT(11) UNSIGNED NOT NULL,
    meeting_type ENUM('成立大會', '會員大會', '理監事會', '臨時會') NOT NULL,
    meeting_title VARCHAR(255) NOT NULL COMMENT '會議標題',
    meeting_date DATETIME NOT NULL COMMENT '會議日期時間',
    meeting_location TEXT NULL COMMENT '會議地點',
    meeting_agenda TEXT NULL COMMENT '會議議程',
    status ENUM('籌備中', '進行中', '已結束', '已取消') DEFAULT '籌備中',
    total_eligible_members INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '應出席人數',
    actual_attendees INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '實際出席人數',
    meeting_minutes TEXT NULL COMMENT '會議紀錄',
    chairperson VARCHAR(100) NULL COMMENT '主席姓名',
    recorder VARCHAR(100) NULL COMMENT '記錄人員',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,

    FOREIGN KEY (urban_renewal_id) REFERENCES urban_renewals(id) ON DELETE CASCADE,
    INDEX idx_urban_renewal_meeting (urban_renewal_id, meeting_date),
    INDEX idx_meeting_status (status),
    INDEX idx_meeting_type (meeting_type)
);

-- 會議出席記錄
CREATE TABLE meeting_attendance (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT(11) UNSIGNED NOT NULL,
    property_owner_id INT(11) UNSIGNED NOT NULL,
    attendance_type ENUM('本人出席', '委託出席', '未出席') NOT NULL,
    proxy_owner_id INT(11) UNSIGNED NULL COMMENT '受委託人ID',
    attendance_time DATETIME NULL COMMENT '簽到時間',
    voting_weight DECIMAL(10,6) NOT NULL DEFAULT 0.000000 COMMENT '表決權重',
    notes TEXT NULL COMMENT '備註',
    created_at DATETIME NULL,

    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (property_owner_id) REFERENCES property_owners(id) ON DELETE CASCADE,
    FOREIGN KEY (proxy_owner_id) REFERENCES property_owners(id) ON DELETE SET NULL,
    UNIQUE KEY unique_meeting_owner (meeting_id, property_owner_id),
    INDEX idx_meeting_attendance (meeting_id, attendance_type),
    INDEX idx_voting_weight (voting_weight)
);

-- 議案管理
CREATE TABLE meeting_resolutions (
    id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT(11) UNSIGNED NOT NULL,
    resolution_number VARCHAR(20) NOT NULL COMMENT '議案編號',
    resolution_title VARCHAR(255) NOT NULL COMMENT '議案標題',
    resolution_content TEXT NOT NULL COMMENT '議案內容',
    resolution_type ENUM('一般決議', '特別決議', '報告事項') NOT NULL,
    required_approval_ratio DECIMAL(3,2) NOT NULL COMMENT '需要通過比例',
    votes_for INT(11) UNSIGNED DEFAULT 0 COMMENT '贊成票數',
    votes_against INT(11) UNSIGNED DEFAULT 0 COMMENT '反對票數',
    votes_abstain INT(11) UNSIGNED DEFAULT 0 COMMENT '棄權票數',
    voting_weight_for DECIMAL(10,6) DEFAULT 0.000000 COMMENT '贊成票權重',
    voting_weight_against DECIMAL(10,6) DEFAULT 0.000000 COMMENT '反對票權重',
    voting_weight_abstain DECIMAL(10,6) DEFAULT 0.000000 COMMENT '棄權票權重',
    resolution_status ENUM('待表決', '通過', '否決', '撤案') DEFAULT '待表決',
    voting_method ENUM('舉手表決', '無記名投票', '記名投票') DEFAULT '舉手表決',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    INDEX idx_meeting_resolution (meeting_id, resolution_status),
    INDEX idx_resolution_type (resolution_type)
);
```

#### 3.2 權重計算與驗證

```sql
-- 表決權重計算函數 (MySQL)
DELIMITER $$
CREATE FUNCTION calculate_voting_weight(owner_id INT, urban_renewal_id INT)
RETURNS DECIMAL(10,6)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE total_ownership DECIMAL(10,6) DEFAULT 0.000000;
    DECLARE total_area DECIMAL(12,2) DEFAULT 0.00;

    -- 計算該所有權人的總持有面積
    SELECT COALESCE(SUM(
        lp.land_area * (olo.ownership_numerator / olo.ownership_denominator) +
        COALESCE(b.building_area * (obo.ownership_numerator / obo.ownership_denominator), 0)
    ), 0) INTO total_ownership
    FROM property_owners po
    LEFT JOIN owner_land_ownership olo ON po.id = olo.property_owner_id
    LEFT JOIN land_plots lp ON olo.land_plot_id = lp.id
    LEFT JOIN owner_building_ownership obo ON po.id = obo.property_owner_id
    LEFT JOIN buildings b ON obo.building_id = b.id
    WHERE po.id = owner_id AND po.urban_renewal_id = urban_renewal_id
    AND po.deleted_at IS NULL AND lp.deleted_at IS NULL AND b.deleted_at IS NULL;

    -- 計算更新會總面積
    SELECT area INTO total_area FROM urban_renewals WHERE id = urban_renewal_id;

    -- 返回表決權重比例
    RETURN CASE
        WHEN total_area > 0 THEN total_ownership / total_area
        ELSE 0.000000
    END;
END$$
DELIMITER ;
```

### 4. 性能優化建議

#### 4.1 立即實施的索引優化

```sql
-- 會議查詢優化索引
ALTER TABLE meetings ADD INDEX idx_meeting_search (
    urban_renewal_id, meeting_type, status, meeting_date
);

-- 出席統計優化索引
ALTER TABLE meeting_attendance ADD INDEX idx_attendance_stats (
    meeting_id, attendance_type, voting_weight
);

-- 複合查詢優化
ALTER TABLE property_owners ADD INDEX idx_owner_search (
    urban_renewal_id, name, exclusion_type, deleted_at
);

-- 權重計算優化索引
ALTER TABLE owner_land_ownership ADD INDEX idx_ownership_calc (
    property_owner_id, ownership_numerator, ownership_denominator, deleted_at
);
```

#### 4.2 查詢緩存策略

```php
// Redis緩存層建議 (CodeIgniter 4)
class MeetingService
{
    private $cache;
    private $cacheTTL = 3600; // 1小時

    public function getVotingWeights($meetingId): array
    {
        $cacheKey = "meeting_voting_weights_{$meetingId}";

        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $weights = $this->calculateVotingWeights($meetingId);
        $this->cache->save($cacheKey, $weights, $this->cacheTTL);

        return $weights;
    }
}
```

### 5. 安全性強化建議

#### 5.1 API安全加強

```php
// JWT Token中介層
class JWTAuthenticationMiddleware
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeader('Authorization');
        if (!$header || !$this->validateJWT($header)) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Unauthorized']);
        }
    }
}

// Rate Limiting實作
class RateLimitMiddleware
{
    public function before(RequestInterface $request)
    {
        $clientIP = $request->getIPAddress();
        $key = "rate_limit_{$clientIP}";

        $requests = cache()->get($key) ?? 0;
        if ($requests >= 100) { // 每分鐘100次請求限制
            return service('response')->setStatusCode(429);
        }

        cache()->save($key, $requests + 1, 60);
    }
}
```

#### 5.2 敏感資料保護

```php
// 身分證字號加密處理
class EncryptionService
{
    public function encryptIdNumber($idNumber): string
    {
        return encrypt($idNumber, 'owner_id_key');
    }

    public function hashSensitiveData($data): string
    {
        return password_hash($data . env('APP_SALT'), PASSWORD_ARGON2ID);
    }
}
```

### 6. 實施時程與優先級建議

#### 階段一: 緊急實施 (1-2週) - P0

1. **會議管理資料表建立**
   - 實作meetings, meeting_attendance, meeting_resolutions表
   - 建立基本的CRUD API
   - 實作表決權重計算邏輯

2. **所有權人管理系統實作**
   - 完成property_owners及相關持有關係表
   - 建立所有權人CRUD功能
   - 整合現有urban_renewals系統

#### 階段二: 核心功能完善 (3-4週) - P0-P1

3. **會議業務邏輯實作**
   - 會議通知發送功能
   - 出席簽到與委託處理
   - 議案表決與統計功能

4. **資料完整性加強**
   - 實作觸發器確保資料一致性
   - 建立持有比例驗證機制
   - 完善級聯更新邏輯

#### 階段三: 性能與安全優化 (5-6週) - P1-P2

5. **性能優化實施**
   - 部署建議的索引策略
   - 實作查詢緩存層
   - 進行負載測試與調優

6. **安全性加固**
   - JWT Authentication實作
   - API Rate Limiting部署
   - 敏感資料加密處理

### 7. 風險評估與緩解策略

#### 7.1 高風險項目

**風險1: 資料遷移複雜性**
- **緩解策略**: 採用漸進式遷移，保留現有資料完整性
- **回滾計劃**: 完整的資料庫備份與還原程序

**風險2: 性能影響**
- **緩解策略**: 分階段部署，監控系統性能指標
- **監控指標**: API響應時間、資料庫查詢效率、記憶體使用率

#### 7.2 合規性風險

**法規合規檢查清單**:
- ✅ 都市更新條例第15條: 成立大會相關規定
- ✅ 都市更新條例第18條: 會員大會決議程序
- ✅ 個人資料保護法: 敏感資料處理規範
- ⚠️ 需確認: 會議紀錄保存年限與格式要求

### 8. 監控與維護建議

#### 8.1 關鍵性能指標 (KPI)

```sql
-- 系統健康監控查詢
-- 1. 會議系統使用率
SELECT
    DATE(meeting_date) as meeting_day,
    COUNT(*) as meeting_count,
    AVG(actual_attendees/total_eligible_members) as avg_attendance_rate
FROM meetings
WHERE meeting_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(meeting_date);

-- 2. API響應時間監控
-- (需要在應用層實作logging)

-- 3. 資料一致性檢查
SELECT
    ur.name,
    ur.member_count as recorded_count,
    COUNT(DISTINCT po.id) as actual_count
FROM urban_renewals ur
LEFT JOIN property_owners po ON ur.id = po.urban_renewal_id
WHERE po.deleted_at IS NULL
GROUP BY ur.id
HAVING recorded_count != actual_count;
```

#### 8.2 定期維護任務

```bash
#!/bin/bash
# 每日維護腳本
# 1. 資料庫完整性檢查
mysql -u$DB_USER -p$DB_PASS -e "CHECK TABLE urban_renewals, land_plots, property_owners;"

# 2. 清理過期快取
redis-cli FLUSHDB

# 3. 備份重要資料
mysqldump -u$DB_USER -p$DB_PASS urban_renewal > backup_$(date +%Y%m%d).sql
```

### 9. 總結與建議

#### 9.1 整體評估結果

**現有架構評分**: **B+ (良好，但需立即完善)**

**優點**:
- CodeIgniter 4框架選擇適當，開發效率高
- 現有資料表設計規範，符合正規化原則
- API設計遵循RESTful標準
- 軟刪除機制完善，符合審計需求

**待改進項目**:
- **會議管理系統為關鍵缺口，需立即實作**
- 所有權人管理系統設計完整但未實作
- 性能優化策略需要加強
- 安全性措施需要完善

#### 9.2 立即行動建議

1. **第一優先**: 立即啟動會議管理系統開發 (預估2週完成MVP)
2. **第二優先**: 完成所有權人管理系統實作 (預估1週完成)
3. **第三優先**: 實施性能優化措施 (預估1週完成)
4. **第四優先**: 加強系統安全性 (預估1週完成)

#### 9.3 長期發展建議

- **微服務架構考慮**: 隨著功能增加，考慮將會議管理拆分為獨立服務
- **API閘道實作**: 統一API管理與監控
- **事件驅動架構**: 實作領域事件處理，提高系統解耦程度
- **自動化測試**: 建立完整的單元測試與整合測試覆蓋

**結論**: 系統整體架構設計合理，技術選型適當，但會議管理功能的缺失是當前最重要的技術債務。建議立即投入資源完成會議管理系統的實作，以確保系統能夠支援完整的都市更新業務流程。

---
*評估執行人: Backend Architecture Review Team*
*評估日期: 2025-09-13*
*下次評估建議時間: 實作完成後4週*