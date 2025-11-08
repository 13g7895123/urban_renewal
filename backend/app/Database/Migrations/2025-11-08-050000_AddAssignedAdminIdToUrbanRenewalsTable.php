<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAssignedAdminIdToUrbanRenewalsTable extends Migration
{
    public function up()
    {
        // 檢查欄位是否已存在，避免重複添加
        if (!$this->db->fieldExists('assigned_admin_id', 'urban_renewals')) {
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
    }

    public function down()
    {
        // 檢查欄位是否存在才執行刪除
        if ($this->db->fieldExists('assigned_admin_id', 'urban_renewals')) {
            // 移除外鍵約束（使用 try-catch 避免約束不存在時報錯）
            try {
                $this->db->query('ALTER TABLE `urban_renewals` DROP FOREIGN KEY `fk_urban_renewals_assigned_admin`');
            } catch (\Exception $e) {
                // 外鍵約束可能不存在，忽略錯誤
                log_message('info', 'Foreign key fk_urban_renewals_assigned_admin does not exist or already removed');
            }

            // 移除欄位
            $this->forge->dropColumn('urban_renewals', 'assigned_admin_id');
        }
    }
}
