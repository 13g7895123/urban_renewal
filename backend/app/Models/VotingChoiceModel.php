<?php

namespace App\Models;

use CodeIgniter\Model;

class VotingChoiceModel extends Model
{
    protected $table = 'voting_choices';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'voting_topic_id',
        'choice_name',
        'sort_order'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'voting_topic_id' => 'required|integer',
        'choice_name' => 'required|max_length[255]'
    ];

    protected $validationMessages = [
        'voting_topic_id' => [
            'required' => '投票議題ID為必填',
            'integer' => '投票議題ID必須為數字'
        ],
        'choice_name' => [
            'required' => '選項名稱為必填',
            'max_length' => '選項名稱不可超過255字元'
        ]
    ];

    /**
     * 取得投票議題的所有投票選項
     */
    public function getChoicesByTopicId($topicId)
    {
        return $this->where('voting_topic_id', $topicId)
                    ->where('deleted_at', null)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * 建立預設投票選項 (同意/不同意)
     */
    public function createDefaultChoices($topicId)
    {
        $defaultChoices = [
            ['voting_topic_id' => $topicId, 'choice_name' => '同意', 'sort_order' => 0],
            ['voting_topic_id' => $topicId, 'choice_name' => '不同意', 'sort_order' => 1]
        ];

        return $this->insertBatch($defaultChoices);
    }
}
