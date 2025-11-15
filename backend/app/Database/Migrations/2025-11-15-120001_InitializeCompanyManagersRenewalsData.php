<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitializeCompanyManagersRenewalsData extends Migration
{
    public function up()
    {
        // 資料遷移：將現有的企業管理者（is_company_manager=1）關聯到他們的更新會
        // 規則：如果用戶是企業管理者且有company_id和urban_renewal_id，則建立一條關聯記錄
        
        $db = $this->db;
        
        try {
            // 遷移資料：從users表提取企業管理者資訊到company_managers_renewals表
            $db->query(<<<SQL
                INSERT INTO company_managers_renewals 
                (company_id, manager_id, urban_renewal_id, permission_level, is_primary, created_at, updated_at)
                SELECT 
                    u.company_id,
                    u.id as manager_id,
                    u.urban_renewal_id,
                    'full' as permission_level,
                    1 as is_primary,
                    NOW() as created_at,
                    NOW() as updated_at
                FROM users u
                WHERE u.user_type = 'enterprise'
                  AND u.is_company_manager = 1
                  AND u.company_id IS NOT NULL
                  AND u.urban_renewal_id IS NOT NULL
                ON DUPLICATE KEY UPDATE 
                    permission_level = VALUES(permission_level),
                    is_primary = VALUES(is_primary),
                    updated_at = NOW()
            SQL);
            
            log_message('info', '[InitializeCompanyManagersRenewalsData] Data migration completed successfully');
            
        } catch (\Exception $e) {
            log_message('error', '[InitializeCompanyManagersRenewalsData] Migration error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down()
    {
        // 回滾時清空表（或刪除此遷移期間新增的資料）
        $db = $this->db;
        
        try {
            // 清空表中來自users的遷移資料
            $db->query(<<<SQL
                DELETE FROM company_managers_renewals
                WHERE permission_level = 'full' AND is_primary = 1
            SQL);
            
            log_message('info', '[InitializeCompanyManagersRenewalsData] Data rollback completed');
            
        } catch (\Exception $e) {
            log_message('error', '[InitializeCompanyManagersRenewalsData] Rollback error: ' . $e->getMessage());
            throw $e;
        }
    }
}
