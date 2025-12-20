<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyPropertyOwnerUniqueKey extends Migration
{
    public function up()
    {
        // 先嘗試移除舊的唯一索引
        // 注意：索引名稱是 'unique_owner_code'，這是在之前的 migration 中定義的
        $this->db->query('ALTER TABLE property_owners DROP INDEX unique_owner_code');

        // 新增複合唯一索引 (urban_renewal_id + owner_code)
        // 這樣可以確保在同一個更新會內 owner_code 不重複，但不同更新會可以有相同的 owner_code (例如 '1')
        $this->db->query('ALTER TABLE property_owners ADD UNIQUE KEY unique_project_owner (urban_renewal_id, owner_code)');
    }

    public function down()
    {
        // 還原變更
        $this->db->query('ALTER TABLE property_owners DROP INDEX unique_project_owner');
        $this->db->query('ALTER TABLE property_owners ADD UNIQUE KEY unique_owner_code (owner_code)');
    }
}
