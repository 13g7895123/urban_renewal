<?php

namespace App\Models;

use CodeIgniter\Model;

class VotingOptionModel extends Model
{
    protected $table = 'voting_options';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'voting_topic_id',
        'option_name',
        'property_owner_id',
        'is_pinned',
        'sort_order'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}
