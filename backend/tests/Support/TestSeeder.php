<?php

namespace Tests\Support;

use CodeIgniter\Database\Seeder;

/**
 * 測試資料 Seeder
 * 用於在測試中快速建立測試資料
 */
class TestSeeder extends Seeder
{
    public function run()
    {
        // 建立測試企業
        $companyModel = model('CompanyModel');
        $companyId = $companyModel->insert([
            'name' => 'Test Company',
            'tax_id' => '12345678',
            'contact_person' => 'Test Contact',
            'contact_phone' => '02-12345678',
            'contact_email' => 'test@company.com',
            'address' => 'Test Address',
            'is_active' => 1,
        ]);

        // 建立測試更新會
        $urbanRenewalModel = model('UrbanRenewalModel');
        $renewalId = $urbanRenewalModel->insert([
            'company_id' => $companyId,
            'name' => 'Test Urban Renewal',
            'area' => 1500.50,
            'address' => 'Test Renewal Address',
        ]);

        // 建立測試使用者
        $userModel = model('UserModel');
        
        // 管理員
        $userModel->insert([
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'full_name' => 'Admin User',
            'phone' => '0912345678',
            'user_type' => 'general',
            'role' => 'admin',
            'is_active' => 1,
        ]);

        // 企業管理者
        $userModel->insert([
            'username' => 'company_manager',
            'email' => 'manager@test.com',
            'password_hash' => password_hash('manager123', PASSWORD_DEFAULT),
            'full_name' => 'Company Manager',
            'phone' => '0912345679',
            'user_type' => 'general',
            'role' => 'admin',
            'is_company_manager' => 1,
            'company_id' => $companyId,
            'is_active' => 1,
        ]);

        // 一般會員
        $userModel->insert([
            'username' => 'member',
            'email' => 'member@test.com',
            'password_hash' => password_hash('member123', PASSWORD_DEFAULT),
            'full_name' => 'Member User',
            'phone' => '0912345680',
            'user_type' => 'general',
            'role' => 'member',
            'is_active' => 1,
        ]);

        // 建立測試所有權人
        $propertyOwnerModel = model('PropertyOwnerModel');
        for ($i = 1; $i <= 5; $i++) {
            $propertyOwnerModel->insert([
                'urban_renewal_id' => $renewalId,
                'name' => "Test Owner $i",
                'id_number' => "A12345678$i",
                'phone1' => "0912345$i$i$i",
                'contact_address' => "Test Address $i",
            ]);
        }

        // 建立測試會議
        $meetingModel = model('MeetingModel');
        $meetingId = $meetingModel->insert([
            'urban_renewal_id' => $renewalId,
            'meeting_type' => '會員大會',
            'meeting_date' => date('Y-m-d', strtotime('+7 days')),
            'meeting_time' => '14:00:00',
            'location' => 'Test Meeting Room',
            'status' => 'scheduled',
        ]);

        // 建立測試投票議題
        $votingTopicModel = model('VotingTopicModel');
        $votingTopicModel->insert([
            'meeting_id' => $meetingId,
            'topic_title' => 'Test Voting Topic',
            'topic_description' => 'This is a test voting topic',
            'voting_method' => 'simple_majority',
            'status' => 'open',
        ]);
    }
}
