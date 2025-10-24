<?php

namespace Tests\Feature\RBAC;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Fixtures\UserFixture;
use Tests\Support\Fixtures\UrbanRenewalFixture;
use Tests\Support\Fixtures\MeetingFixture;

/**
 * 會員權限測試
 * 驗收情境 1、4、7
 */
class MemberPermissionsTest extends CIUnitTestCase
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
    protected $member;
    protected $memberToken;
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

        // Create member assigned to urbanRenewal1
        $this->member = $this->userFixture->createMember($this->urbanRenewal1['id']);
        $this->memberToken = $this->userFixture->generateToken($this->member);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Fixtures will auto-cleanup via __destruct
    }

    /**
     * 驗收情境 1: 會員登入後，僅能查看其 urban_renewal_id 的會議
     */
    public function testMemberCanOnlyViewAssignedUrbanRenewalMeetings()
    {
        // Create meetings in both urban renewals
        $meeting1 = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->member['id']);
        $meeting2 = $this->meetingFixture->create($this->urbanRenewal2['id'], $this->member['id']);

        // Member should access meeting1 (their urban_renewal_id)
        $result1 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->get('/api/meetings/' . $meeting1['id']);

        $result1->assertStatus(200);
        $data1 = json_decode($result1->getJSON(), true);
        $this->assertTrue($data1['success']);

        // Member should NOT access meeting2 (different urban_renewal_id)
        $result2 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->get('/api/meetings/' . $meeting2['id']);

        $result2->assertStatus(403);
        $data2 = json_decode($result2->getJSON(), true);
        $this->assertFalse($data2['success']);
    }

    /**
     * 驗收情境 1: 會員查看會議列表時只顯示其 urban_renewal_id 的會議
     */
    public function testMemberMeetingListOnlyShowsAssignedUrbanRenewal()
    {
        // Create multiple meetings
        $meeting1 = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->member['id'], [
            'title' => '會員的會議1'
        ]);
        $meeting2 = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->member['id'], [
            'title' => '會員的會議2'
        ]);
        $meeting3 = $this->meetingFixture->create($this->urbanRenewal2['id'], $this->member['id'], [
            'title' => '其他地區的會議'
        ]);

        // Get meeting list
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
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
     * 驗收情境 4: 會員可以在其 urban_renewal_id 的會議中投票
     */
    public function testMemberCanVoteInAssignedUrbanRenewalMeeting()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->member['id']);

        // Create voting topic
        $votingTopicModel = model('VotingTopicModel');
        $topicId = $votingTopicModel->insert([
            'meeting_id' => $meeting['id'],
            'title' => '測試投票議題',
            'description' => '會員投票測試',
            'voting_type' => 'simple_majority',
            'status' => 'open'
        ]);

        // Member votes
        $voteData = [
            'voting_topic_id' => $topicId,
            'vote_choice' => 'agree'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->withBodyFormat('json')
            ->post('/api/voting/vote', $voteData);

        $result->assertStatus(201);
        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
    }

    /**
     * 驗收情境 4: 會員無法在其他 urban_renewal_id 的會議中投票
     */
    public function testMemberCannotVoteInOtherUrbanRenewalMeeting()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal2['id'], $this->member['id']);

        // Create voting topic
        $votingTopicModel = model('VotingTopicModel');
        $topicId = $votingTopicModel->insert([
            'meeting_id' => $meeting['id'],
            'title' => '其他地區投票議題',
            'voting_type' => 'simple_majority',
            'status' => 'open'
        ]);

        // Member tries to vote
        $voteData = [
            'voting_topic_id' => $topicId,
            'vote_choice' => 'agree'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->withBodyFormat('json')
            ->post('/api/voting/vote', $voteData);

        $result->assertStatus(403);
    }

    /**
     * 驗收情境 7: 會員可以查看其 urban_renewal_id 的投票結果
     */
    public function testMemberCanViewVotingResultsInAssignedUrbanRenewal()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->member['id']);

        // Create voting topic
        $votingTopicModel = model('VotingTopicModel');
        $topicId = $votingTopicModel->insert([
            'meeting_id' => $meeting['id'],
            'title' => '投票結果測試',
            'voting_type' => 'simple_majority',
            'status' => 'closed'
        ]);

        // Get voting results
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->get('/api/voting/topics/' . $topicId . '/results');

        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
    }

    /**
     * 驗收情境 7: 會員無法查看其他 urban_renewal_id 的投票結果
     */
    public function testMemberCannotViewVotingResultsInOtherUrbanRenewal()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal2['id'], $this->member['id']);

        // Create voting topic
        $votingTopicModel = model('VotingTopicModel');
        $topicId = $votingTopicModel->insert([
            'meeting_id' => $meeting['id'],
            'title' => '其他地區投票結果',
            'voting_type' => 'simple_majority',
            'status' => 'closed'
        ]);

        // Try to get voting results
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->get('/api/voting/topics/' . $topicId . '/results');

        $result->assertStatus(403);
    }

    /**
     * 會員無法建立會議
     */
    public function testMemberCannotCreateMeeting()
    {
        $meetingData = [
            'urban_renewal_id' => $this->urbanRenewal1['id'],
            'meeting_type' => 'general',
            'title' => '會員嘗試建立會議',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 week')),
            'location' => '會議室'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->withBodyFormat('json')
            ->post('/api/meetings', $meetingData);

        $result->assertStatus(403);
    }

    /**
     * 會員無法編輯會議
     */
    public function testMemberCannotUpdateMeeting()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->member['id']);

        $updateData = [
            'title' => '會員嘗試更新會議'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->withBodyFormat('json')
            ->put('/api/meetings/' . $meeting['id'], $updateData);

        $result->assertStatus(403);
    }

    /**
     * 會員無法刪除會議
     */
    public function testMemberCannotDeleteMeeting()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->member['id']);

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->delete('/api/meetings/' . $meeting['id']);

        $result->assertStatus(403);
    }

    /**
     * 會員無法建立其他使用者
     */
    public function testMemberCannotCreateUser()
    {
        $userData = [
            'username' => 'new_user_' . time(),
            'password' => 'password123',
            'email' => 'newuser@test.com',
            'role' => 'member',
            'urban_renewal_id' => $this->urbanRenewal1['id']
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->withBodyFormat('json')
            ->post('/api/users', $userData);

        $result->assertStatus(403);
    }

    /**
     * 會員可以查看自己的資料
     */
    public function testMemberCanViewOwnProfile()
    {
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->get('/api/users/' . $this->member['id']);

        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals($this->member['id'], $data['data']['id']);
    }

    /**
     * 會員可以修改自己的基本資料
     */
    public function testMemberCanUpdateOwnProfile()
    {
        $updateData = [
            'full_name' => '更新的姓名',
            'phone' => '0912345678'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->withBodyFormat('json')
            ->put('/api/users/' . $this->member['id'], $updateData);

        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('更新的姓名', $data['data']['full_name']);
    }

    /**
     * 會員無法查看其他使用者的資料
     */
    public function testMemberCannotViewOtherUserProfile()
    {
        $otherMember = $this->userFixture->createMember($this->urbanRenewal1['id']);

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->memberToken
            ])
            ->get('/api/users/' . $otherMember['id']);

        $result->assertStatus(403);
    }
}
