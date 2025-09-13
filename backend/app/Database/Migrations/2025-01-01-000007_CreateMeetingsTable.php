<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMeetingsTable extends Migration
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
            'urban_renewal_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '所屬更新會ID',
            ],
            'meeting_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => '會議名稱',
            ],
            'meeting_type' => [
                'type' => 'ENUM',
                'constraint' => ['會員大會', '理事會', '監事會', '臨時會議'],
                'default' => '會員大會',
                'comment' => '會議類型',
            ],
            'meeting_date' => [
                'type' => 'DATE',
                'comment' => '會議日期',
            ],
            'meeting_time' => [
                'type' => 'TIME',
                'comment' => '會議時間',
            ],
            'meeting_location' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '開會地點',
            ],
            // 出席統計
            'attendee_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => '出席人數',
            ],
            'calculated_total_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => '納入計算總人數',
            ],
            'observer_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => '列席總人數',
            ],
            // 成會門檻設定
            'quorum_land_area_numerator' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => '成會土地面積比例分子',
            ],
            'quorum_land_area_denominator' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
                'comment' => '成會土地面積比例分母',
            ],
            'quorum_land_area' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '成會土地面積(平方公尺)',
            ],
            'quorum_building_area_numerator' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => '成會建物面積比例分子',
            ],
            'quorum_building_area_denominator' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
                'comment' => '成會建物面積比例分母',
            ],
            'quorum_building_area' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '成會建物面積(平方公尺)',
            ],
            'quorum_member_numerator' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => '成會人數比例分子',
            ],
            'quorum_member_denominator' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
                'comment' => '成會人數比例分母',
            ],
            'quorum_member_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
                'comment' => '成會人數',
            ],
            // 會議狀態
            'meeting_status' => [
                'type' => 'ENUM',
                'constraint' => ['draft', 'scheduled', 'in_progress', 'completed', 'cancelled'],
                'default' => 'draft',
                'comment' => '會議狀態',
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
        $this->forge->addForeignKey('urban_renewal_id', 'urban_renewals', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('urban_renewal_id', false, false, 'idx_urban_renewal_id');
        $this->forge->addKey('meeting_date', false, false, 'idx_meeting_date');
        $this->forge->addKey('meeting_status', false, false, 'idx_meeting_status');
        $this->forge->addKey(['meeting_date', 'meeting_status'], false, false, 'idx_meeting_date_status');

        $this->forge->createTable('meetings');
    }

    public function down()
    {
        $this->forge->dropTable('meetings');
    }
}