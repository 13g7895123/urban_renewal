<?php

namespace App\Models;

use CodeIgniter\Model;

class MeetingObserverModel extends Model
{
    protected $table = 'meeting_observers';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'meeting_id',
        'observer_name',
        'observer_title',
        'observer_organization',
        'contact_phone',
        'notes'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'meeting_id' => 'required|integer',
        'observer_name' => 'required|max_length[100]'
    ];

    protected $validationMessages = [
        'meeting_id' => [
            'required' => '會議ID為必填'
        ],
        'observer_name' => [
            'required' => '列席者姓名為必填'
        ]
    ];
}
