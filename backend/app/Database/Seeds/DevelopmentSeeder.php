<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * 開發測試資料種子
 * 提供完整的測試資料用於本地開發和測試
 */
class DevelopmentSeeder extends Seeder
{
    public function run()
    {
        // 呼叫其他 Seeder
        $this->call('UserSeeder');
        $this->call('LocationSeeder');

        // 建立測試都市更新會
        $this->createUrbanRenewals();

        // 建立測試地籍資料
        $this->createLandPlotsAndBuildings();

        // 建立測試所有權人
        $this->createPropertyOwners();

        // 建立測試會議
        $this->createMeetings();

        // 建立測試投票議題
        $this->createVotingTopics();

        // 建立測試系統設定
        $this->createSystemSettings();
    }

    /**
     * 建立測試都市更新會
     */
    private function createUrbanRenewals()
    {
        $data = [
            [
                'name' => '測試都市更新會 A',
                'area' => 5000.50,
                'chairman_name' => '張三',
                'chairman_phone' => '0912-345-678',
                'chairman_email' => 'chairman.a@example.com',
                'county_code' => 'TPE',
                'district_code' => 'DA',
                'address' => '台北市大安區信義路四段1號',
                'status' => 'active',
                'established_date' => '2023-01-15',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => '測試都市更新會 B',
                'area' => 3500.75,
                'chairman_name' => '李四',
                'chairman_phone' => '0923-456-789',
                'chairman_email' => 'chairman.b@example.com',
                'county_code' => 'TPE',
                'district_code' => 'SH',
                'address' => '台北市松山區南京東路三段100號',
                'status' => 'active',
                'established_date' => '2023-06-20',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('urban_renewals')->insertBatch($data);
    }

    /**
     * 建立測試地籍資料
     */
    private function createLandPlotsAndBuildings()
    {
        // 土地資料
        $landData = [
            [
                'urban_renewal_id' => 1,
                'county_code' => 'TPE',
                'district_code' => 'DA',
                'section_code' => 'SEC001',
                'land_number' => '123',
                'land_sub_number' => '1',
                'area' => 500.00,
                'public_area_numerator' => 1,
                'public_area_denominator' => 10,
                'zoning' => 'residential',
                'usage_district' => '住宅區',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'urban_renewal_id' => 1,
                'county_code' => 'TPE',
                'district_code' => 'DA',
                'section_code' => 'SEC001',
                'land_number' => '124',
                'land_sub_number' => null,
                'area' => 350.50,
                'public_area_numerator' => 1,
                'public_area_denominator' => 15,
                'zoning' => 'residential',
                'usage_district' => '住宅區',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('land_plots')->insertBatch($landData);

        // 建物資料
        $buildingData = [
            [
                'urban_renewal_id' => 1,
                'county_code' => 'TPE',
                'district_code' => 'DA',
                'section_code' => 'SEC001',
                'building_number' => '501',
                'building_sub_number' => '1',
                'floor_number' => '3F',
                'building_area' => 120.00,
                'main_building_area' => 100.00,
                '附屬建物面積' => 20.00,
                'building_purpose' => '住宅',
                'building_structure' => '鋼筋混凝土',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('buildings')->insertBatch($buildingData);
    }

    /**
     * 建立測試所有權人
     */
    private function createPropertyOwners()
    {
        $data = [
            [
                'urban_renewal_id' => 1,
                'owner_code' => 'OWN001',
                'owner_name' => '王小明',
                'id_number' => 'A123456789',
                'phone' => '0912-111-111',
                'email' => 'wang@example.com',
                'address' => '台北市大安區復興南路一段10號',
                'ownership_type' => 'full',
                'is_agree' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'urban_renewal_id' => 1,
                'owner_code' => 'OWN002',
                'owner_name' => '陳小華',
                'id_number' => 'B234567890',
                'phone' => '0923-222-222',
                'email' => 'chen@example.com',
                'address' => '台北市大安區和平東路二段20號',
                'ownership_type' => 'full',
                'is_agree' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'urban_renewal_id' => 1,
                'owner_code' => 'OWN003',
                'owner_name' => '林小美',
                'id_number' => 'C345678901',
                'phone' => '0934-333-333',
                'email' => 'lin@example.com',
                'address' => '台北市大安區建國南路一段30號',
                'ownership_type' => 'shared',
                'is_agree' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('property_owners')->insertBatch($data);

        // 建立所有權關係
        $ownershipData = [
            [
                'owner_id' => 1,
                'land_plot_id' => 1,
                'ownership_numerator' => 1,
                'ownership_denominator' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'owner_id' => 2,
                'land_plot_id' => 1,
                'ownership_numerator' => 1,
                'ownership_denominator' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'owner_id' => 3,
                'land_plot_id' => 2,
                'ownership_numerator' => 1,
                'ownership_denominator' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('owner_land_ownerships')->insertBatch($ownershipData);
    }

    /**
     * 建立測試會議
     */
    private function createMeetings()
    {
        $data = [
            [
                'urban_renewal_id' => 1,
                'meeting_number' => 'M2024001',
                'meeting_type' => 'regular',
                'title' => '第一次會員大會',
                'meeting_date' => date('Y-m-d', strtotime('+7 days')),
                'meeting_time' => '14:00:00',
                'location' => '台北市大安區信義路四段1號會議室',
                'status' => 'scheduled',
                'quorum_numerator' => 1,
                'quorum_denominator' => 2,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'urban_renewal_id' => 1,
                'meeting_number' => 'M2024002',
                'meeting_type' => 'extraordinary',
                'title' => '臨時會員大會',
                'meeting_date' => date('Y-m-d', strtotime('+14 days')),
                'meeting_time' => '10:00:00',
                'location' => '台北市大安區信義路四段1號會議室',
                'status' => 'draft',
                'quorum_numerator' => 2,
                'quorum_denominator' => 3,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('meetings')->insertBatch($data);
    }

    /**
     * 建立測試投票議題
     */
    private function createVotingTopics()
    {
        $data = [
            [
                'meeting_id' => 1,
                'topic_number' => 'T001',
                'title' => '都市更新計畫案表決',
                'description' => '針對都市更新計畫進行表決',
                'voting_method' => 'absolute_majority',
                'calculation_base' => 'area',
                'status' => 'draft',
                'start_time' => null,
                'end_time' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'meeting_id' => 1,
                'topic_number' => 'T002',
                'title' => '預算案審議',
                'description' => '審議年度預算分配',
                'voting_method' => 'simple_majority',
                'calculation_base' => 'count',
                'status' => 'draft',
                'start_time' => null,
                'end_time' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('voting_topics')->insertBatch($data);
    }

    /**
     * 建立測試系統設定
     */
    private function createSystemSettings()
    {
        $data = [
            [
                'category' => 'system',
                'key' => 'system_name',
                'value' => '都更計票系統',
                'description' => '系統名稱',
                'is_public' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'category' => 'system',
                'key' => 'system_version',
                'value' => '1.0.0',
                'description' => '系統版本',
                'is_public' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'category' => 'security',
                'key' => 'max_login_attempts',
                'value' => '5',
                'description' => '最大登入嘗試次數',
                'is_public' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'category' => 'security',
                'key' => 'account_lockout_time',
                'value' => '1800',
                'description' => '帳號鎖定時間（秒）',
                'is_public' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'category' => 'notification',
                'key' => 'enable_email_notification',
                'value' => '1',
                'description' => '啟用電子郵件通知',
                'is_public' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('system_settings')->insertBatch($data);
    }
}
