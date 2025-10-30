<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompanyFieldsToUrbanRenewalsTable extends Migration
{
    public function up()
    {
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
    }

    public function down()
    {
        $this->forge->dropColumn('urban_renewals', [
            'tax_id',
            'company_phone',
            'max_renewal_count',
            'max_issue_count'
        ]);
    }
}
