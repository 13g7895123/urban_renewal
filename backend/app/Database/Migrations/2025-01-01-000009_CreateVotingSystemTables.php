<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVotingSystemTables extends Migration
{
    public function up()
    {
        // Voting Topics Table
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
            'topic_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => '議題編號',
            ],
            'topic_title' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'comment' => '議題標題',
            ],
            'topic_description' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '議題描述',
            ],
            'voting_method' => [
                'type' => 'ENUM',
                'constraint' => ['simple_majority', 'absolute_majority', 'two_thirds_majority', 'unanimous'],
                'default' => 'simple_majority',
                'comment' => '投票方式',
            ],
            // 投票統計
            'total_votes' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => '總票數',
            ],
            'agree_votes' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => '同意票數',
            ],
            'disagree_votes' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => '不同意票數',
            ],
            'abstain_votes' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => '棄權票數',
            ],
            // 面積權重投票統計
            'total_land_area' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '總土地面積',
            ],
            'agree_land_area' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '同意土地面積',
            ],
            'disagree_land_area' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '不同意土地面積',
            ],
            'abstain_land_area' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '棄權土地面積',
            ],
            'total_building_area' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '總建物面積',
            ],
            'agree_building_area' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '同意建物面積',
            ],
            'disagree_building_area' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '不同意建物面積',
            ],
            'abstain_building_area' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '棄權建物面積',
            ],
            'voting_result' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'passed', 'failed', 'withdrawn'],
                'default' => 'pending',
                'comment' => '投票結果',
            ],
            'voting_status' => [
                'type' => 'ENUM',
                'constraint' => ['draft', 'active', 'closed'],
                'default' => 'draft',
                'comment' => '議題狀態',
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
        $this->forge->addKey('topic_number', false, false, 'idx_topic_number');
        $this->forge->addKey('voting_status', false, false, 'idx_voting_status');
        $this->forge->addKey('voting_result', false, false, 'idx_voting_result');

        $this->forge->createTable('voting_topics');

        // Voting Records Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'voting_topic_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '投票議題ID',
            ],
            'property_owner_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '所有權人ID',
            ],
            'vote_choice' => [
                'type' => 'ENUM',
                'constraint' => ['agree', 'disagree', 'abstain'],
                'comment' => '投票選擇',
            ],
            'vote_time' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => '投票時間',
            ],
            'voter_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => '投票人姓名(快照)',
            ],
            // 投票權重(快照資料)
            'land_area_weight' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '土地面積權重',
            ],
            'building_area_weight' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '建物面積權重',
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
        $this->forge->addForeignKey('voting_topic_id', 'voting_topics', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('property_owner_id', 'property_owners', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['voting_topic_id', 'property_owner_id'], 'unique_topic_owner');
        $this->forge->addKey('voting_topic_id', false, false, 'idx_voting_topic_id');
        $this->forge->addKey('property_owner_id', false, false, 'idx_property_owner_id');
        $this->forge->addKey('vote_choice', false, false, 'idx_vote_choice');
        $this->forge->addKey('vote_time', false, false, 'idx_vote_time');

        $this->forge->createTable('voting_records');

        // Meeting Documents Table
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
            'document_type' => [
                'type' => 'ENUM',
                'constraint' => ['agenda', 'minutes', 'attendance', 'notice', 'other'],
                'comment' => '文件類型',
            ],
            'document_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => '文件名稱',
            ],
            'file_path' => [
                'type' => 'TEXT',
                'comment' => '檔案路徑',
            ],
            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => '原始檔名',
            ],
            'file_size' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '檔案大小(bytes)',
            ],
            'mime_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'MIME類型',
            ],
            'uploaded_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '上傳者ID(未來擴展)',
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
        $this->forge->addKey('document_type', false, false, 'idx_document_type');

        $this->forge->createTable('meeting_documents');

        // Meeting Logs Table
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
            'action_type' => [
                'type' => 'ENUM',
                'constraint' => ['create', 'update', 'delete', 'check_in', 'vote', 'export'],
                'comment' => '操作類型',
            ],
            'action_description' => [
                'type' => 'TEXT',
                'comment' => '操作描述',
            ],
            'related_table' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => '相關資料表',
            ],
            'related_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '相關記錄ID',
            ],
            'operator_info' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => '操作者資訊',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => 'IP位址',
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '瀏覽器資訊',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('meeting_id', 'meetings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('meeting_id', false, false, 'idx_meeting_id');
        $this->forge->addKey('action_type', false, false, 'idx_action_type');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');
        $this->forge->addKey(['related_table', 'related_id'], false, false, 'idx_related_table_id');

        $this->forge->createTable('meeting_logs');
    }

    public function down()
    {
        $this->forge->dropTable('meeting_logs');
        $this->forge->dropTable('meeting_documents');
        $this->forge->dropTable('voting_records');
        $this->forge->dropTable('voting_topics');
    }
}