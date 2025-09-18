<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddComprehensiveTableComments extends Migration
{
    public function up()
    {
        // Add table-level comments for all tables
        $this->db->query("ALTER TABLE counties COMMENT = '縣市資料表 - 儲存台灣各縣市行政區劃資訊，用於地址選擇和地籍資料管理'");
        $this->db->query("ALTER TABLE districts COMMENT = '行政區資料表 - 儲存鄉鎮市區資訊，與縣市表關聯，用於完整地址管理'");
        $this->db->query("ALTER TABLE sections COMMENT = '段小段資料表 - 儲存地籍段別資訊，用於土地地號完整定位'");

        $this->db->query("ALTER TABLE urban_renewals COMMENT = '都市更新會資料表 - 儲存更新會基本資料，包含面積、成員數量、理事長資訊等核心資料'");
        $this->db->query("ALTER TABLE land_plots COMMENT = '地號資料表 - 儲存土地地號資訊，包含完整地籍資料、面積及是否為代表號等資訊'");
        $this->db->query("ALTER TABLE property_owners COMMENT = '所有權人資料表 - 儲存所有權人基本資料，包含個人資訊、聯絡方式及排除計算類型'");
        $this->db->query("ALTER TABLE buildings COMMENT = '建物資料表 - 儲存建物基本資訊，包含建號、面積、地址等資料'");

        $this->db->query("ALTER TABLE building_land_relations COMMENT = '建物地號關聯表 - 建立建物與土地地號的多對多關聯關係'");
        $this->db->query("ALTER TABLE owner_land_ownership COMMENT = '土地所有權關係表 - 記錄所有權人對土地的持有比例，支援分數表示法'");
        $this->db->query("ALTER TABLE owner_building_ownership COMMENT = '建物所有權關係表 - 記錄所有權人對建物的持有比例，支援分數表示法'");

        $this->db->query("ALTER TABLE meetings COMMENT = '會議資料表 - 儲存會議基本資料、出席統計、成會門檻設定等完整會議管理資訊'");
        $this->db->query("ALTER TABLE meeting_notices COMMENT = '會議通知資料表 - 儲存會議通知相關資料，包含發文字號、聯絡人資訊及附件等'");
        $this->db->query("ALTER TABLE meeting_notice_descriptions COMMENT = '會議通知說明表 - 儲存會議通知的詳細說明內容，支援多項目說明'");
        $this->db->query("ALTER TABLE meeting_attendances COMMENT = '會議出席記錄表 - 記錄會員出席狀態，包含親自出席、委託出席、缺席等狀態及簽到時間'");
        $this->db->query("ALTER TABLE meeting_observers COMMENT = '會議列席者表 - 記錄會議列席人員資訊，包含機關單位人員等非投票權人員'");

        $this->db->query("ALTER TABLE voting_topics COMMENT = '投票議題表 - 儲存投票議題資料及統計結果，包含人數、土地面積、建物面積等多維度統計'");
        $this->db->query("ALTER TABLE voting_records COMMENT = '投票記錄表 - 記錄每位所有權人的投票選擇及權重，作為投票統計的基礎資料'");
        $this->db->query("ALTER TABLE meeting_documents COMMENT = '會議文件表 - 管理會議相關文件，包含議程、會議記錄、出席名單等各類文件'");
        $this->db->query("ALTER TABLE meeting_logs COMMENT = '會議操作日誌表 - 記錄會議相關的所有操作，提供完整的審計追蹤功能'");

        $this->db->query("ALTER TABLE users COMMENT = '系統使用者表 - 管理系統使用者帳號，支援角色權限管理及安全性控制'");
        $this->db->query("ALTER TABLE user_sessions COMMENT = '使用者會話表 - 管理使用者登入會話，支援多裝置登入及會話安全控制'");
        $this->db->query("ALTER TABLE user_permissions COMMENT = '使用者權限表 - 細粒度權限控制，可針對特定資源授予權限'");

        $this->db->query("ALTER TABLE system_settings COMMENT = '系統設定表 - 儲存系統各項設定參數，支援不同資料類型及分類管理'");
        $this->db->query("ALTER TABLE system_setting_history COMMENT = '系統設定異動記錄表 - 追蹤系統設定的異動歷史，提供審計功能'");

        $this->db->query("ALTER TABLE notifications COMMENT = '通知訊息表 - 管理系統通知訊息，支援多種通知類型及傳送方式'");
        $this->db->query("ALTER TABLE notification_templates COMMENT = '通知範本表 - 管理通知訊息範本，支援變數替換及多種傳送格式'");
        $this->db->query("ALTER TABLE user_notification_preferences COMMENT = '使用者通知偏好表 - 管理使用者對各類通知的接收偏好設定'");

        // Add missing column comments to existing tables
        $this->db->query("ALTER TABLE counties MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '縣市ID主鍵'");
        $this->db->query("ALTER TABLE counties MODIFY COLUMN code VARCHAR(10) NOT NULL COMMENT '縣市代碼（如：台北市=01）'");
        $this->db->query("ALTER TABLE counties MODIFY COLUMN name VARCHAR(50) NOT NULL COMMENT '縣市名稱（如：台北市）'");
        $this->db->query("ALTER TABLE counties MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE counties MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");

        $this->db->query("ALTER TABLE districts MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '行政區ID主鍵'");
        $this->db->query("ALTER TABLE districts MODIFY COLUMN county_id INT(11) UNSIGNED NOT NULL COMMENT '所屬縣市ID'");
        $this->db->query("ALTER TABLE districts MODIFY COLUMN code VARCHAR(10) NOT NULL COMMENT '行政區代碼'");
        $this->db->query("ALTER TABLE districts MODIFY COLUMN name VARCHAR(50) NOT NULL COMMENT '行政區名稱（如：中正區）'");
        $this->db->query("ALTER TABLE districts MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE districts MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");

        $this->db->query("ALTER TABLE sections MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '段別ID主鍵'");
        $this->db->query("ALTER TABLE sections MODIFY COLUMN district_id INT(11) UNSIGNED NOT NULL COMMENT '所屬行政區ID'");
        $this->db->query("ALTER TABLE sections MODIFY COLUMN code VARCHAR(10) NOT NULL COMMENT '段別代碼'");
        $this->db->query("ALTER TABLE sections MODIFY COLUMN name VARCHAR(100) NOT NULL COMMENT '段別名稱（如：大安段一小段）'");
        $this->db->query("ALTER TABLE sections MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE sections MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");

        $this->db->query("ALTER TABLE urban_renewals MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '都市更新會ID主鍵'");
        $this->db->query("ALTER TABLE urban_renewals MODIFY COLUMN name VARCHAR(255) NOT NULL COMMENT '都市更新會名稱'");
        $this->db->query("ALTER TABLE urban_renewals MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE urban_renewals MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");
        $this->db->query("ALTER TABLE urban_renewals MODIFY COLUMN deleted_at DATETIME COMMENT '軟刪除時間'");

        $this->db->query("ALTER TABLE land_plots MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '地號ID主鍵'");
        $this->db->query("ALTER TABLE land_plots MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE land_plots MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");
        $this->db->query("ALTER TABLE land_plots MODIFY COLUMN deleted_at DATETIME COMMENT '軟刪除時間'");

        $this->db->query("ALTER TABLE buildings MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '建物ID主鍵'");
        $this->db->query("ALTER TABLE buildings MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE buildings MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");
        $this->db->query("ALTER TABLE buildings MODIFY COLUMN deleted_at DATETIME COMMENT '軟刪除時間'");

        $this->db->query("ALTER TABLE building_land_relations MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '關聯ID主鍵'");
        $this->db->query("ALTER TABLE building_land_relations MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE building_land_relations MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");

        $this->db->query("ALTER TABLE owner_land_ownership MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '所有權關係ID主鍵'");
        $this->db->query("ALTER TABLE owner_land_ownership MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE owner_land_ownership MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");
        $this->db->query("ALTER TABLE owner_land_ownership MODIFY COLUMN deleted_at DATETIME COMMENT '軟刪除時間'");

        $this->db->query("ALTER TABLE owner_building_ownership MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '所有權關係ID主鍵'");
        $this->db->query("ALTER TABLE owner_building_ownership MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE owner_building_ownership MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");
        $this->db->query("ALTER TABLE owner_building_ownership MODIFY COLUMN deleted_at DATETIME COMMENT '軟刪除時間'");

        $this->db->query("ALTER TABLE meetings MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '會議ID主鍵'");
        $this->db->query("ALTER TABLE meetings MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE meetings MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");
        $this->db->query("ALTER TABLE meetings MODIFY COLUMN deleted_at DATETIME COMMENT '軟刪除時間'");

        $this->db->query("ALTER TABLE meeting_notices MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '會議通知ID主鍵'");
        $this->db->query("ALTER TABLE meeting_notices MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE meeting_notices MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");
        $this->db->query("ALTER TABLE meeting_notices MODIFY COLUMN deleted_at DATETIME COMMENT '軟刪除時間'");

        $this->db->query("ALTER TABLE meeting_notice_descriptions MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '會議通知說明ID主鍵'");
        $this->db->query("ALTER TABLE meeting_notice_descriptions MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE meeting_notice_descriptions MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");

        $this->db->query("ALTER TABLE meeting_attendances MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '出席記錄ID主鍵'");
        $this->db->query("ALTER TABLE meeting_attendances MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE meeting_attendances MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");

        $this->db->query("ALTER TABLE meeting_observers MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '列席者ID主鍵'");
        $this->db->query("ALTER TABLE meeting_observers MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE meeting_observers MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");

        $this->db->query("ALTER TABLE voting_topics MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '投票議題ID主鍵'");
        $this->db->query("ALTER TABLE voting_topics MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE voting_topics MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");
        $this->db->query("ALTER TABLE voting_topics MODIFY COLUMN deleted_at DATETIME COMMENT '軟刪除時間'");

        $this->db->query("ALTER TABLE voting_records MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '投票記錄ID主鍵'");
        $this->db->query("ALTER TABLE voting_records MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE voting_records MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");

        $this->db->query("ALTER TABLE meeting_documents MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '會議文件ID主鍵'");
        $this->db->query("ALTER TABLE meeting_documents MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");
        $this->db->query("ALTER TABLE meeting_documents MODIFY COLUMN updated_at DATETIME COMMENT '更新時間'");
        $this->db->query("ALTER TABLE meeting_documents MODIFY COLUMN deleted_at DATETIME COMMENT '軟刪除時間'");

        $this->db->query("ALTER TABLE meeting_logs MODIFY COLUMN id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '會議日誌ID主鍵'");
        $this->db->query("ALTER TABLE meeting_logs MODIFY COLUMN created_at DATETIME COMMENT '建立時間'");

        // Add comprehensive field documentation via comments for better understanding
        $this->db->query("
            INSERT INTO system_settings (setting_key, setting_value, setting_type, category, title, description, is_public, is_editable, display_order, created_at, updated_at)
            VALUES
            ('database_schema_version', '1.0.0', 'string', 'system', '資料庫架構版本', '目前資料庫架構版本號，用於版本控制和升級管理', 0, 0, 999, NOW(), NOW()),
            ('last_schema_update', '" . date('Y-m-d H:i:s') . "', 'string', 'system', '最後架構更新時間', '最後一次資料庫架構更新的時間戳記', 0, 0, 1000, NOW(), NOW())
        ");
    }

    public function down()
    {
        // Remove table comments
        $this->db->query("ALTER TABLE counties COMMENT = ''");
        $this->db->query("ALTER TABLE districts COMMENT = ''");
        $this->db->query("ALTER TABLE sections COMMENT = ''");
        $this->db->query("ALTER TABLE urban_renewals COMMENT = ''");
        $this->db->query("ALTER TABLE land_plots COMMENT = ''");
        $this->db->query("ALTER TABLE property_owners COMMENT = ''");
        $this->db->query("ALTER TABLE buildings COMMENT = ''");
        $this->db->query("ALTER TABLE building_land_relations COMMENT = ''");
        $this->db->query("ALTER TABLE owner_land_ownership COMMENT = ''");
        $this->db->query("ALTER TABLE owner_building_ownership COMMENT = ''");
        $this->db->query("ALTER TABLE meetings COMMENT = ''");
        $this->db->query("ALTER TABLE meeting_notices COMMENT = ''");
        $this->db->query("ALTER TABLE meeting_notice_descriptions COMMENT = ''");
        $this->db->query("ALTER TABLE meeting_attendances COMMENT = ''");
        $this->db->query("ALTER TABLE meeting_observers COMMENT = ''");
        $this->db->query("ALTER TABLE voting_topics COMMENT = ''");
        $this->db->query("ALTER TABLE voting_records COMMENT = ''");
        $this->db->query("ALTER TABLE meeting_documents COMMENT = ''");
        $this->db->query("ALTER TABLE meeting_logs COMMENT = ''");
        $this->db->query("ALTER TABLE users COMMENT = ''");
        $this->db->query("ALTER TABLE user_sessions COMMENT = ''");
        $this->db->query("ALTER TABLE user_permissions COMMENT = ''");
        $this->db->query("ALTER TABLE system_settings COMMENT = ''");
        $this->db->query("ALTER TABLE system_setting_history COMMENT = ''");
        $this->db->query("ALTER TABLE notifications COMMENT = ''");
        $this->db->query("ALTER TABLE notification_templates COMMENT = ''");
        $this->db->query("ALTER TABLE user_notification_preferences COMMENT = ''");

        // Remove added system settings
        $this->db->query("DELETE FROM system_settings WHERE setting_key IN ('database_schema_version', 'last_schema_update')");
    }
}