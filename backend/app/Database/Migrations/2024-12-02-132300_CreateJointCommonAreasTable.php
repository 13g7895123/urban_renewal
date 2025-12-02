<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJointCommonAreasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'urban_renewal_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'county' => [
                'type' => 'VARCHAR',
                'constraint' => 10
            ],
            'district' => [
                'type' => 'VARCHAR',
                'constraint' => 10
            ],
            'section' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ],
            'building_number_main' => [
                'type' => 'VARCHAR',
                'constraint' => 10
            ],
            'building_number_sub' => [
                'type' => 'VARCHAR',
                'constraint' => 10
            ],
            'building_total_area' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'null' => true
            ],
            'corresponding_building_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true
            ],
            'ownership_numerator' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ],
            'ownership_denominator' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('urban_renewal_id');
        $this->forge->addKey('corresponding_building_id');
        $this->forge->createTable('joint_common_areas');
    }

    public function down()
    {
        $this->forge->dropTable('joint_common_areas');
    }
}
