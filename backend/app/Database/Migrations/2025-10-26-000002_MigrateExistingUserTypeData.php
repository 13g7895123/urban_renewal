<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateExistingUserTypeData extends Migration
{
    public function up()
    {
        // 將所有有 urban_renewal_id 的使用者設定為企業使用者
        $this->db->query("
            UPDATE users
            SET user_type = 'enterprise'
            WHERE urban_renewal_id IS NOT NULL
            AND deleted_at IS NULL
        ");

        // 將 chairman 角色的企業使用者設定為企業管理者
        $this->db->query("
            UPDATE users
            SET is_company_manager = 1
            WHERE role = 'chairman'
            AND user_type = 'enterprise'
            AND deleted_at IS NULL
        ");

        // Admin 使用者保持為一般類型（因為他們是系統管理員，不屬於特定企業）
        // 除非他們也有 urban_renewal_id
    }

    public function down()
    {
        // 回復資料（將所有設定重置為預設值）
        $this->db->query("
            UPDATE users
            SET user_type = 'general',
                is_company_manager = 0
        ");
    }
}
