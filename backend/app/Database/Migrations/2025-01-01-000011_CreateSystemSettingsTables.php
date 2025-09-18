<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemSettingsTables extends Migration
{
    public function up()
    {
        // System Settings Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'setting_key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
                'comment' => '設定項目鍵值',
            ],
            'setting_value' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => '設定項目值',
            ],
            'setting_type' => [
                'type' => 'ENUM',
                'constraint' => ['string', 'number', 'boolean', 'json', 'encrypted'],
                'default' => 'string',
                'comment' => '設定資料類型',
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'general',
                'comment' => '設定分類',
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '設定項目名稱',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '設定項目說明',
            ],
            'validation_rules' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '驗證規則（JSON格式）',
            ],
            'is_public' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '是否為公開設定（前端可存取）',
            ],
            'is_editable' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '是否可編輯',
            ],
            'display_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '顯示順序',
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '建立者ID',
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '更新者ID',
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
        $this->forge->addUniqueKey('setting_key', 'unique_setting_key');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('updated_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('category', false, false, 'idx_category');
        $this->forge->addKey('is_public', false, false, 'idx_is_public');
        $this->forge->addKey('is_editable', false, false, 'idx_is_editable');
        $this->forge->addKey(['category', 'display_order'], false, false, 'idx_category_order');

        $this->forge->createTable('system_settings');

        // System Setting History Table (for audit trail)
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'setting_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '系統設定ID',
            ],
            'old_value' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => '舊值',
            ],
            'new_value' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => '新值',
            ],
            'changed_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '修改者ID',
            ],
            'change_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '修改原因',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => '修改者IP位址',
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '瀏覽器用戶代理',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('setting_id', 'system_settings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('changed_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('setting_id', false, false, 'idx_setting_id');
        $this->forge->addKey('changed_by', false, false, 'idx_changed_by');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        $this->forge->createTable('system_setting_history');

        // Insert default system settings
        $defaultSettings = [
            [
                'setting_key' => 'app_name',
                'setting_value' => '都市更新管理系統',
                'setting_type' => 'string',
                'category' => 'general',
                'title' => '應用程式名稱',
                'description' => '系統顯示的應用程式名稱',
                'is_public' => 1,
                'is_editable' => 1,
                'display_order' => 1,
            ],
            [
                'setting_key' => 'app_version',
                'setting_value' => '1.0.0',
                'setting_type' => 'string',
                'category' => 'general',
                'title' => '系統版本',
                'description' => '目前系統版本號',
                'is_public' => 1,
                'is_editable' => 0,
                'display_order' => 2,
            ],
            [
                'setting_key' => 'maintenance_mode',
                'setting_value' => 'false',
                'setting_type' => 'boolean',
                'category' => 'system',
                'title' => '維護模式',
                'description' => '啟用維護模式將暫停所有用戶存取',
                'is_public' => 0,
                'is_editable' => 1,
                'display_order' => 1,
            ],
            [
                'setting_key' => 'session_timeout',
                'setting_value' => '3600',
                'setting_type' => 'number',
                'category' => 'security',
                'title' => '會話逾時時間',
                'description' => '用戶會話逾時時間（秒）',
                'validation_rules' => '{"min":300,"max":86400}',
                'is_public' => 0,
                'is_editable' => 1,
                'display_order' => 1,
            ],
            [
                'setting_key' => 'max_login_attempts',
                'setting_value' => '5',
                'setting_type' => 'number',
                'category' => 'security',
                'title' => '最大登入嘗試次數',
                'description' => '帳號鎖定前的最大登入嘗試次數',
                'validation_rules' => '{"min":3,"max":10}',
                'is_public' => 0,
                'is_editable' => 1,
                'display_order' => 2,
            ],
            [
                'setting_key' => 'account_lockout_time',
                'setting_value' => '1800',
                'setting_type' => 'number',
                'category' => 'security',
                'title' => '帳號鎖定時間',
                'description' => '帳號鎖定持續時間（秒）',
                'validation_rules' => '{"min":300,"max":7200}',
                'is_public' => 0,
                'is_editable' => 1,
                'display_order' => 3,
            ],
            [
                'setting_key' => 'voting_result_threshold',
                'setting_value' => '{"land_area": 0.5, "building_area": 0.5, "member_count": 0.5}',
                'setting_type' => 'json',
                'category' => 'voting',
                'title' => '投票通過門檻',
                'description' => '投票議題通過所需的各項門檻設定',
                'is_public' => 1,
                'is_editable' => 1,
                'display_order' => 1,
            ],
            [
                'setting_key' => 'meeting_quorum_threshold',
                'setting_value' => '{"land_area": 0.5, "building_area": 0.5, "member_count": 0.5}',
                'setting_type' => 'json',
                'category' => 'meeting',
                'title' => '會議成會門檻',
                'description' => '會議成會所需的各項門檻設定',
                'is_public' => 1,
                'is_editable' => 1,
                'display_order' => 1,
            ],
            [
                'setting_key' => 'file_upload_max_size',
                'setting_value' => '10485760',
                'setting_type' => 'number',
                'category' => 'file',
                'title' => '檔案上傳大小限制',
                'description' => '單一檔案上傳大小限制（位元組）',
                'validation_rules' => '{"min":1048576,"max":104857600}',
                'is_public' => 1,
                'is_editable' => 1,
                'display_order' => 1,
            ],
            [
                'setting_key' => 'allowed_file_types',
                'setting_value' => '["pdf","doc","docx","xls","xlsx","jpg","jpeg","png","gif"]',
                'setting_type' => 'json',
                'category' => 'file',
                'title' => '允許上傳檔案類型',
                'description' => '系統允許上傳的檔案副檔名清單',
                'is_public' => 1,
                'is_editable' => 1,
                'display_order' => 2,
            ],
        ];

        $builder = $this->db->table('system_settings');
        foreach ($defaultSettings as $setting) {
            $setting['created_at'] = date('Y-m-d H:i:s');
            $setting['updated_at'] = date('Y-m-d H:i:s');
            $builder->insert($setting);
        }
    }

    public function down()
    {
        $this->forge->dropTable('system_setting_history');
        $this->forge->dropTable('system_settings');
    }
}