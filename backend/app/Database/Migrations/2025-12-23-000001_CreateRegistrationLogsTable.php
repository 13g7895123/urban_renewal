<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRegistrationLogsTable extends Migration
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
            'request_data' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '註冊請求資料（密碼已遮蔽）',
            ],
            'response_status' => [
                'type' => 'ENUM',
                'constraint' => ['success', 'error'],
                'default' => 'error',
                'comment' => '回應狀態',
            ],
            'response_code' => [
                'type' => 'INT',
                'constraint' => 3,
                'unsigned' => true,
                'null' => true,
                'comment' => 'HTTP 狀態碼',
            ],
            'response_message' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => '回應訊息',
            ],
            'error_details' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '錯誤詳情',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => '請求 IP 位址',
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => '瀏覽器 User Agent',
            ],
            'created_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '成功建立的使用者 ID',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('response_status');
        $this->forge->addKey('created_at');
        $this->forge->addKey('ip_address');

        $this->forge->createTable('registration_logs', true);

        // 加入表格註解
        $this->db->query("ALTER TABLE `registration_logs` COMMENT '註冊請求日誌表'");
    }

    public function down()
    {
        $this->forge->dropTable('registration_logs', true);
    }
}
