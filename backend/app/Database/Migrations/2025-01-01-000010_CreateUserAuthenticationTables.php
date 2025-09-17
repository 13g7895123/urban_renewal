<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserAuthenticationTables extends Migration
{
    public function up()
    {
        // Users Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
                'comment' => '使用者帳號',
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => true,
                'null' => true,
                'comment' => '電子信箱',
            ],
            'password_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => '密碼雜湊值',
            ],
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'chairman', 'member', 'observer'],
                'default' => 'member',
                'comment' => '使用者角色：管理員/理事長/會員/列席者',
            ],
            'urban_renewal_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '所屬更新會ID（限制存取範圍）',
            ],
            'property_owner_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '關聯所有權人ID（會員角色使用）',
            ],
            'full_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => '真實姓名',
            ],
            'phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => '聯絡電話',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '帳號是否啟用',
            ],
            'last_login_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '最後登入時間',
            ],
            'login_attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => '登入嘗試次數',
            ],
            'locked_until' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '帳號鎖定至',
            ],
            'password_reset_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '密碼重設令牌',
            ],
            'password_reset_expires' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '密碼重設令牌過期時間',
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
        $this->forge->addUniqueKey('username', 'unique_username');
        $this->forge->addUniqueKey('email', 'unique_email');
        $this->forge->addForeignKey('urban_renewal_id', 'urban_renewals', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('property_owner_id', 'property_owners', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('role', false, false, 'idx_role');
        $this->forge->addKey('is_active', false, false, 'idx_is_active');
        $this->forge->addKey('urban_renewal_id', false, false, 'idx_urban_renewal_id');
        $this->forge->addKey('last_login_at', false, false, 'idx_last_login_at');

        $this->forge->createTable('users');

        // User Sessions Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '使用者ID',
            ],
            'session_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => true,
                'comment' => '會話令牌',
            ],
            'refresh_token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '刷新令牌',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'comment' => '會話過期時間',
            ],
            'refresh_expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '刷新令牌過期時間',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => '登入IP位址',
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '瀏覽器用戶代理',
            ],
            'device_info' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '裝置資訊',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '會話是否有效',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_activity_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '最後活動時間',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey('session_token', 'unique_session_token');
        $this->forge->addKey('user_id', false, false, 'idx_user_id');
        $this->forge->addKey('expires_at', false, false, 'idx_expires_at');
        $this->forge->addKey('is_active', false, false, 'idx_is_active');
        $this->forge->addKey('last_activity_at', false, false, 'idx_last_activity_at');
        $this->forge->addKey(['user_id', 'is_active'], false, false, 'idx_user_active');

        $this->forge->createTable('user_sessions');

        // User Permissions Table (for fine-grained access control)
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '使用者ID',
            ],
            'permission_type' => [
                'type' => 'ENUM',
                'constraint' => [
                    'urban_renewal_manage',
                    'meeting_manage',
                    'voting_manage',
                    'property_owner_manage',
                    'system_admin',
                    'report_view',
                    'document_manage'
                ],
                'comment' => '權限類型',
            ],
            'resource_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '資源ID（特定更新會、會議等）',
            ],
            'granted_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '權限授予者ID',
            ],
            'granted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '權限授予時間',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '權限過期時間',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('granted_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addUniqueKey(['user_id', 'permission_type', 'resource_id'], 'unique_user_permission');
        $this->forge->addKey('user_id', false, false, 'idx_user_id');
        $this->forge->addKey('permission_type', false, false, 'idx_permission_type');
        $this->forge->addKey('expires_at', false, false, 'idx_expires_at');

        $this->forge->createTable('user_permissions');
    }

    public function down()
    {
        $this->forge->dropTable('user_permissions');
        $this->forge->dropTable('user_sessions');
        $this->forge->dropTable('users');
    }
}