<?php

namespace App\Models;

use CodeIgniter\Model;

class VotingRecordModel extends Model
{
    protected $table = 'voting_records';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'voting_topic_id',
        'property_owner_id',
        'vote_choice',
        'vote_time',
        'voter_name',
        'land_area_weight',
        'building_area_weight',
        'notes'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'voting_topic_id' => 'required|integer',
        'property_owner_id' => 'required|integer',
        'vote_choice' => 'required|in_list[agree,disagree,abstain]',
        'voter_name' => 'permit_empty|max_length[100]',
        'land_area_weight' => 'permit_empty|decimal',
        'building_area_weight' => 'permit_empty|decimal'
    ];

    protected $validationMessages = [
        'voting_topic_id' => [
            'required' => '投票議題ID為必填',
            'integer' => '投票議題ID必須為數字'
        ],
        'property_owner_id' => [
            'required' => '所有權人ID為必填',
            'integer' => '所有權人ID必須為數字'
        ],
        'vote_choice' => [
            'required' => '投票選擇為必填',
            'in_list' => '投票選擇必須為：agree, disagree, abstain'
        ],
        'voter_name' => [
            'max_length' => '投票人姓名不可超過100字元'
        ],
        'land_area_weight' => [
            'decimal' => '土地面積權重必須為數字'
        ],
        'building_area_weight' => [
            'decimal' => '建物面積權重必須為數字'
        ]
    ];

    /**
     * 取得投票記錄列表
     */
    public function getVotingRecords($topicId, $page = 1, $perPage = 10, $choice = null)
    {
        $builder = $this->select('voting_records.*, property_owners.name as owner_name, property_owners.id_number')
                       ->join('property_owners', 'property_owners.id = voting_records.property_owner_id', 'left')
                       ->where('voting_records.voting_topic_id', $topicId);

        if ($choice) {
            $builder->where('voting_records.vote_choice', $choice);
        }

        return $builder->orderBy('voting_records.vote_time', 'ASC')
                       ->paginate($perPage, 'default', $page);
    }

    /**
     * 取得投票統計
     */
    public function getVotingStatistics($topicId)
    {
        $records = $this->select('vote_choice, COUNT(*) as count,
                                SUM(land_area_weight) as total_land_area,
                                SUM(building_area_weight) as total_building_area')
                      ->where('voting_topic_id', $topicId)
                      ->groupBy('vote_choice')
                      ->findAll();

        $statistics = [
            'total_votes' => 0,
            'agree_votes' => 0,
            'disagree_votes' => 0,
            'abstain_votes' => 0,
            'total_land_area' => 0,
            'agree_land_area' => 0,
            'disagree_land_area' => 0,
            'abstain_land_area' => 0,
            'total_building_area' => 0,
            'agree_building_area' => 0,
            'disagree_building_area' => 0,
            'abstain_building_area' => 0
        ];

        foreach ($records as $record) {
            $statistics['total_votes'] += $record['count'];
            $statistics['total_land_area'] += $record['total_land_area'];
            $statistics['total_building_area'] += $record['total_building_area'];

            switch ($record['vote_choice']) {
                case 'agree':
                    $statistics['agree_votes'] = $record['count'];
                    $statistics['agree_land_area'] = $record['total_land_area'];
                    $statistics['agree_building_area'] = $record['total_building_area'];
                    break;
                case 'disagree':
                    $statistics['disagree_votes'] = $record['count'];
                    $statistics['disagree_land_area'] = $record['total_land_area'];
                    $statistics['disagree_building_area'] = $record['total_building_area'];
                    break;
                case 'abstain':
                    $statistics['abstain_votes'] = $record['count'];
                    $statistics['abstain_land_area'] = $record['total_land_area'];
                    $statistics['abstain_building_area'] = $record['total_building_area'];
                    break;
            }
        }

        return $statistics;
    }

    /**
     * 投票
     */
    public function castVote($topicId, $propertyOwnerId, $choice, $voterName = null, $notes = null)
    {
        // 檢查是否已經投過票
        $existingVote = $this->where('voting_topic_id', $topicId)
                            ->where('property_owner_id', $propertyOwnerId)
                            ->first();

        // 取得所有權人的土地和建物面積
        $propertyOwnerModel = model('PropertyOwnerModel');
        $owner = $propertyOwnerModel->find($propertyOwnerId);
        if (!$owner) {
            return false;
        }

        $voteData = [
            'voting_topic_id' => $topicId,
            'property_owner_id' => $propertyOwnerId,
            'vote_choice' => $choice,
            'vote_time' => date('Y-m-d H:i:s'),
            'voter_name' => $voterName ?: $owner['name'],
            'land_area_weight' => $owner['land_area'] ?: 0,
            'building_area_weight' => $owner['building_area'] ?: 0,
            'notes' => $notes
        ];

        if ($existingVote) {
            // 更新投票
            return $this->update($existingVote['id'], $voteData);
        } else {
            // 新增投票
            return $this->insert($voteData);
        }
    }

    /**
     * 檢查使用者是否已投票
     */
    public function hasVoted($topicId, $propertyOwnerId)
    {
        return $this->where('voting_topic_id', $topicId)
                   ->where('property_owner_id', $propertyOwnerId)
                   ->countAllResults() > 0;
    }

    /**
     * 取得使用者的投票記錄
     */
    public function getUserVote($topicId, $propertyOwnerId)
    {
        return $this->where('voting_topic_id', $topicId)
                   ->where('property_owner_id', $propertyOwnerId)
                   ->first();
    }

    /**
     * 批量投票（代理投票）
     */
    public function batchCastVotes($votes)
    {
        $results = [];
        foreach ($votes as $vote) {
            $result = $this->castVote(
                $vote['topic_id'],
                $vote['property_owner_id'],
                $vote['choice'],
                $vote['voter_name'] ?? null,
                $vote['notes'] ?? null
            );
            $results[] = [
                'property_owner_id' => $vote['property_owner_id'],
                'success' => $result !== false
            ];
        }
        return $results;
    }

    /**
     * 取得投票詳細記錄（含所有權人資訊）
     */
    public function getDetailedVotingRecords($topicId)
    {
        return $this->select('voting_records.*,
                            property_owners.name as owner_name,
                            property_owners.id_number,
                            property_owners.phone,
                            property_owners.land_area,
                            property_owners.building_area')
                   ->join('property_owners', 'property_owners.id = voting_records.property_owner_id', 'left')
                   ->where('voting_records.voting_topic_id', $topicId)
                   ->orderBy('voting_records.vote_time', 'ASC')
                   ->findAll();
    }

    /**
     * 刪除投票記錄
     */
    public function removeVote($topicId, $propertyOwnerId)
    {
        return $this->where('voting_topic_id', $topicId)
                   ->where('property_owner_id', $propertyOwnerId)
                   ->delete();
    }

    /**
     * 取得投票參與統計
     */
    public function getParticipationStatistics($topicId)
    {
        $votingTopicModel = model('VotingTopicModel');
        $topic = $votingTopicModel->find($topicId);
        if (!$topic) {
            return null;
        }

        // 取得該會議的總所有權人數
        $propertyOwnerModel = model('PropertyOwnerModel');
        $totalOwners = $propertyOwnerModel->where('urban_renewal_id', $topic['meeting_id'])->countAllResults();

        // 取得實際投票人數
        $actualVotes = $this->where('voting_topic_id', $topicId)->countAllResults();

        return [
            'total_eligible_voters' => $totalOwners,
            'actual_voters' => $actualVotes,
            'participation_rate' => $totalOwners > 0 ? ($actualVotes / $totalOwners) * 100 : 0,
            'abstention_count' => $totalOwners - $actualVotes
        ];
    }

    /**
     * 匯出投票記錄
     */
    public function exportVotingRecords($topicId, $format = 'csv')
    {
        $records = $this->getDetailedVotingRecords($topicId);

        if ($format === 'csv') {
            $csv = "投票時間,所有權人姓名,身分證字號,投票選擇,土地面積權重,建物面積權重,備註\n";
            foreach ($records as $record) {
                $csv .= sprintf(
                    "%s,%s,%s,%s,%.2f,%.2f,%s\n",
                    $record['vote_time'],
                    $record['owner_name'],
                    $record['id_number'],
                    $this->translateVoteChoice($record['vote_choice']),
                    $record['land_area_weight'],
                    $record['building_area_weight'],
                    $record['notes']
                );
            }
            return $csv;
        }

        return $records;
    }

    /**
     * 翻譯投票選擇
     */
    private function translateVoteChoice($choice)
    {
        $translations = [
            'agree' => '同意',
            'disagree' => '不同意',
            'abstain' => '棄權'
        ];
        return $translations[$choice] ?? $choice;
    }
}