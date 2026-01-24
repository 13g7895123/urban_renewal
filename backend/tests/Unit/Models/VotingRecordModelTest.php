<?php

namespace Tests\Unit\Models;

use Tests\Support\ApiTestCase;
use App\Models\VotingRecordModel;
use App\Models\VotingTopicModel;
use App\Models\PropertyOwnerModel;
use App\Models\MeetingModel;
use App\Models\UrbanRenewalModel;

class VotingRecordModelTest extends ApiTestCase
{
    protected $votingRecordModel;
    protected $votingTopicModel;
    protected $propertyOwnerModel;
    protected $meetingModel;
    protected $urbanRenewalModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->votingRecordModel = new VotingRecordModel();
        $this->votingTopicModel = new VotingTopicModel();
        $this->propertyOwnerModel = new PropertyOwnerModel();
        $this->meetingModel = new MeetingModel();
        $this->urbanRenewalModel = new UrbanRenewalModel();
    }

    public function testCastVoteCreatesNewRecord()
    {
        // 建立測試資料
        $company = $this->createTestCompany();
        
        $renewalId = $this->urbanRenewalModel->insert([
            'company_id' => $company['id'],
            'name' => 'Test Renewal',
            'area' => 1000.00,
            'address' => 'Test Address'
        ]);

        $meetingId = $this->meetingModel->insert([
            'urban_renewal_id' => $renewalId,
            'meeting_type' => '會員大會',
            'meeting_date' => date('Y-m-d'),
            'status' => 'scheduled'
        ]);

        $topicId = $this->votingTopicModel->insert([
            'meeting_id' => $meetingId,
            'topic_title' => 'Test Topic',
            'voting_method' => 'simple_majority',
            'status' => 'open'
        ]);

        $ownerId = $this->propertyOwnerModel->insert([
            'urban_renewal_id' => $renewalId,
            'name' => 'Test Owner',
            'id_number' => 'A123456789'
        ]);

        // 執行投票
        $result = $this->votingRecordModel->castVote(
            $topicId,
            $ownerId,
            'agree',
            'Test Voter',
            'Test note'
        );

        $this->assertTrue($result);

        // 驗證記錄已建立
        $vote = $this->votingRecordModel
            ->where('voting_topic_id', $topicId)
            ->where('property_owner_id', $ownerId)
            ->first();

        $this->assertNotNull($vote);
        $this->assertEquals('agree', $vote['vote_choice']);
        $this->assertEquals('Test Voter', $vote['voter_name']);
        $this->assertEquals('Test note', $vote['notes']);
    }

    public function testCannotVoteTwiceForSameTopic()
    {
        // 建立測試資料
        $company = $this->createTestCompany();
        
        $renewalId = $this->urbanRenewalModel->insert([
            'company_id' => $company['id'],
            'name' => 'Test Renewal',
            'area' => 1000.00,
            'address' => 'Test Address'
        ]);

        $meetingId = $this->meetingModel->insert([
            'urban_renewal_id' => $renewalId,
            'meeting_type' => '會員大會',
            'meeting_date' => date('Y-m-d'),
            'status' => 'scheduled'
        ]);

        $topicId = $this->votingTopicModel->insert([
            'meeting_id' => $meetingId,
            'topic_title' => 'Test Topic',
            'voting_method' => 'simple_majority',
            'status' => 'open'
        ]);

        $ownerId = $this->propertyOwnerModel->insert([
            'urban_renewal_id' => $renewalId,
            'name' => 'Test Owner',
            'id_number' => 'A123456789'
        ]);

        // 第一次投票
        $result1 = $this->votingRecordModel->castVote($topicId, $ownerId, 'agree');
        $this->assertTrue($result1);

        // 第二次投票（應該更新而非新增）
        $result2 = $this->votingRecordModel->castVote($topicId, $ownerId, 'disagree');
        $this->assertTrue($result2);

        // 驗證只有一筆記錄
        $votes = $this->votingRecordModel
            ->where('voting_topic_id', $topicId)
            ->where('property_owner_id', $ownerId)
            ->findAll();

        $this->assertCount(1, $votes);
        $this->assertEquals('disagree', $votes[0]['vote_choice']);
    }

    public function testHasVotedReturnsTrueWhenVoted()
    {
        // 建立測試資料
        $company = $this->createTestCompany();
        
        $renewalId = $this->urbanRenewalModel->insert([
            'company_id' => $company['id'],
            'name' => 'Test Renewal',
            'area' => 1000.00,
            'address' => 'Test Address'
        ]);

        $meetingId = $this->meetingModel->insert([
            'urban_renewal_id' => $renewalId,
            'meeting_type' => '會員大會',
            'meeting_date' => date('Y-m-d'),
            'status' => 'scheduled'
        ]);

        $topicId = $this->votingTopicModel->insert([
            'meeting_id' => $meetingId,
            'topic_title' => 'Test Topic',
            'voting_method' => 'simple_majority',
            'status' => 'open'
        ]);

        $ownerId = $this->propertyOwnerModel->insert([
            'urban_renewal_id' => $renewalId,
            'name' => 'Test Owner',
            'id_number' => 'A123456789'
        ]);

        // 投票前
        $this->assertFalse($this->votingRecordModel->hasVoted($topicId, $ownerId));

        // 投票
        $this->votingRecordModel->castVote($topicId, $ownerId, 'agree');

        // 投票後
        $this->assertTrue($this->votingRecordModel->hasVoted($topicId, $ownerId));
    }

    public function testRemoveVoteDeletesRecord()
    {
        // 建立測試資料
        $company = $this->createTestCompany();
        
        $renewalId = $this->urbanRenewalModel->insert([
            'company_id' => $company['id'],
            'name' => 'Test Renewal',
            'area' => 1000.00,
            'address' => 'Test Address'
        ]);

        $meetingId = $this->meetingModel->insert([
            'urban_renewal_id' => $renewalId,
            'meeting_type' => '會員大會',
            'meeting_date' => date('Y-m-d'),
            'status' => 'scheduled'
        ]);

        $topicId = $this->votingTopicModel->insert([
            'meeting_id' => $meetingId,
            'topic_title' => 'Test Topic',
            'voting_method' => 'simple_majority',
            'status' => 'open'
        ]);

        $ownerId = $this->propertyOwnerModel->insert([
            'urban_renewal_id' => $renewalId,
            'name' => 'Test Owner',
            'id_number' => 'A123456789'
        ]);

        // 投票
        $this->votingRecordModel->castVote($topicId, $ownerId, 'agree');
        $this->assertTrue($this->votingRecordModel->hasVoted($topicId, $ownerId));

        // 移除投票
        $result = $this->votingRecordModel->removeVote($topicId, $ownerId);
        $this->assertTrue($result);

        // 驗證已移除
        $this->assertFalse($this->votingRecordModel->hasVoted($topicId, $ownerId));
    }

    public function testGetUserVoteReturnsCorrectRecord()
    {
        // 建立測試資料
        $company = $this->createTestCompany();
        
        $renewalId = $this->urbanRenewalModel->insert([
            'company_id' => $company['id'],
            'name' => 'Test Renewal',
            'area' => 1000.00,
            'address' => 'Test Address'
        ]);

        $meetingId = $this->meetingModel->insert([
            'urban_renewal_id' => $renewalId,
            'meeting_type' => '會員大會',
            'meeting_date' => date('Y-m-d'),
            'status' => 'scheduled'
        ]);

        $topicId = $this->votingTopicModel->insert([
            'meeting_id' => $meetingId,
            'topic_title' => 'Test Topic',
            'voting_method' => 'simple_majority',
            'status' => 'open'
        ]);

        $ownerId = $this->propertyOwnerModel->insert([
            'urban_renewal_id' => $renewalId,
            'name' => 'Test Owner',
            'id_number' => 'A123456789'
        ]);

        // 投票
        $this->votingRecordModel->castVote($topicId, $ownerId, 'agree', 'Test Voter');

        // 取得投票記錄
        $vote = $this->votingRecordModel->getUserVote($topicId, $ownerId);

        $this->assertNotNull($vote);
        $this->assertEquals('agree', $vote['vote_choice']);
        $this->assertEquals('Test Voter', $vote['voter_name']);
    }
}
