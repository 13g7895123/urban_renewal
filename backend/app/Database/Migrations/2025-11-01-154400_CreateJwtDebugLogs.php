<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJwtDebugLogs extends Migration
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
            'request_id' => [
                'type' => 'VARCHAR',
                'constraint' => 36,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'token_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'request_uri' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'request_method' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'stage' => [
                'type' => 'ENUM',
                'constraint' => ['header_check','token_parse','token_decode','signature_verify','claims_validate','user_validate','permission_check','success'],
            ],
            'stage_status' => [
                'type' => 'ENUM',
                'constraint' => ['pass','fail','error'],
            ],
            'stage_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'has_auth_header' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'auth_header_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'token_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'jwt_iss' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'jwt_aud' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'jwt_iat' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'jwt_exp' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'jwt_user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'jwt_role' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'jwt_session_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'jwt_jti' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'is_expired' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'time_until_exp' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'signature_valid' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'user_exists' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'user_is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'user_role' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'required_roles' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'role_check_passed' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'session_exists' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'session_is_expired' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'session_logged_out' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => true,
            ],
            'error_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'error_trace' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'validation_time_ms' => [
                'type' => 'DECIMAL',
                'constraint' => '10,3',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('request_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('token_hash');
        $this->forge->addKey(['stage', 'stage_status']);
        $this->forge->addKey('created_at');
        $this->forge->addKey('ip_address');

        $this->forge->createTable('jwt_debug_logs', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci', 'COMMENT' => 'JWT 驗證除錯日誌']);
    }

    public function down()
    {
        $this->forge->dropTable('jwt_debug_logs');
    }
}
