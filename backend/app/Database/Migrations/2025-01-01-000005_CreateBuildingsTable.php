<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBuildingsTable extends Migration
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
            'county' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'comment' => '縣市',
            ],
            'district' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'comment' => '行政區',
            ],
            'section' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'comment' => '段小段',
            ],
            'building_number_main' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'comment' => '建號母號',
            ],
            'building_number_sub' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => '000',
                'comment' => '建號子號',
            ],
            'building_area' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => true,
                'comment' => '建物總面積(平方公尺)',
            ],
            'building_address' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => '建物門牌',
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
        $this->forge->addKey(['building_number_main', 'building_number_sub'], false, false, 'idx_building_number');
        $this->forge->addKey(['county', 'district', 'section'], false, false, 'idx_location');

        $this->forge->createTable('buildings');
    }

    public function down()
    {
        $this->forge->dropTable('buildings');
    }
}