<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyManagersRenewalsTable extends Migration
{
    public function up()
    {
        // 建立企業管理者與更新會的關聯表
        // 支援一個企業管理者可管理多個更新會
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'company_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
                'comment' => '所屬企業ID',
            ],
            'manager_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
                'comment' => '企業管理者用戶ID',
            ],
            'urban_renewal_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
                'comment' => '管理者被授權管理的更新會ID',
            ],
            'permission_level' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'full',
                'comment' => '權限等級：full(完全)/readonly(唯讀)/finance(財務)',
            ],
            'is_primary' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '是否為該企業該更新會的主管理者',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        
        // 複合UNIQUE約束：同一企業的同一管理者對同一更新會只能出現一次
        $this->forge->addUniqueKey(['company_id', 'manager_id', 'urban_renewal_id'], 'unique_manager_renewal');
        
        // 複合索引：查詢某企業的某管理者可訪問的更新會
        $this->forge->addKey(['company_id', 'manager_id'], false, false, 'idx_company_manager');
        
        // 複合索引：查詢某企業的某更新會有哪些管理者
        $this->forge->addKey(['company_id', 'urban_renewal_id'], false, false, 'idx_company_renewal');
        
        // 單一索引：查詢某管理者的所有授權
        $this->forge->addKey('manager_id', false, false, 'idx_manager');

        $this->forge->createTable('company_managers_renewals');

        // 外鍵約束需在表建立後添加
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'fk_cmr_company_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('manager_id', 'users', 'id', 'fk_cmr_manager_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('urban_renewal_id', 'urban_renewals', 'id', 'fk_cmr_renewal_id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropTable('company_managers_renewals', true);
    }
}
