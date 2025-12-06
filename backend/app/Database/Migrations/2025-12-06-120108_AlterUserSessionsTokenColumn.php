<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterUserSessionsTokenColumn extends Migration
{
    public function up()
    {
        // 修改 session_token 欄位從 VARCHAR(255) 改為 TEXT，以容納 JWT token
        $this->forge->modifyColumn('user_sessions', [
            'session_token' => [
                'type' => 'TEXT',
                'null' => false,
                'comment' => 'JWT 會話令牌',
            ],
        ]);

        // 移除舊的 unique index（TEXT 類型不能有 unique index）
        try {
            $this->db->query('ALTER TABLE user_sessions DROP INDEX unique_session_token');
        } catch (\Exception $e) {
            // Index might not exist, ignore
        }
    }

    public function down()
    {
        // 恢復為 VARCHAR(255)
        $this->forge->modifyColumn('user_sessions', [
            'session_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
                'comment' => '會話令牌',
            ],
        ]);
    }
}
