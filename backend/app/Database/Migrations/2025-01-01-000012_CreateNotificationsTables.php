<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTables extends Migration
{
    public function up()
    {
        // Notifications Table
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
                'null' => true,
                'comment' => '接收者用戶ID（NULL表示系統通知）',
            ],
            'notification_type' => [
                'type' => 'ENUM',
                'constraint' => [
                    'meeting_notice',
                    'meeting_reminder',
                    'voting_start',
                    'voting_end',
                    'voting_reminder',
                    'check_in_reminder',
                    'system_maintenance',
                    'user_account',
                    'document_upload',
                    'report_ready',
                    'permission_changed',
                    'system_alert'
                ],
                'comment' => '通知類型',
            ],
            'priority' => [
                'type' => 'ENUM',
                'constraint' => ['low', 'normal', 'high', 'urgent'],
                'default' => 'normal',
                'comment' => '通知優先級',
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => '通知標題',
            ],
            'message' => [
                'type' => 'TEXT',
                'comment' => '通知內容',
            ],
            'related_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '相關資源類型（meeting, voting_topic, urban_renewal等）',
            ],
            'related_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '相關資源ID',
            ],
            'action_url' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '點擊後導向的URL',
            ],
            'action_text' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '操作按鈕文字',
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '額外的通知元資料',
            ],
            'is_read' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '是否已讀',
            ],
            'read_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '已讀時間',
            ],
            'is_global' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '是否為全域通知',
            ],
            'urban_renewal_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '限定更新會範圍（NULL表示全系統）',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '通知過期時間',
            ],
            'send_email' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '是否發送郵件通知',
            ],
            'email_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '郵件發送時間',
            ],
            'send_sms' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '是否發送簡訊通知',
            ],
            'sms_sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '簡訊發送時間',
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '通知建立者ID（系統生成為NULL）',
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
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey('user_id', false, false, 'idx_user_id');
        $this->forge->addKey('notification_type', false, false, 'idx_notification_type');
        $this->forge->addKey('priority', false, false, 'idx_priority');
        $this->forge->addKey('is_read', false, false, 'idx_is_read');
        $this->forge->addKey('is_global', false, false, 'idx_is_global');
        $this->forge->addKey('urban_renewal_id', false, false, 'idx_urban_renewal_id');
        $this->forge->addKey('expires_at', false, false, 'idx_expires_at');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');
        $this->forge->addKey(['user_id', 'is_read'], false, false, 'idx_user_read');
        $this->forge->addKey(['related_type', 'related_id'], false, false, 'idx_related');

        $this->forge->createTable('notifications');

        // Notification Templates Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'template_key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
                'comment' => '範本識別鍵',
            ],
            'notification_type' => [
                'type' => 'ENUM',
                'constraint' => [
                    'meeting_notice',
                    'meeting_reminder',
                    'voting_start',
                    'voting_end',
                    'voting_reminder',
                    'check_in_reminder',
                    'system_maintenance',
                    'user_account',
                    'document_upload',
                    'report_ready',
                    'permission_changed',
                    'system_alert'
                ],
                'comment' => '適用通知類型',
            ],
            'template_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => '範本名稱',
            ],
            'title_template' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => '標題範本（支援變數替換）',
            ],
            'message_template' => [
                'type' => 'TEXT',
                'comment' => '內容範本（支援變數替換）',
            ],
            'email_subject_template' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '郵件主旨範本',
            ],
            'email_body_template' => [
                'type' => 'LONGTEXT',
                'null' => true,
                'comment' => '郵件內容範本（HTML格式）',
            ],
            'sms_template' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '簡訊內容範本',
            ],
            'variables' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '可用變數說明',
            ],
            'default_priority' => [
                'type' => 'ENUM',
                'constraint' => ['low', 'normal', 'high', 'urgent'],
                'default' => 'normal',
                'comment' => '預設優先級',
            ],
            'auto_send_email' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '是否自動發送郵件',
            ],
            'auto_send_sms' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '是否自動發送簡訊',
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '範本是否啟用',
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
        $this->forge->addUniqueKey('template_key', 'unique_template_key');
        $this->forge->addKey('notification_type', false, false, 'idx_notification_type');
        $this->forge->addKey('is_active', false, false, 'idx_is_active');

        $this->forge->createTable('notification_templates');

        // User Notification Preferences Table
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
            'notification_type' => [
                'type' => 'ENUM',
                'constraint' => [
                    'meeting_notice',
                    'meeting_reminder',
                    'voting_start',
                    'voting_end',
                    'voting_reminder',
                    'check_in_reminder',
                    'system_maintenance',
                    'user_account',
                    'document_upload',
                    'report_ready',
                    'permission_changed',
                    'system_alert'
                ],
                'comment' => '通知類型',
            ],
            'web_enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '是否接收網頁通知',
            ],
            'email_enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '是否接收郵件通知',
            ],
            'sms_enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '是否接收簡訊通知',
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
        $this->forge->addUniqueKey(['user_id', 'notification_type'], 'unique_user_notification_type');
        $this->forge->addKey('user_id', false, false, 'idx_user_id');
        $this->forge->addKey('notification_type', false, false, 'idx_notification_type');

        $this->forge->createTable('user_notification_preferences');

        // Insert default notification templates
        $defaultTemplates = [
            [
                'template_key' => 'meeting_notice_default',
                'notification_type' => 'meeting_notice',
                'template_name' => '會議通知預設範本',
                'title_template' => '【會議通知】{{meeting_name}}',
                'message_template' => '{{urban_renewal_name}} 將於 {{meeting_date}} {{meeting_time}} 在 {{meeting_location}} 舉行 {{meeting_name}}，請準時出席。',
                'email_subject_template' => '【{{urban_renewal_name}}】{{meeting_name}} 會議通知',
                'email_body_template' => '<h2>會議通知</h2><p>親愛的會員您好：</p><p>{{urban_renewal_name}} 將於 <strong>{{meeting_date}} {{meeting_time}}</strong> 在 <strong>{{meeting_location}}</strong> 舉行 <strong>{{meeting_name}}</strong>。</p><p>請準時出席，謝謝。</p>',
                'sms_template' => '【{{urban_renewal_name}}】{{meeting_name}} 將於{{meeting_date}} {{meeting_time}}在{{meeting_location}}舉行，請準時出席。',
                'default_priority' => 'high',
                'auto_send_email' => 1,
                'auto_send_sms' => 0,
            ],
            [
                'template_key' => 'voting_start_default',
                'notification_type' => 'voting_start',
                'template_name' => '投票開始預設範本',
                'title_template' => '【投票開始】{{topic_title}}',
                'message_template' => '{{meeting_name}} 的投票議題「{{topic_title}}」現在開始投票，請儘快參與投票。',
                'email_subject_template' => '【投票通知】{{topic_title}} 開始投票',
                'email_body_template' => '<h2>投票通知</h2><p>親愛的會員您好：</p><p>{{meeting_name}} 的投票議題「<strong>{{topic_title}}</strong>」現在開始投票。</p><p>請登入系統參與投票，謝謝。</p>',
                'sms_template' => '【投票通知】{{topic_title}} 開始投票，請登入系統參與投票。',
                'default_priority' => 'high',
                'auto_send_email' => 1,
                'auto_send_sms' => 1,
            ],
            [
                'template_key' => 'system_maintenance_default',
                'notification_type' => 'system_maintenance',
                'template_name' => '系統維護預設範本',
                'title_template' => '【系統維護】{{maintenance_title}}',
                'message_template' => '系統將於 {{maintenance_start}} 至 {{maintenance_end}} 進行維護，期間將無法使用系統功能。',
                'email_subject_template' => '【系統維護通知】{{maintenance_title}}',
                'email_body_template' => '<h2>系統維護通知</h2><p>親愛的用戶您好：</p><p>系統將於 <strong>{{maintenance_start}}</strong> 至 <strong>{{maintenance_end}}</strong> 進行維護。</p><p>維護期間將無法使用系統功能，造成不便敬請見諒。</p>',
                'default_priority' => 'normal',
                'auto_send_email' => 1,
                'auto_send_sms' => 0,
            ],
        ];

        $builder = $this->db->table('notification_templates');
        foreach ($defaultTemplates as $template) {
            $template['created_at'] = date('Y-m-d H:i:s');
            $template['updated_at'] = date('Y-m-d H:i:s');
            $builder->insert($template);
        }
    }

    public function down()
    {
        $this->forge->dropTable('user_notification_preferences');
        $this->forge->dropTable('notification_templates');
        $this->forge->dropTable('notifications');
    }
}