<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVotingChoicesTable extends Migration
{
    public function up()
    {
        // Create voting_choices table for voting options like "同意/不同意"
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
            'choice_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => '選項名稱',
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

        $this->forge->createTable('voting_choices');
    }

    public function down()
    {
        $this->forge->dropTable('voting_choices');
    }
}
