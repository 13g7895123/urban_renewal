<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompaniesTable extends Migration
{
    public function up()
    {
        // Create companies table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'urban_renewal_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '對應的更新會ID (一對一關係)',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
                'comment'    => '企業名稱',
            ],
            'tax_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'comment'    => '統一編號',
            ],
            'company_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'comment'    => '企業電話',
            ],
            'max_renewal_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 1,
                'null'       => false,
                'comment'    => '最大更新會數量',
            ],
            'max_issue_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 8,
                'null'       => false,
                'comment'    => '最大議題數量',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('urban_renewal_id');
        $this->forge->addUniqueKey('urban_renewal_id', 'unique_urban_renewal_id');
        $this->forge->addForeignKey('urban_renewal_id', 'urban_renewals', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('companies');

        // Add table comment
        $this->db->query("ALTER TABLE companies COMMENT = '企業資料表 - 儲存管理企業基本資料，與更新會為一對一關係'");

        // Migrate data from urban_renewals to companies
        // Copy company-related data from urban_renewals table
        $this->db->query("
            INSERT INTO companies (urban_renewal_id, name, tax_id, company_phone, max_renewal_count, max_issue_count, created_at, updated_at)
            SELECT 
                id as urban_renewal_id,
                name,
                tax_id,
                company_phone,
                COALESCE(max_renewal_count, 1) as max_renewal_count,
                COALESCE(max_issue_count, 8) as max_issue_count,
                created_at,
                updated_at
            FROM urban_renewals
            WHERE deleted_at IS NULL
        ");
    }

    public function down()
    {
        $this->forge->dropTable('companies');
    }
}
