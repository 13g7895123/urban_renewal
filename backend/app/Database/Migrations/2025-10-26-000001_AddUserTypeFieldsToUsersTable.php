<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserTypeFieldsToUsersTable extends Migration
{
    public function up()
    {
        // 新增 user_type 欄位：區分企業使用者與一般使用者
        $fields = [
            'user_type' => [
                'type' => 'ENUM',
                'constraint' => ['enterprise', 'general'],
                'default' => 'general',
                'null' => false,
                'comment' => '使用者類型：企業使用者/一般使用者',
                'after' => 'role',
            ],
            'is_company_manager' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'comment' => '是否為企業管理者（僅企業使用者適用）',
                'after' => 'user_type',
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['user_type', 'is_company_manager']);
    }
}
