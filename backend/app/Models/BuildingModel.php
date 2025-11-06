<?php

namespace App\Models;

use CodeIgniter\Model;

class BuildingModel extends Model
{
    protected $table = 'buildings';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'urban_renewal_id',
        'county',
        'district',
        'section',
        'building_number_main',
        'building_number_sub',
        'building_area',
        'building_address'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'urban_renewal_id' => 'required|integer',
        'county' => 'required|max_length[10]',
        'district' => 'required|max_length[10]',
        'section' => 'required|max_length[10]',
        'building_number_main' => 'required|max_length[10]',
        'building_number_sub' => 'permit_empty|max_length[10]',
        'building_area' => 'permit_empty|decimal',
        'building_address' => 'permit_empty|max_length[255]'
    ];

    protected $validationMessages = [
        'urban_renewal_id' => [
            'required' => '所屬更新會為必填項目',
            'integer' => '所屬更新會ID格式不正確'
        ],
        'county' => [
            'required' => '縣市為必填項目',
            'max_length' => '縣市名稱不能超過10字符'
        ],
        'district' => [
            'required' => '行政區為必填項目',
            'max_length' => '行政區名稱不能超過10字符'
        ],
        'section' => [
            'required' => '段小段為必填項目',
            'max_length' => '段小段名稱不能超過10字符'
        ],
        'building_number_main' => [
            'required' => '建號母號為必填項目',
            'max_length' => '建號母號不能超過10字符'
        ],
        'building_number_sub' => [
            'max_length' => '建號子號不能超過10字符'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $beforeUpdate = [];
    protected $afterInsert = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get buildings by urban renewal ID
     */
    public function getByUrbanRenewalId(int $urbanRenewalId): array
    {
        $result = $this->where('urban_renewal_id', $urbanRenewalId)
                       ->where('deleted_at IS NULL')
                       ->orderBy('created_at', 'DESC')
                       ->findAll();

        return $result !== false ? $result : [];
    }

    /**
     * Check if building number exists
     */
    public function buildingNumberExists(string $county, string $district, string $section, string $main, string $sub, ?int $excludeId = null): bool
    {
        $builder = $this->where('county', $county)
                        ->where('district', $district)
                        ->where('section', $section)
                        ->where('building_number_main', $main)
                        ->where('building_number_sub', $sub)
                        ->where('deleted_at IS NULL');

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        $count = $builder->countAllResults();
        return $count !== false && $count > 0;
    }

    /**
     * Find building by location and numbers
     */
    public function findByLocation(string $county, string $district, string $section, string $main, string $sub): ?array
    {
        $result = $this->where('county', $county)
                       ->where('district', $district)
                       ->where('section', $section)
                       ->where('building_number_main', $main)
                       ->where('building_number_sub', $sub)
                       ->where('deleted_at IS NULL')
                       ->first();

        return $result !== false ? $result : null;
    }

    /**
     * Create building if not exists
     */
    public function createIfNotExists(array $data): int
    {
        // Ensure building_number_sub is set (even if empty string)
        if (!isset($data['building_number_sub'])) {
            $data['building_number_sub'] = '';
        }

        $existing = $this->findByLocation(
            $data['county'],
            $data['district'],
            $data['section'],
            $data['building_number_main'],
            $data['building_number_sub']
        );

        if ($existing) {
            return $existing['id'];
        }

        $id = $this->insert($data);

        if ($id === false) {
            // Log validation errors
            $errors = $this->errors();
            log_message('error', 'Failed to create building: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
            log_message('error', 'Building data: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
            return 0;
        }

        return $id ?: 0;
    }

    /**
     * Get building full number
     */
    public function getFullNumber(array $building): string
    {
        return $building['building_number_main'] . '-' . $building['building_number_sub'];
    }

    /**
     * Get building location string
     */
    public function getLocationString(array $building): string
    {
        return $building['county'] . '/' . $building['district'] . '/' . $building['section'];
    }
}