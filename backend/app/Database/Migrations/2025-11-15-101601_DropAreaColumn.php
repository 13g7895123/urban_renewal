<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropAreaColumn extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('urban_renewals', 'area');
    }

    public function down()
    {
        $this->forge->addColumn('urban_renewals', [
            'area' => [
                'type'       => 'DECIMAL',
                'constraint' => [10, 2],
                'null'       => true,
                'comment'    => '土地面積（平方公尺）',
            ],
        ]);
    }
}
