<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveDeletedAtFromOwnershipTables extends Migration
{
    public function up()
    {
        // 移除 owner_building_ownership 的 deleted_at 欄位
        $this->forge->dropColumn('owner_building_ownership', 'deleted_at');

        // 移除 owner_land_ownership 的 deleted_at 欄位
        $this->forge->dropColumn('owner_land_ownership', 'deleted_at');
    }

    public function down()
    {
        // 回滾時重新加入 owner_building_ownership 的 deleted_at 欄位
        $fields = [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at',
            ],
        ];

        $this->forge->addColumn('owner_building_ownership', $fields);

        // 回滾時重新加入 owner_land_ownership 的 deleted_at 欄位
        $this->forge->addColumn('owner_land_ownership', $fields);
    }
}
