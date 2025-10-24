<?php

namespace Tests\Feature\RBAC;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Fixtures\UserFixture;
use Tests\Support\Fixtures\UrbanRenewalFixture;
use Tests\Support\Fixtures\MeetingFixture;

/**
 * 跨更新地區存取測試
 * 驗收情境 3、US4 驗收情境 5
 */
class CrossUrbanRenewalAccessTest extends CIUnitTestCase
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->userFixture = new UserFixture();
        $this->urbanRenewalFixture = new UrbanRenewalFixture();
        $this->meetingFixture = new MeetingFixture();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Fixtures will auto-cleanup via __destruct
    }

    /**
     * 驗收情境 3: 確認使用者跨 urban_renewal_id 存取會議時被阻擋
     */
    public function testUserCannotAccessMeetingFromDifferentUrbanRenewal()
    {
        // Create two urban renewals
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal2 = $this->urbanRenewalFixture->createActive();

        // Create chairman in urbanRenewal1
        $chairman1 = $this->userFixture->createChairman($urbanRenewal1['id']);
        $chairman1Token = $this->userFixture->generateToken($chairman1);

        // Create meeting in urbanRenewal2
        $meeting2 = $this->meetingFixture->create($urbanRenewal2['id'], $chairman1['id']);

        // Chairman1 tries to access meeting in urbanRenewal2
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $chairman1Token
            ])
            ->get('/api/meetings/' . $meeting2['id']);

        $result->assertStatus(403);
        $data = json_decode($result->getJSON(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('403', $data['error']['code']);
    }

    /**
     * 驗收情境 3: 會員嘗試讀取其他 urban_renewal_id 的投票議題被拒絕
     */
    public function testMemberCannotAccessVotingTopicFromDifferentUrbanRenewal()
    {
        // Create two urban renewals
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal2 = $this->urbanRenewalFixture->createActive();

        // Create member in urbanRenewal1
        $member1 = $this->userFixture->createMember($urbanRenewal1['id']);
        $member1Token = $this->userFixture->generateToken($member1);

        // Create meeting and voting topic in urbanRenewal2
        $meeting2 = $this->meetingFixture->create($urbanRenewal2['id'], $member1['id']);
        $votingTopicModel = model('VotingTopicModel');
        $topicId = $votingTopicModel->insert([
            'meeting_id' => $meeting2['id'],
            'title' => '其他地區投票議題',
            'voting_type' => 'simple_majority',
            'status' => 'open'
        ]);

        // Member1 tries to access voting topic in urbanRenewal2
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $member1Token
            ])
            ->get('/api/voting/topics/' . $topicId);

        $result->assertStatus(403);
    }

    /**
     * 驗收情境 3: 觀察員嘗試讀取其他 urban_renewal_id 的文件被拒絕
     */
    public function testObserverCannotAccessDocumentFromDifferentUrbanRenewal()
    {
        // Create two urban renewals
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal2 = $this->urbanRenewalFixture->createActive();

        // Create observer in urbanRenewal1
        $observer1 = $this->userFixture->createObserver($urbanRenewal1['id']);
        $observer1Token = $this->userFixture->generateToken($observer1);

        // Create meeting in urbanRenewal2
        $meeting2 = $this->meetingFixture->create($urbanRenewal2['id'], $observer1['id']);

        // Observer1 tries to access documents in urbanRenewal2
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $observer1Token
            ])
            ->get('/api/meetings/' . $meeting2['id'] . '/documents');

        $result->assertStatus(403);
    }

    /**
     * 驗收情境 3: 管理員可以存取所有 urban_renewal_id 的資源（無限制）
     */
    public function testAdminCanAccessAllUrbanRenewals()
    {
        // Create two urban renewals
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal2 = $this->urbanRenewalFixture->createActive();

        // Create admin
        $admin = $this->userFixture->createAdmin();
        $adminToken = $this->userFixture->generateToken($admin);

        // Create meetings in both urban renewals
        $meeting1 = $this->meetingFixture->create($urbanRenewal1['id'], $admin['id']);
        $meeting2 = $this->meetingFixture->create($urbanRenewal2['id'], $admin['id']);

        // Admin should access meeting1
        $result1 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $adminToken
            ])
            ->get('/api/meetings/' . $meeting1['id']);

        $result1->assertStatus(200);

        // Admin should access meeting2
        $result2 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $adminToken
            ])
            ->get('/api/meetings/' . $meeting2['id']);

        $result2->assertStatus(200);
    }

    /**
     * US4 驗收情境 5: 理事長嘗試存取其他 urban_renewal_id 的敏感端點時被拒絕
     */
    public function testChairmanCannotAccessSensitiveEndpointsOfOtherUrbanRenewal()
    {
        // Create two urban renewals
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal2 = $this->urbanRenewalFixture->createActive();

        // Create chairman in urbanRenewal1
        $chairman1 = $this->userFixture->createChairman($urbanRenewal1['id']);
        $chairman1Token = $this->userFixture->generateToken($chairman1);

        // Create member in urbanRenewal2
        $member2 = $this->userFixture->createMember($urbanRenewal2['id']);

        // Chairman1 tries to update member2 (different urban_renewal_id)
        $updateData = [
            'full_name' => '嘗試跨區修改'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $chairman1Token
            ])
            ->withBodyFormat('json')
            ->put('/api/users/' . $member2['id'], $updateData);

        $result->assertStatus(403);
    }

    /**
     * US4 驗收情境 5: 會員嘗試在其他 urban_renewal_id 的會議投票被拒絕
     */
    public function testMemberCannotVoteInOtherUrbanRenewalMeeting()
    {
        // Create two urban renewals
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal2 = $this->urbanRenewalFixture->createActive();

        // Create member in urbanRenewal1
        $member1 = $this->userFixture->createMember($urbanRenewal1['id']);
        $member1Token = $this->userFixture->generateToken($member1);

        // Create meeting and voting topic in urbanRenewal2
        $meeting2 = $this->meetingFixture->create($urbanRenewal2['id'], $member1['id']);
        $votingTopicModel = model('VotingTopicModel');
        $topicId = $votingTopicModel->insert([
            'meeting_id' => $meeting2['id'],
            'title' => '其他地區投票',
            'voting_type' => 'simple_majority',
            'status' => 'open'
        ]);

        // Member1 tries to vote in urbanRenewal2
        $voteData = [
            'voting_topic_id' => $topicId,
            'vote_choice' => 'agree'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $member1Token
            ])
            ->withBodyFormat('json')
            ->post('/api/voting/vote', $voteData);

        $result->assertStatus(403);
    }

    /**
     * 測試多個使用者同時存取不同 urban_renewal_id 的資源
     */
    public function testMultipleUsersAccessDifferentUrbanRenewalsSimultaneously()
    {
        // Create three urban renewals
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal2 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal3 = $this->urbanRenewalFixture->createActive();

        // Create users for each urban renewal
        $chairman1 = $this->userFixture->createChairman($urbanRenewal1['id']);
        $chairman2 = $this->userFixture->createChairman($urbanRenewal2['id']);
        $chairman3 = $this->userFixture->createChairman($urbanRenewal3['id']);

        $token1 = $this->userFixture->generateToken($chairman1);
        $token2 = $this->userFixture->generateToken($chairman2);
        $token3 = $this->userFixture->generateToken($chairman3);

        // Create meetings for each urban renewal
        $meeting1 = $this->meetingFixture->create($urbanRenewal1['id'], $chairman1['id']);
        $meeting2 = $this->meetingFixture->create($urbanRenewal2['id'], $chairman2['id']);
        $meeting3 = $this->meetingFixture->create($urbanRenewal3['id'], $chairman3['id']);

        // Chairman1 can only access meeting1
        $result1 = $this->withHeaders(['Authorization' => 'Bearer ' . $token1])
            ->get('/api/meetings/' . $meeting1['id']);
        $result1->assertStatus(200);

        $result1_2 = $this->withHeaders(['Authorization' => 'Bearer ' . $token1])
            ->get('/api/meetings/' . $meeting2['id']);
        $result1_2->assertStatus(403);

        // Chairman2 can only access meeting2
        $result2 = $this->withHeaders(['Authorization' => 'Bearer ' . $token2])
            ->get('/api/meetings/' . $meeting2['id']);
        $result2->assertStatus(200);

        $result2_3 = $this->withHeaders(['Authorization' => 'Bearer ' . $token2])
            ->get('/api/meetings/' . $meeting3['id']);
        $result2_3->assertStatus(403);

        // Chairman3 can only access meeting3
        $result3 = $this->withHeaders(['Authorization' => 'Bearer ' . $token3])
            ->get('/api/meetings/' . $meeting3['id']);
        $result3->assertStatus(200);

        $result3_1 = $this->withHeaders(['Authorization' => 'Bearer ' . $token3])
            ->get('/api/meetings/' . $meeting1['id']);
        $result3_1->assertStatus(403);
    }

    /**
     * 測試使用者列表過濾只顯示相同 urban_renewal_id 的使用者
     */
    public function testUserListFiltersByUrbanRenewalId()
    {
        // Create two urban renewals
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();
        $urbanRenewal2 = $this->urbanRenewalFixture->createActive();

        // Create chairman and members for urbanRenewal1
        $chairman1 = $this->userFixture->createChairman($urbanRenewal1['id']);
        $member1a = $this->userFixture->createMember($urbanRenewal1['id']);
        $member1b = $this->userFixture->createMember($urbanRenewal1['id']);

        // Create members for urbanRenewal2
        $member2a = $this->userFixture->createMember($urbanRenewal2['id']);
        $member2b = $this->userFixture->createMember($urbanRenewal2['id']);

        $chairman1Token = $this->userFixture->generateToken($chairman1);

        // Get user list as chairman1
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $chairman1Token
            ])
            ->get('/api/users');

        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);

        $userIds = array_column($data['data']['users'], 'id');

        // Should see users from urbanRenewal1
        $this->assertContains($chairman1['id'], $userIds);
        $this->assertContains($member1a['id'], $userIds);
        $this->assertContains($member1b['id'], $userIds);

        // Should NOT see users from urbanRenewal2
        $this->assertNotContains($member2a['id'], $userIds);
        $this->assertNotContains($member2b['id'], $userIds);
    }

    /**
     * 測試 urban_renewal_id 為 null 時的資料隔離
     */
    public function testNullUrbanRenewalIdIsolation()
    {
        // Create urban renewal
        $urbanRenewal1 = $this->urbanRenewalFixture->createActive();

        // Create admin (urban_renewal_id = null)
        $admin = $this->userFixture->createAdmin();
        $adminToken = $this->userFixture->generateToken($admin);

        // Create member
        $member = $this->userFixture->createMember($urbanRenewal1['id']);
        $memberToken = $this->userFixture->generateToken($member);

        // Create meeting for urbanRenewal1
        $meeting = $this->meetingFixture->create($urbanRenewal1['id'], $admin['id']);

        // Admin (urban_renewal_id = null) can access
        $adminResult = $this->withHeaders(['Authorization' => 'Bearer ' . $adminToken])
            ->get('/api/meetings/' . $meeting['id']);
        $adminResult->assertStatus(200);

        // Member (has urban_renewal_id) can access their own
        $memberResult = $this->withHeaders(['Authorization' => 'Bearer ' . $memberToken])
            ->get('/api/meetings/' . $meeting['id']);
        $memberResult->assertStatus(200);
    }
}
