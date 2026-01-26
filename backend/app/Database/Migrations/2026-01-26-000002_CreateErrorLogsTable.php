<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateErrorLogsTable extends Migration
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
            'severity' => [
                'type' => 'ENUM',
                'constraint' => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
                'default' => 'error',
                'comment' => '錯誤嚴重程度',
            ],
            'message' => [
                'type' => 'TEXT',
                'comment' => '錯誤訊息',
            ],
            'exception_class' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '異常類別名稱',
            ],
            'file' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => '發生錯誤的檔案路徑',
            ],
            'line' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '發生錯誤的行號',
            ],
            'trace' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => '完整的堆疊追蹤',
            ],
            'context' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '錯誤發生時的上下文資訊',
            ],
            'request_method' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'comment' => 'HTTP 請求方法',
            ],
            'request_uri' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => '請求的 URI',
            ],
            'request_body' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => '請求內容',
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '發生錯誤時的使用者 ID',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => '請求來源 IP',
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'comment' => '瀏覽器 User Agent',
            ],
            'environment' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'production',
                'comment' => '執行環境',
            ],
            'is_resolved' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '是否已解決',
            ],
            'resolved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '解決時間',
            ],
            'resolved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '解決者 ID',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '備註說明',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // 設定主鍵
        $this->forge->addKey('id', true);

        // 建立索引
        $this->forge->addKey('severity');
        $this->forge->addKey('exception_class');
        $this->forge->addKey('user_id');
        $this->forge->addKey('is_resolved');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['severity', 'created_at']);
        $this->forge->addKey(['is_resolved', 'created_at']);

        // 建立資料表
        $this->forge->createTable('error_logs', true);

        // 加入表格註解
        $this->db->query("ALTER TABLE `error_logs` COMMENT '系統錯誤日誌表，記錄所有異常和錯誤'");
    }

    public function down()
    {
        $this->forge->dropTable('error_logs', true);
    }
}
