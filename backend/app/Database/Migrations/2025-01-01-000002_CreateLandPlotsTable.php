<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLandPlotsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'urban_renewal_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => '更新會ID',
            ],
            'county' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => false,
                'comment'    => '縣市代碼',
            ],
            'district' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => false,
                'comment'    => '行政區代碼',
            ],
            'section' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => false,
                'comment'    => '段小段代碼',
            ],
            'land_number_main' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => false,
                'comment'    => '地號母號',
            ],
            'land_number_sub' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
                'comment'    => '地號子號',
            ],
            'land_area' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'comment'    => '土地面積(平方公尺)',
            ],
            'is_representative' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment'    => '是否為代表號',
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('urban_renewal_id');
        $this->forge->addKey(['county', 'district', 'section']);
        $this->forge->addKey('is_representative');
        $this->forge->addKey('created_at');

        // Add foreign key constraint
        $this->forge->addForeignKey('urban_renewal_id', 'urban_renewals', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('land_plots');
    }

    public function down()
    {
        $this->forge->dropTable('land_plots');
    }
}