<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropMemberCountColumn extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('urban_renewals', 'member_count');
    }

    public function down()
    {
        $this->forge->addColumn('urban_renewals', [
            'member_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '所有權人數',
            ],
        ]);
    }
}
