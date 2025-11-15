<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompanyIdToUsersTable extends Migration
{
    public function up()
    {
        // 新增 company_id 欄位到 users 表，用於新架構中企業管理者與企業的關聯
        // 新架構：一個企業可以管理多個更新會
        // 企業管理者通過 company_id 與企業關聯，而不是 urban_renewal_id
        
        $fields = [
            'company_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
                'comment' => '所屬企業ID (新架構，一對多關係)',
                'after' => 'urban_renewal_id',
            ],
        ];

        $this->forge->addColumn('users', $fields);

        // 添加外鍵約束
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'fk_users_company_id', 'SET NULL', 'CASCADE');

        // 添加索引
        $this->forge->addKey('company_id', false, false, 'idx_company_id');
    }

    public function down()
    {
        // 移除外鍵
        $this->forge->dropForeignKey('users', 'fk_users_company_id');

        // 移除索引
        $this->forge->dropKey('users', 'idx_company_id');

        // 移除欄位
        $this->forge->dropColumn('users', 'company_id');
    }
}
