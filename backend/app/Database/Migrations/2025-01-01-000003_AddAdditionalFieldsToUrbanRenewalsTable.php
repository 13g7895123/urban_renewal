<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdditionalFieldsToUrbanRenewalsTable extends Migration
{
    public function up()
    {
        $fields = [
            'address' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => '設立地址',
            ],
            'representative' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => '負責人',
            ],
        ];

        $this->forge->addColumn('urban_renewals', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('urban_renewals', ['address', 'representative']);
    }
}