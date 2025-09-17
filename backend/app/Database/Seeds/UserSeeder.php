<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'full_name' => '系統管理員',
                'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                'role' => 'admin',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'chairman',
                'email' => 'chairman@example.com',
                'full_name' => '主任委員',
                'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                'role' => 'chairman',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'member1',
                'email' => 'member1@example.com',
                'full_name' => '地主成員1',
                'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                'role' => 'member',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'observer1',
                'email' => 'observer1@example.com',
                'full_name' => '觀察員1',
                'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                'role' => 'observer',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Using query builder
        $this->db->table('users')->insertBatch($data);
    }
}