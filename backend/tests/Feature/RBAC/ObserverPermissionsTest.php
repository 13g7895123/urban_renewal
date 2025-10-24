<?php

namespace Tests\Feature\RBAC;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Fixtures\UserFixture;
use Tests\Support\Fixtures\UrbanRenewalFixture;
use Tests\Support\Fixtures\MeetingFixture;

/**
 * 觀察員權限測試
 * 驗收情境 5
 */
class ObserverPermissionsTest extends CIUnitTestCase
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
    protected $observer;
    protected $observerToken;
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

        // Create observer assigned to urbanRenewal1
        $this->observer = $this->userFixture->createObserver($this->urbanRenewal1['id']);
        $this->observerToken = $this->userFixture->generateToken($this->observer);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Fixtures will auto-cleanup via __destruct
    }

    /**
     * 驗收情境 5: 觀察員僅能唯讀查看其 urban_renewal_id 的會議和投票結果
     */
    public function testObserverCanViewAssignedUrbanRenewalMeetings()
    {
        // Create meetings in both urban renewals
        $meeting1 = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->observer['id']);
        $meeting2 = $this->meetingFixture->create($this->urbanRenewal2['id'], $this->observer['id']);

        // Observer should access meeting1 (their urban_renewal_id)
        $result1 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->get('/api/meetings/' . $meeting1['id']);

        $result1->assertStatus(200);
        $data1 = json_decode($result1->getJSON(), true);
        $this->assertTrue($data1['success']);

        // Observer should NOT access meeting2 (different urban_renewal_id)
        $result2 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->get('/api/meetings/' . $meeting2['id']);

        $result2->assertStatus(403);
        $data2 = json_decode($result2->getJSON(), true);
        $this->assertFalse($data2['success']);
    }

    /**
     * 驗收情境 5: 觀察員可以查看會議列表（僅限其 urban_renewal_id）
     */
    public function testObserverCanViewMeetingList()
    {
        // Create multiple meetings
        $meeting1 = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->observer['id'], [
            'title' => '觀察員可見會議1'
        ]);
        $meeting2 = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->observer['id'], [
            'title' => '觀察員可見會議2'
        ]);
        $meeting3 = $this->meetingFixture->create($this->urbanRenewal2['id'], $this->observer['id'], [
            'title' => '其他地區會議'
        ]);

        // Get meeting list
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
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
     * 驗收情境 5: 觀察員可以查看投票結果（僅限其 urban_renewal_id）
     */
    public function testObserverCanViewVotingResults()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->observer['id']);

        // Create voting topic
        $votingTopicModel = model('VotingTopicModel');
        $topicId = $votingTopicModel->insert([
            'meeting_id' => $meeting['id'],
            'title' => '投票結果測試',
            'voting_type' => 'simple_majority',
            'status' => 'closed'
        ]);

        // Observer views voting results
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->get('/api/voting/topics/' . $topicId . '/results');

        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
    }

    /**
     * 驗收情境 5: 觀察員無法查看其他 urban_renewal_id 的投票結果
     */
    public function testObserverCannotViewOtherUrbanRenewalVotingResults()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal2['id'], $this->observer['id']);

        // Create voting topic
        $votingTopicModel = model('VotingTopicModel');
        $topicId = $votingTopicModel->insert([
            'meeting_id' => $meeting['id'],
            'title' => '其他地區投票結果',
            'voting_type' => 'simple_majority',
            'status' => 'closed'
        ]);

        // Observer tries to view voting results
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->get('/api/voting/topics/' . $topicId . '/results');

        $result->assertStatus(403);
    }

    /**
     * 驗收情境 5: 觀察員無法投票
     */
    public function testObserverCannotVote()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->observer['id']);

        // Create voting topic
        $votingTopicModel = model('VotingTopicModel');
        $topicId = $votingTopicModel->insert([
            'meeting_id' => $meeting['id'],
            'title' => '投票議題',
            'voting_type' => 'simple_majority',
            'status' => 'open'
        ]);

        // Observer tries to vote
        $voteData = [
            'voting_topic_id' => $topicId,
            'vote_choice' => 'agree'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->withBodyFormat('json')
            ->post('/api/voting/vote', $voteData);

        $result->assertStatus(403);
    }

    /**
     * 驗收情境 5: 觀察員無法建立會議
     */
    public function testObserverCannotCreateMeeting()
    {
        $meetingData = [
            'urban_renewal_id' => $this->urbanRenewal1['id'],
            'meeting_type' => 'general',
            'title' => '觀察員嘗試建立會議',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 week')),
            'location' => '會議室'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->withBodyFormat('json')
            ->post('/api/meetings', $meetingData);

        $result->assertStatus(403);
    }

    /**
     * 驗收情境 5: 觀察員無法編輯會議
     */
    public function testObserverCannotUpdateMeeting()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->observer['id']);

        $updateData = [
            'title' => '觀察員嘗試更新會議'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->withBodyFormat('json')
            ->put('/api/meetings/' . $meeting['id'], $updateData);

        $result->assertStatus(403);
    }

    /**
     * 驗收情境 5: 觀察員無法刪除會議
     */
    public function testObserverCannotDeleteMeeting()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->observer['id']);

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->delete('/api/meetings/' . $meeting['id']);

        $result->assertStatus(403);
    }

    /**
     * 觀察員無法建立使用者
     */
    public function testObserverCannotCreateUser()
    {
        $userData = [
            'username' => 'new_user_' . time(),
            'password' => 'password123',
            'email' => 'newuser@test.com',
            'role' => 'observer',
            'urban_renewal_id' => $this->urbanRenewal1['id']
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->withBodyFormat('json')
            ->post('/api/users', $userData);

        $result->assertStatus(403);
    }

    /**
     * 觀察員無法查看使用者列表
     */
    public function testObserverCannotViewUserList()
    {
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->get('/api/users');

        $result->assertStatus(403);
    }

    /**
     * 觀察員可以查看自己的資料
     */
    public function testObserverCanViewOwnProfile()
    {
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->get('/api/users/' . $this->observer['id']);

        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals($this->observer['id'], $data['data']['id']);
    }

    /**
     * 觀察員可以修改自己的基本資料
     */
    public function testObserverCanUpdateOwnProfile()
    {
        $updateData = [
            'full_name' => '更新的姓名',
            'phone' => '0987654321'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->withBodyFormat('json')
            ->put('/api/users/' . $this->observer['id'], $updateData);

        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('更新的姓名', $data['data']['full_name']);
    }

    /**
     * 觀察員無法修改其他使用者的資料
     */
    public function testObserverCannotUpdateOtherUserProfile()
    {
        $otherObserver = $this->userFixture->createObserver($this->urbanRenewal1['id']);

        $updateData = [
            'full_name' => '嘗試修改其他人'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->withBodyFormat('json')
            ->put('/api/users/' . $otherObserver['id'], $updateData);

        $result->assertStatus(403);
    }

    /**
     * 觀察員可以查看文件（僅限其 urban_renewal_id）
     */
    public function testObserverCanViewDocumentsInAssignedUrbanRenewal()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->observer['id']);

        // Get meeting documents
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->get('/api/meetings/' . $meeting['id'] . '/documents');

        $result->assertStatus(200);
    }

    /**
     * 觀察員無法上傳文件
     */
    public function testObserverCannotUploadDocument()
    {
        $meeting = $this->meetingFixture->create($this->urbanRenewal1['id'], $this->observer['id']);

        $documentData = [
            'meeting_id' => $meeting['id'],
            'title' => '嘗試上傳文件',
            'document_type' => 'agenda'
        ];

        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->observerToken
            ])
            ->withBodyFormat('json')
            ->post('/api/documents', $documentData);

        $result->assertStatus(403);
    }
}
