<?php

namespace Tests\Feature\RBAC;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Fixtures\UserFixture;
use Tests\Support\Fixtures\UrbanRenewalFixture;
use Tests\Support\Fixtures\MeetingFixture;

/**
 * 理事長權限測試
 * 驗收情境 2、6、7
 */
class ChairmanPermissionsTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate     = false;
    protected $migrateOnce = false;
    protected $refresh     = false;
    protected $namespace   = null;

    protected $userFixture;
    protected $urbanRenewalFixture;
    protected $meetingFixture;
    protected $chairman;
    protected $chairmanToken;
    protected $urbanRenewal1;
    protected $urbanRenewal2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userFixture = new UserFixture();
        $this->urbanRenewalFixture = new UrbanRenewalFixture();
        $this->meetingFixture = new MeetingFixture();

        // Create two urban renewal projects
        $this->urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $this->urbanRenewal2 = $this->urbanRenewalFixture->createActive();

        // Create chairman assigned to urbanRenewal1
        $this->chairman = $this->userFixture->createChairman($this->urbanRenewal1['id']);
        $this->chairmanToken = $this->userFixture->generateToken($this->chairman);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Fixtures will auto-cleanup via __destruct
    }

    /**
     * 驗收情境 2: 理事長登入後，僅能查看其 urban_renewal_id 的會議和資源
     */
    public function testChairmanCanOnlyAccessAssignedUrbanRenewal()
    {
        // Create meetings in both urban renewals
        $meeting1 = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->chairman['id']);
        $meeting2 = $this->meetingFixture->create($this->urbanRenewal2['id'], $this->chairman['id']);

        // Chairman should access meeting1 (their urban_renewal_id)
        $result1 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->get('/api/meetings/' . $meeting1['id']);

        $result1->assertStatus(200);

        // Chairman should NOT access meeting2 (different urban_renewal_id)
        $result2 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->get('/api/meetings/' . $meeting2['id']);

        $result2->assertStatus(403);
        $data = json_decode($result2->getJSON(), true);
        $this->assertFalse($data['success']);
    }

    /**
     * 驗收情境 2: 理事長查看會議列表時只顯示其 urban_renewal_id 的會議
     */
    public function testChairmanMeetingListOnlyShowsAssignedUrbanRenewal()
    {
        // Create multiple meetings
        $meeting1 = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->chairman['id'], [
            'title' => '理事長的會議1'
        ]);
        $meeting2 = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->chairman['id'], [
            'title' => '理事長的會議2'
        ]);
        $meeting3 = $this->meetingFixture->create($this->urbanRenewal2['id'], $this->chairman['id'], [
            'title' => '其他地區的會議'
        ]);

        // Get meeting list
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->get('/api/meetings');

        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);

        // Should only see meetings from urbanRenewal1
        $meetingIds = array_column($data['data'], 'id');
        $this->assertContains($meeting1['id'], $meetingIds);
        $this->assertContains($meeting2['id'], $meetingIds);
        $this->assertNotContains($meeting3['id'], $meetingIds);
    }

    /**
     * 驗收情境 6: 理事長可以建立、編輯、刪除其 urban_renewal_id 的會議
     */
    public function testChairmanCanCreateMeetingInAssignedUrbanRenewal()
    {
        $meetingData = [
            'urban_renewal_id' => $this->urbanRenewal1['id'],
            'meeting_type' => 'general',
            'title' => '理事長建立的會議',
            'description' => '測試理事長建立會議',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 week')),
            'location' => '會議室A'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->withBodyFormat('json')
            ->post('/api/meetings', $meetingData);

        $result->assertStatus(201);
        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('理事長建立的會議', $data['data']['title']);
    }

    /**
     * 驗收情境 6: 理事長無法建立其他 urban_renewal_id 的會議
     */
    public function testChairmanCannotCreateMeetingInOtherUrbanRenewal()
    {
        $meetingData = [
            'urban_renewal_id' => $this->urbanRenewal2['id'], // Different urban_renewal_id
            'meeting_type' => 'general',
            'title' => '嘗試建立其他地區的會議',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 week')),
            'location' => '會議室B'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->withBodyFormat('json')
            ->post('/api/meetings', $meetingData);

        $result->assertStatus(403);
        $data = json_decode($result->getJSON(), true);
        $this->assertFalse($data['success']);
    }

    /**
     * 驗收情境 6: 理事長可以編輯其 urban_renewal_id 的會議
     */
    public function testChairmanCanUpdateMeetingInAssignedUrbanRenewal()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->chairman['id']);

        $updateData = [
            'title' => '更新後的會議標題',
            'description' => '理事長更新的內容'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->withBodyFormat('json')
            ->put('/api/meetings/' . $meeting['id'], $updateData);

        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('更新後的會議標題', $data['data']['title']);
    }

    /**
     * 驗收情境 6: 理事長無法編輯其他 urban_renewal_id 的會議
     */
    public function testChairmanCannotUpdateMeetingInOtherUrbanRenewal()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal2['id'], $this->chairman['id']);

        $updateData = [
            'title' => '嘗試更新其他地區的會議'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->withBodyFormat('json')
            ->put('/api/meetings/' . $meeting['id'], $updateData);

        $result->assertStatus(403);
    }

    /**
     * 驗收情境 6: 理事長可以刪除其 urban_renewal_id 的會議
     */
    public function testChairmanCanDeleteMeetingInAssignedUrbanRenewal()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->chairman['id']);

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->delete('/api/meetings/' . $meeting['id']);

        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
    }

    /**
     * 驗收情境 6: 理事長無法刪除其他 urban_renewal_id 的會議
     */
    public function testChairmanCannotDeleteMeetingInOtherUrbanRenewal()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal2['id'], $this->chairman['id']);

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->delete('/api/meetings/' . $meeting['id']);

        $result->assertStatus(403);
    }

    /**
     * 驗收情境 7: 理事長可以管理其 urban_renewal_id 的使用者（建立、編輯、停用）
     */
    public function testChairmanCanCreateUserInAssignedUrbanRenewal()
    {
        $userData = [
            'username' => 'new_member_' . time(),
            'password' => 'password123',
            'email' => 'newmember@test.com',
            'role' => 'member',
            'urban_renewal_id' => $this->urbanRenewal1['id'],
            'full_name' => '新會員'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->withBodyFormat('json')
            ->post('/api/users', $userData);

        $result->assertStatus(201);
        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('member', $data['data']['role']);
    }

    /**
     * 驗收情境 7: 理事長無法建立其他 urban_renewal_id 的使用者
     */
    public function testChairmanCannotCreateUserInOtherUrbanRenewal()
    {
        $userData = [
            'username' => 'other_member_' . time(),
            'password' => 'password123',
            'email' => 'othermember@test.com',
            'role' => 'member',
            'urban_renewal_id' => $this->urbanRenewal2['id'], // Different urban_renewal_id
            'full_name' => '其他地區會員'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->withBodyFormat('json')
            ->post('/api/users', $userData);

        $result->assertStatus(403);
    }

    /**
     * 驗收情境 7: 理事長無法建立管理員帳號
     */
    public function testChairmanCannotCreateAdminUser()
    {
        $userData = [
            'username' => 'new_admin_' . time(),
            'password' => 'admin123',
            'email' => 'newadmin@test.com',
            'role' => 'admin', // Admin role
            'full_name' => '嘗試建立管理員'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->withBodyFormat('json')
            ->post('/api/users', $userData);

        $result->assertStatus(403);
        $data = json_decode($result->getJSON(), true);
        $this->assertFalse($data['success']);
    }

    /**
     * 驗收情境 7: 理事長可以查看其 urban_renewal_id 的使用者列表
     */
    public function testChairmanCanListUsersInAssignedUrbanRenewal()
    {
        // Create users in both urban renewals
        $member1 = $this->userFixture->createMember($this->urbanRenewal1['id']);
        $member2 = $this->userFixture->createMember($this->urbanRenewal2['id']);

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->chairmanToken
            ])
            ->get('/api/users');

        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);

        // Should only see users from urbanRenewal1
        $userIds = array_column($data['data']['users'], 'id');
        $this->assertContains($member1['id'], $userIds);
        $this->assertNotContains($member2['id'], $userIds);
    }
}
