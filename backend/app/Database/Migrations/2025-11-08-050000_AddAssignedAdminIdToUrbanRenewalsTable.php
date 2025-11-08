<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAssignedAdminIdToUrbanRenewalsTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('urban_renewals', [
            'assigned_admin_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '分配的企業管理者ID',
                'after'      => 'chairman_phone',
            ],
        ]);

        // 新增外鍵索引
        $this->forge->addKey('assigned_admin_id');

        // 新增外鍵約束
        $this->db->query('ALTER TABLE `urban_renewals` ADD CONSTRAINT `fk_urban_renewals_assigned_admin` FOREIGN KEY (`assigned_admin_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        // 移除外鍵約束
        $this->db->query('ALTER TABLE `urban_renewals` DROP FOREIGN KEY `fk_urban_renewals_assigned_admin`');

        // 移除欄位
        $this->forge->dropColumn('urban_renewals', 'assigned_admin_id');
    }
}
