<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddContactFieldsToUrbanRenewalsTable extends Migration
{
    public function up()
    {
        $fields = [
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => '聯絡電話',
            ],
            'fax' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
                'comment'    => '傳真號碼',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'comment'    => '電子郵件',
            ],
            'contact_person' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
                'comment'    => '聯絡人',
            ],
            'notes' => [
                'type'       => 'TEXT',
                'null'       => true,
                'comment'    => '備註',
            ],
        ];

        $this->forge->addColumn('urban_renewals', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('urban_renewals', ['phone', 'fax', 'email', 'contact_person', 'notes']);
    }
}
