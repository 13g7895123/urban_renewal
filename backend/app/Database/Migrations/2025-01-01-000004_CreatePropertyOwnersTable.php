<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePropertyOwnersTable extends Migration
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
            'owner_code' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => '所有權人編號(自動產生)',
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => '所有權人名稱',
            ],
            'id_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => '身分證字號',
            ],
            'phone1' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => '電話1',
            ],
            'phone2' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'comment' => '電話2',
            ],
            'contact_address' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '聯絡地址',
            ],
            'household_address' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => '戶籍地址',
            ],
            'exclusion_type' => [
                'type' => 'ENUM',
                'constraint' => ['法院囑託查封', '假扣押', '假處分', '破產登記', '未經繼承'],
                'null' => true,
                'comment' => '排除計算類型',
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
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('owner_code', 'unique_owner_code');
        $this->forge->addForeignKey('urban_renewal_id', 'urban_renewals', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey('urban_renewal_id', false, false, 'idx_urban_renewal_id');
        $this->forge->addKey('owner_code', false, false, 'idx_owner_code');
        $this->forge->addKey('name', false, false, 'idx_name');

        $this->forge->createTable('property_owners');
    }

    public function down()
    {
        $this->forge->dropTable('property_owners');
    }
}