<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMeetingManagementTables extends Migration
{
    public function up()
    {
        // Meeting Notices Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'meeting_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '會議ID',
            ],
            'document_number_prefix' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '發文字號前綴',
            ],
            'document_number_middle' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '字第',
            ],
            'document_number_suffix' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '號碼',
            ],
            'document_number_postfix' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '號後綴',
            ],
            'chairman_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => '理事長姓名',
            ],
            'contact_person' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => '聯絡人姓名',
            ],
            'contact_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => '聯絡人電話',
            ],
            'attachment_path' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '附件路徑',
            ],
            'attachment_filename' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '附件檔名',
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
        $this->forge->addForeignKey('meeting_id', 'meetings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('meeting_id', false, false, 'idx_meeting_id');

        $this->forge->createTable('meeting_notices');

        // Meeting Notice Descriptions Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'meeting_notice_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '會議通知ID',
            ],
            'sequence_number' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '序號',
            ],
            'description_text' => [
                'type' => 'TEXT',
                'comment' => '說明內容',
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
        $this->forge->addForeignKey('meeting_notice_id', 'meeting_notices', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('meeting_notice_id', false, false, 'idx_meeting_notice_id');
        $this->forge->addKey(['meeting_notice_id', 'sequence_number'], false, false, 'idx_sequence');

        $this->forge->createTable('meeting_notice_descriptions');

        // Meeting Attendances Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'meeting_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '會議ID',
            ],
            'property_owner_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '所有權人ID',
            ],
            'attendance_type' => [
                'type' => 'ENUM',
                'constraint' => ['present', 'proxy', 'absent'],
                'default' => 'absent',
                'comment' => '出席類型',
            ],
            'proxy_person' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => '代理人姓名',
            ],
            'check_in_time' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '簽到時間',
            ],
            'is_calculated' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '是否納入計算',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '備註',
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
        $this->forge->addForeignKey('meeting_id', 'meetings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('property_owner_id', 'property_owners', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['meeting_id', 'property_owner_id'], 'unique_meeting_owner');
        $this->forge->addKey('meeting_id', false, false, 'idx_meeting_id');
        $this->forge->addKey('property_owner_id', false, false, 'idx_property_owner_id');
        $this->forge->addKey('attendance_type', false, false, 'idx_attendance_type');
        $this->forge->addKey('check_in_time', false, false, 'idx_check_in_time');

        $this->forge->createTable('meeting_attendances');

        // Meeting Observers Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'meeting_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '會議ID',
            ],
            'observer_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => '列席者姓名',
            ],
            'observer_title' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => '職稱',
            ],
            'observer_organization' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
                'comment' => '所屬機關/單位',
            ],
            'contact_phone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => '聯絡電話',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '備註',
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
        $this->forge->addForeignKey('meeting_id', 'meetings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('meeting_id', false, false, 'idx_meeting_id');
        $this->forge->addKey('observer_name', false, false, 'idx_observer_name');

        $this->forge->createTable('meeting_observers');
    }

    public function down()
    {
        $this->forge->dropTable('meeting_observers');
        $this->forge->dropTable('meeting_attendances');
        $this->forge->dropTable('meeting_notice_descriptions');
        $this->forge->dropTable('meeting_notices');
    }
}