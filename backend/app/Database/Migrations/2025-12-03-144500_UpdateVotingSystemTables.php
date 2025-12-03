<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateVotingSystemTables extends Migration
{
    public function up()
    {
        // Add columns to voting_topics
        $fields = [
            'is_anonymous' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '是否匿名',
            ],
            'max_selections' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
                'comment' => '最大有效圈選數',
            ],
            'accepted_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
                'comment' => '正取數量',
            ],
            'alternate_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '備取數量',
            ],
            'land_area_ratio_numerator' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '通過條件土地面積比例分子',
            ],
            'land_area_ratio_denominator' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '通過條件土地面積比例分母',
            ],
            'building_area_ratio_numerator' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '通過條件建物面積比例分子',
            ],
            'building_area_ratio_denominator' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '通過條件建物面積比例分母',
            ],
            'people_ratio_numerator' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '通過條件人數比例分子',
            ],
            'people_ratio_denominator' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'default' => 0.00,
                'comment' => '通過條件人數比例分母',
            ],
            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '備註',
            ],
        ];
        
        $this->forge->addColumn('voting_topics', $fields);

        // Create voting_options table
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
            'option_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => '選項名稱',
            ],
            'property_owner_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => '關聯的所有權人ID(可選)',
            ],
            'is_pinned' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '是否置頂',
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => '排序順序',
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
        $this->forge->addForeignKey('voting_topic_id', 'voting_topics', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('voting_topic_id', false, false, 'idx_voting_topic_id');
        
        $this->forge->createTable('voting_options');
    }

    public function down()
    {
        $this->forge->dropTable('voting_options');
        
        $columns = [
            'is_anonymous',
            'max_selections',
            'accepted_count',
            'alternate_count',
            'land_area_ratio_numerator',
            'land_area_ratio_denominator',
            'building_area_ratio_numerator',
            'building_area_ratio_denominator',
            'people_ratio_numerator',
            'people_ratio_denominator',
            'remarks'
        ];
        
        $this->forge->dropColumn('voting_topics', $columns);
    }
}
