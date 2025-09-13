<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUrbanRenewalsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'area' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
                'comment'    => '土地面積(平方公尺)',
            ],
            'member_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '所有權人數',
            ],
            'chairman_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => false,
                'comment'    => '理事長姓名',
            ],
            'chairman_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => false,
                'comment'    => '理事長電話',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('name');
        $this->forge->addKey('created_at');
        $this->forge->createTable('urban_renewals');
    }

    public function down()
    {
        $this->forge->dropTable('urban_renewals');
    }
}