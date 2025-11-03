<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveCompanyFieldsFromUrbanRenewalsTable extends Migration
{
    public function up()
    {
        // Remove company-specific fields from urban_renewals table
        // Keep 'name' field as it represents urban renewal name
        $this->forge->dropColumn('urban_renewals', [
            'tax_id',
            'company_phone',
            'max_renewal_count',
            'max_issue_count'
        ]);

        // Update table comment to clarify it's for urban renewal associations only
        $this->db->query("ALTER TABLE urban_renewals COMMENT = '都市更新會資料表 - 儲存更新會基本資料，包含面積、成員數量、理事長資訊等核心資料（企業資料已移至 companies 表）'");
        
        // Update name column comment to clarify it's urban renewal name, not company name
        $this->db->query("ALTER TABLE urban_renewals MODIFY COLUMN name VARCHAR(255) NOT NULL COMMENT '更新會名稱（非企業名稱）'");
    }

    public function down()
    {
        // Re-add company fields if rolling back
        $fields = [
            'tax_id' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
                'comment' => '統一編號',
                'after' => 'name',
            ],
            'company_phone' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => true,
                'comment' => '企業電話',
                'after' => 'chairman_phone',
            ],
            'max_renewal_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
                'null' => false,
                'comment' => '最大更新會數量',
                'after' => 'company_phone',
            ],
            'max_issue_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 8,
                'null' => false,
                'comment' => '最大議題數量',
                'after' => 'max_renewal_count',
            ],
        ];

        $this->forge->addColumn('urban_renewals', $fields);

        // Restore original comment
        $this->db->query("ALTER TABLE urban_renewals COMMENT = '都市更新會資料表 - 儲存更新會基本資料，包含面積、成員數量、理事長資訊等核心資料'");
        $this->db->query("ALTER TABLE urban_renewals MODIFY COLUMN name VARCHAR(255) NOT NULL COMMENT '都市更新會名稱'");

        // Restore data from companies table
        $this->db->query("
            UPDATE urban_renewals ur
            INNER JOIN companies c ON ur.id = c.urban_renewal_id
            SET 
                ur.tax_id = c.tax_id,
                ur.company_phone = c.company_phone,
                ur.max_renewal_count = c.max_renewal_count,
                ur.max_issue_count = c.max_issue_count
        ");
    }
}
