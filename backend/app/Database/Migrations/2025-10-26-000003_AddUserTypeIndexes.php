<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserTypeIndexes extends Migration
{
    public function up()
    {
        // 為 user_type 欄位新增索引（提升查詢效能）
        $this->db->query("
            ALTER TABLE users
            ADD INDEX idx_user_type (user_type)
        ");

        // 為 is_company_manager 欄位新增索引
        $this->db->query("
            ALTER TABLE users
            ADD INDEX idx_is_company_manager (is_company_manager)
        ");

        // 複合索引：同時查詢 urban_renewal_id 和 is_company_manager
        $this->db->query("
            ALTER TABLE users
            ADD INDEX idx_company_manager (urban_renewal_id, is_company_manager)
        ");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE users DROP INDEX idx_user_type");
        $this->db->query("ALTER TABLE users DROP INDEX idx_is_company_manager");
        $this->db->query("ALTER TABLE users DROP INDEX idx_company_manager");
    }
}
