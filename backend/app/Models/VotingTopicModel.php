<?php

namespace App\Models;

use CodeIgniter\Model;

class VotingTopicModel extends Model
{
    protected $table = 'voting_topics';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'meeting_id',
        'topic_number',
        'topic_title',
        'topic_description',
        'voting_method',
        'is_anonymous',
        'max_selections',
        'accepted_count',
        'alternate_count',
        'land_area_ratio_numerator',
        'land_area_ratio_denominator',
        'building_area_ratio_numerator',
        'building_area_ratio_denominator',
        'people_ratio_numerator',
        'people_ratio_denominator',
        'remarks',
        'total_votes',
        'agree_votes',
        'disagree_votes',
        'abstain_votes',
        'total_land_area',
        'agree_land_area',
        'disagree_land_area',
        'abstain_land_area',
        'total_building_area',
        'agree_building_area',
        'disagree_building_area',
        'abstain_building_area',
        'voting_result',
        'voting_status'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'meeting_id' => 'required|integer',
        'topic_number' => 'required|max_length[20]',
        'topic_title' => 'required|max_length[500]',
        'voting_method' => 'permit_empty|in_list[simple_majority,absolute_majority,two_thirds_majority,unanimous]',
        'voting_result' => 'permit_empty|in_list[pending,passed,failed,withdrawn]',
        'voting_status' => 'permit_empty|in_list[draft,active,closed]'
    ];

    protected $validationMessages = [
        'meeting_id' => [
            'required' => '會議ID為必填',
            'integer' => '會議ID必須為數字'
        ],
        'topic_number' => [
            'required' => '議題編號為必填',
            'max_length' => '議題編號不可超過20字元'
        ],
        'topic_title' => [
            'required' => '議題標題為必填',
            'max_length' => '議題標題不可超過500字元'
        ],
        'voting_method' => [
            'required' => '投票方式為必填',
            'in_list' => '投票方式必須為：simple_majority, absolute_majority, two_thirds_majority, unanimous'
        ],
        'voting_result' => [
            'in_list' => '投票結果必須為：pending, passed, failed, withdrawn'
        ],
        'voting_status' => [
            'in_list' => '投票狀態必須為：draft, active, closed'
        ]
    ];

    /**
     * 取得會議的投票議題列表
     */
    public function getTopicsByMeeting($meetingId, $page = 1, $perPage = 10, $status = null)
    {
        $builder = $this->select('voting_topics.*, meetings.meeting_name')
                       ->join('meetings', 'meetings.id = voting_topics.meeting_id', 'left')
                       ->where('voting_topics.meeting_id', $meetingId)
                       ->where('voting_topics.deleted_at', null);

        if ($status) {
            $builder->where('voting_topics.voting_status', $status);
        }

        return $builder->orderBy('voting_topics.topic_number', 'ASC')
                       ->paginate($perPage, 'default', $page);
    }

    /**
     * 取得投票議題詳情（包含投票記錄統計）
     */
    public function getTopicWithDetails($topicId)
    {
        $topic = $this->select('voting_topics.*, meetings.meeting_name, meetings.meeting_date, meetings.meeting_time')
                     ->join('meetings', 'meetings.id = voting_topics.meeting_id', 'left')
                     ->where('voting_topics.id', $topicId)
                     ->where('voting_topics.deleted_at', null)
                     ->first();

        if (!$topic) {
            return null;
        }

        // 取得投票記錄統計
        $votingRecordModel = model('VotingRecordModel');
        $topic['voting_statistics'] = $votingRecordModel->getVotingStatistics($topicId);

        // 取得投票記錄列表
        $topic['voting_records'] = $votingRecordModel->getVotingRecords($topicId);

        // 取得投票選項
        $votingOptionModel = model('VotingOptionModel');
        $topic['voting_options'] = $votingOptionModel->where('voting_topic_id', $topicId)
                                                    ->orderBy('sort_order', 'ASC')
                                                    ->findAll();

        // 計算投票結果
        $topic['result_analysis'] = $this->calculateVotingResult($topicId);

        return $topic;
    }

    /**
     * 計算投票結果
     */
    public function calculateVotingResult($topicId)
    {
        $topic = $this->find($topicId);
        if (!$topic) {
            return null;
        }

        $votingRecordModel = model('VotingRecordModel');
        $statistics = $votingRecordModel->getVotingStatistics($topicId);

        // 更新投票統計
        $this->update($topicId, [
            'total_votes' => $statistics['total_votes'],
            'agree_votes' => $statistics['agree_votes'],
            'disagree_votes' => $statistics['disagree_votes'],
            'abstain_votes' => $statistics['abstain_votes'],
            'total_land_area' => $statistics['total_land_area'],
            'agree_land_area' => $statistics['agree_land_area'],
            'disagree_land_area' => $statistics['disagree_land_area'],
            'abstain_land_area' => $statistics['abstain_land_area'],
            'total_building_area' => $statistics['total_building_area'],
            'agree_building_area' => $statistics['agree_building_area'],
            'disagree_building_area' => $statistics['disagree_building_area'],
            'abstain_building_area' => $statistics['abstain_building_area']
        ]);

        // 根據投票方式計算結果
        $result = 'failed';
        $agreeLandPercentage = $statistics['total_land_area'] > 0 ?
                              ($statistics['agree_land_area'] / $statistics['total_land_area']) * 100 : 0;
        $agreeBuildingPercentage = $statistics['total_building_area'] > 0 ?
                                  ($statistics['agree_building_area'] / $statistics['total_building_area']) * 100 : 0;
        $agreeVotePercentage = $statistics['total_votes'] > 0 ?
                              ($statistics['agree_votes'] / $statistics['total_votes']) * 100 : 0;

        switch ($topic['voting_method']) {
            case 'simple_majority':
                if ($agreeLandPercentage > 50 && $agreeBuildingPercentage > 50 && $agreeVotePercentage > 50) {
                    $result = 'passed';
                }
                break;
            case 'absolute_majority':
                if ($agreeLandPercentage >= 50 && $agreeBuildingPercentage >= 50 && $agreeVotePercentage >= 50) {
                    $result = 'passed';
                }
                break;
            case 'two_thirds_majority':
                if ($agreeLandPercentage >= 66.67 && $agreeBuildingPercentage >= 66.67 && $agreeVotePercentage >= 66.67) {
                    $result = 'passed';
                }
                break;
            case 'unanimous':
                if ($statistics['disagree_votes'] == 0 && $statistics['agree_votes'] > 0) {
                    $result = 'passed';
                }
                break;
        }

        // 更新投票結果
        if ($topic['voting_status'] === 'closed') {
            $this->update($topicId, ['voting_result' => $result]);
        }

        return [
            'voting_method' => $topic['voting_method'],
            'result' => $result,
            'land_area_percentage' => $agreeLandPercentage,
            'building_area_percentage' => $agreeBuildingPercentage,
            'vote_percentage' => $agreeVotePercentage,
            'statistics' => $statistics
        ];
    }

    /**
     * 檢查議題編號是否重複
     */
    public function checkTopicNumberExists($meetingId, $topicNumber, $excludeId = null)
    {
        $builder = $this->where('meeting_id', $meetingId)
                       ->where('topic_number', $topicNumber)
                       ->where('deleted_at', null);

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first();
    }

    /**
     * 取得投票統計摘要
     */
    public function getVotingStatistics($meetingId = null)
    {
        $builder = $this->select('voting_status, voting_result, COUNT(*) as count')
                       ->where('deleted_at', null);

        if ($meetingId) {
            $builder->where('meeting_id', $meetingId);
        }

        return $builder->groupBy(['voting_status', 'voting_result'])->findAll();
    }

    /**
     * 啟動投票
     */
    public function startVoting($topicId)
    {
        $topic = $this->find($topicId);
        if (!$topic || $topic['voting_status'] !== 'draft') {
            return false;
        }

        return $this->update($topicId, [
            'voting_status' => 'active',
            'voting_result' => 'pending'
        ]);
    }

    /**
     * 結束投票
     */
    public function closeVoting($topicId)
    {
        $topic = $this->find($topicId);
        if (!$topic || $topic['voting_status'] !== 'active') {
            return false;
        }

        // 計算最終結果
        $resultAnalysis = $this->calculateVotingResult($topicId);

        return $this->update($topicId, [
            'voting_status' => 'closed',
            'voting_result' => $resultAnalysis['result']
        ]);
    }

    /**
     * 搜尋投票議題
     */
    public function searchTopics($keyword, $page = 1, $perPage = 10, $filters = [])
    {
        $builder = $this->select('voting_topics.*, meetings.meeting_name, meetings.meeting_date')
                       ->join('meetings', 'meetings.id = voting_topics.meeting_id', 'left')
                       ->where('voting_topics.deleted_at', null);

        if (!empty($keyword)) {
            $builder->groupStart()
                   ->like('voting_topics.topic_title', $keyword)
                   ->orLike('voting_topics.topic_number', $keyword)
                   ->orLike('voting_topics.topic_description', $keyword)
                   ->groupEnd();
        }

        // 篩選條件
        if (!empty($filters['meeting_id'])) {
            $builder->where('voting_topics.meeting_id', $filters['meeting_id']);
        }

        if (!empty($filters['voting_status'])) {
            $builder->where('voting_topics.voting_status', $filters['voting_status']);
        }

        if (!empty($filters['voting_result'])) {
            $builder->where('voting_topics.voting_result', $filters['voting_result']);
        }

        if (!empty($filters['voting_method'])) {
            $builder->where('voting_topics.voting_method', $filters['voting_method']);
        }

        return $builder->orderBy('voting_topics.created_at', 'DESC')
                       ->paginate($perPage, 'default', $page);
    }

    /**
     * 取得即將投票的議題
     */
    public function getUpcomingTopics($days = 7)
    {
        return $this->select('voting_topics.*, meetings.meeting_name, meetings.meeting_date, meetings.meeting_time')
                   ->join('meetings', 'meetings.id = voting_topics.meeting_id', 'left')
                   ->where('voting_topics.deleted_at', null)
                   ->where('voting_topics.voting_status', 'draft')
                   ->where('meetings.meeting_date >=', date('Y-m-d'))
                   ->where('meetings.meeting_date <=', date('Y-m-d', strtotime("+{$days} days")))
                   ->orderBy('meetings.meeting_date', 'ASC')
                   ->orderBy('voting_topics.topic_number', 'ASC')
                   ->findAll();
    }
}