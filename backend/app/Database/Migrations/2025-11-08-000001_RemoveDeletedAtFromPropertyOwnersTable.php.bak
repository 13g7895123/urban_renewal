<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveDeletedAtFromPropertyOwnersTable extends Migration
{
    public function up()
    {
        // 移除 deleted_at 欄位
        $this->forge->dropColumn('property_owners', 'deleted_at');
    }

    public function down()
    {
        // 回滾時重新加入 deleted_at 欄位
        $fields = [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at',
            ],
        ];

        $this->forge->addColumn('property_owners', $fields);
    }
}
