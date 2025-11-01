<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserProfileFieldsToUsersTable extends Migration
{
    public function up()
    {
        // 新增使用者個人資料欄位
        $fields = [
            'nickname' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => '暱稱',
                'after' => 'full_name',
            ],
            'line_account' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'LINE 帳號',
                'after' => 'phone',
            ],
            'position' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => '職稱',
                'after' => 'line_account',
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['nickname', 'line_account', 'position']);
    }
}
