<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserRenewalAssignmentsTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => '使用者ID',
            ],
            'urban_renewal_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => '被指派管理的更新會ID',
            ],
            'assigned_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => '指派者ID',
            ],
            'permissions' => [
                'type'    => 'JSON',
                'null'    => true,
                'comment' => '細部權限設定',
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

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('urban_renewal_id', 'urban_renewals', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey(['user_id', 'urban_renewal_id'], false, true, 'idx_user_renewal');

        $this->forge->createTable('user_renewal_assignments');
    }

    public function down()
    {
        $this->forge->dropTable('user_renewal_assignments');
    }
}
