<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiRequestLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'method' => [
                'type' => 'ENUM',
                'constraint' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'comment' => 'HTTP 請求方法',
            ],
            'endpoint' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'comment' => 'API 路徑',
            ],
            'request_headers' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '請求標頭（敏感資訊已遮蔽）',
            ],
            'request_query' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '查詢參數',
            ],
            'request_body' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '請求內容（密碼等敏感資訊已遮蔽）',
            ],
            'response_status' => [
                'type' => 'INT',
                'constraint' => 3,
                'unsigned' => true,
                'null' => true,
                'comment' => 'HTTP 狀態碼 (200, 404, 500 等)',
            ],
            'response_headers' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '回應標頭',
            ],
            'response_body' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => '回應內容（JSON 字串）',
            ],
            'duration_ms' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '執行時間（毫秒）',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '錯誤訊息',
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '發送請求的使用者 ID',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => '請求來源 IP 位址',
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => '瀏覽器 User Agent',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // 設定主鍵
        $this->forge->addKey('id', true);

        // 建立索引以加速查詢
        $this->forge->addKey('method');
        $this->forge->addKey('endpoint');
        $this->forge->addKey('response_status');
        $this->forge->addKey('user_id');
        $this->forge->addKey('ip_address');
        $this->forge->addKey('created_at');

        // 建立複合索引用於常見查詢模式
        $this->forge->addKey(['endpoint', 'method']);
        $this->forge->addKey(['user_id', 'created_at']);

        // 建立資料表
        $this->forge->createTable('api_request_logs', true);

        // 加入表格註解
        $this->db->query("ALTER TABLE `api_request_logs` COMMENT 'API 請求日誌表，用於記錄所有 API 請求與回應以便除錯'");
    }

    public function down()
    {
        $this->forge->dropTable('api_request_logs', true);
    }
}
