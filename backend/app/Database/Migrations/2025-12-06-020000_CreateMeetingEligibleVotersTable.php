<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMeetingEligibleVotersTable extends Migration
{
    public function up()
    {
        // 會議合格投票人快照表
        // 用於記錄會議建立當下的合格投票人名單
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
            // 快照當下的所有權人資料
            'owner_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => '所有權人姓名 (快照)',
            ],
            'owner_code' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => '所有權人編號 (快照)',
            ],
            'id_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => '身分證字號 (快照)',
            ],
            // 快照當下的權重資料
            'land_area_weight' => [
                'type' => 'DECIMAL',
                'constraint' => '20,4',
                'default' => 0,
                'comment' => '土地面積權重 (快照)',
            ],
            'building_area_weight' => [
                'type' => 'DECIMAL',
                'constraint' => '20,4',
                'default' => 0,
                'comment' => '建物面積權重 (快照)',
            ],
            'snapshot_at' => [
                'type' => 'DATETIME',
                'comment' => '快照時間',
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('meeting_id');
        $this->forge->addKey('property_owner_id');
        $this->forge->addUniqueKey(['meeting_id', 'property_owner_id'], 'unique_meeting_owner');

        // 外鍵約束
        $this->forge->addForeignKey('meeting_id', 'meetings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('property_owner_id', 'property_owners', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('meeting_eligible_voters', true);

        // 加入表註解
        $this->db->query("ALTER TABLE `meeting_eligible_voters` COMMENT '會議合格投票人快照表 - 記錄會議建立當下的合格投票人名單與權重'");
    }

    public function down()
    {
        $this->forge->dropTable('meeting_eligible_voters', true);
    }
}
