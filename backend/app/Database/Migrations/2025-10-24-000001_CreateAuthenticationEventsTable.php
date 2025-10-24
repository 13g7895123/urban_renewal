<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthenticationEventsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'event_type' => [
                'type' => 'ENUM',
                'constraint' => ['login_success', 'login_failure', 'logout', 'token_refresh'],
                'null' => false,
                'comment' => '認證事件類型',
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '使用者 ID（成功認證時）',
            ],
            'username_attempted' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => '嘗試的使用者名稱（失敗嘗試時）',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => 'IPv4/IPv6 位址',
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '瀏覽器 User Agent',
            ],
            'failure_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '失敗原因（invalid_credentials、account_locked 等）',
            ],
            'event_metadata' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '額外事件資料（JSON 格式）',
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
                'comment' => '事件時間戳記',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('event_type');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['ip_address', 'created_at']);

        // Foreign key constraint
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('authentication_events');
    }

    public function down()
    {
        $this->forge->dropTable('authentication_events');
    }
}
