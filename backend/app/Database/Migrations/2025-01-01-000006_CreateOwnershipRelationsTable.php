<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOwnershipRelationsTable extends Migration
{
    public function up()
    {
        // Building and Land Relations Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'building_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '建物ID',
            ],
            'land_plot_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '地號ID',
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
        $this->forge->addForeignKey('building_id', 'buildings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('land_plot_id', 'land_plots', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['building_id', 'land_plot_id'], 'unique_building_land');
        $this->forge->addKey('building_id', false, false, 'idx_building_id');
        $this->forge->addKey('land_plot_id', false, false, 'idx_land_plot_id');

        $this->forge->createTable('building_land_relations');

        // Owner Land Ownership Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'property_owner_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '所有權人ID',
            ],
            'land_plot_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '地號ID',
            ],
            'ownership_numerator' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '持有比例分子',
            ],
            'ownership_denominator' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '持有比例分母',
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
        $this->forge->addForeignKey('property_owner_id', 'property_owners', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('land_plot_id', 'land_plots', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['property_owner_id', 'land_plot_id'], 'unique_owner_land');
        $this->forge->addKey('property_owner_id', false, false, 'idx_property_owner_id');
        $this->forge->addKey('land_plot_id', false, false, 'idx_land_plot_id');

        $this->forge->createTable('owner_land_ownership');

        // Owner Building Ownership Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'property_owner_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '所有權人ID',
            ],
            'building_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '建物ID',
            ],
            'ownership_numerator' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '持有比例分子',
            ],
            'ownership_denominator' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => '持有比例分母',
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
        $this->forge->addForeignKey('property_owner_id', 'property_owners', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('building_id', 'buildings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['property_owner_id', 'building_id'], 'unique_owner_building');
        $this->forge->addKey('property_owner_id', false, false, 'idx_property_owner_id');
        $this->forge->addKey('building_id', false, false, 'idx_building_id');

        $this->forge->createTable('owner_building_ownership');
    }

    public function down()
    {
        $this->forge->dropTable('owner_building_ownership');
        $this->forge->dropTable('owner_land_ownership');
        $this->forge->dropTable('building_land_relations');
    }
}