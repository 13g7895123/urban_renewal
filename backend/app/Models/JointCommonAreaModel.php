<?php

namespace App\Models;

use CodeIgniter\Model;

class JointCommonAreaModel extends Model
{
    protected $table = 'joint_common_areas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'urban_renewal_id',
        'county',
        'district',
        'section',
        'building_number_main',
        'building_number_sub',
        'building_total_area',
        'corresponding_building_id',
        'ownership_numerator',
        'ownership_denominator'
    ];

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'urban_renewal_id' => 'required|integer',
        'county' => 'required|max_length[10]',
        'district' => 'required|max_length[10]',
        'section' => 'required|max_length[20]',
        'building_number_main' => 'required|max_length[10]',
        'building_number_sub' => 'required|max_length[10]'
    ];

    protected $validationMessages = [
        'urban_renewal_id' => [
            'required' => '更新會ID為必填項目',
            'integer' => '更新會ID必須是整數'
        ],
        'county' => [
            'required' => '縣市為必填項目'
        ],
        'district' => [
            'required' => '行政區為必填項目'
        ],
        'section' => [
            'required' => '段小段為必填項目'
        ],
        'building_number_main' => [
            'required' => '建號母號為必填項目'
        ],
        'building_number_sub' => [
            'required' => '建號子號為必填項目'
        ]
    ];

    /**
     * Get joint common areas by urban renewal ID
     */
    public function getByUrbanRenewalId($urbanRenewalId)
    {
        return $this->where('urban_renewal_id', $urbanRenewalId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Format building number for display
     */
    public function formatBuildingNumber($area)
    {
        $main = $area['building_number_main'] ?? '';
        $sub = $area['building_number_sub'] ?? '';

        if (empty($sub) || $sub === '0' || $sub === '000') {
            return $main;
        }
        return "{$main}-{$sub}";
    }
}
