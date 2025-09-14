<?php

namespace App\Models;

use CodeIgniter\Model;

class PropertyOwnerModel extends Model
{
    protected $table = 'property_owners';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'urban_renewal_id',
        'name',
        'id_number',
        'owner_code',
        'phone1',
        'phone2',
        'contact_address',
        'household_address',
        'exclusion_type',
        'notes'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'urban_renewal_id' => 'required|integer',
        'name' => 'required|max_length[100]',
        'id_number' => 'permit_empty|max_length[20]',
        'owner_code' => 'permit_empty|max_length[20]|is_unique[property_owners.owner_code,id,{id}]',
        'phone1' => 'permit_empty|max_length[20]',
        'phone2' => 'permit_empty|max_length[20]',
        'contact_address' => 'permit_empty',
        'household_address' => 'permit_empty',
        'exclusion_type' => 'permit_empty|in_list[法院囑託查封,假扣押,假處分,破產登記,未經繼承]',
        'notes' => 'permit_empty'
    ];

    protected $validationMessages = [
        'urban_renewal_id' => [
            'required' => '所屬更新會為必填項目',
            'integer' => '所屬更新會ID格式不正確'
        ],
        'name' => [
            'required' => '所有權人名稱為必填項目',
            'max_length' => '所有權人名稱不能超過100字符'
        ],
        'owner_code' => [
            'required' => '所有權人編號為必填項目',
            'max_length' => '所有權人編號不能超過20字符',
            'is_unique' => '所有權人編號已存在'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = ['generateOwnerCode'];
    protected $beforeUpdate = [];
    protected $afterInsert = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = ['loadRelatedData'];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Generate unique owner code before insert
     */
    protected function generateOwnerCode(array $data)
    {
        if (!isset($data['data']['owner_code']) || empty($data['data']['owner_code'])) {
            $data['data']['owner_code'] = $this->generateUniqueOwnerCode();
        }
        return $data;
    }

    /**
     * Generate unique owner code
     */
    private function generateUniqueOwnerCode(): string
    {
        do {
            $timestamp = date('ymd');
            $randomNum = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $code = 'OW' . $timestamp . $randomNum;
        } while ($this->where('owner_code', $code)->first());

        return $code;
    }

    /**
     * Load related data after find
     */
    protected function loadRelatedData(array $data)
    {
        if (isset($data['data'])) {
            if (isset($data['data'][0])) {
                // Multiple records
                foreach ($data['data'] as &$record) {
                    $record = $this->attachRelatedData($record);
                }
            } else {
                // Single record
                $data['data'] = $this->attachRelatedData($data['data']);
            }
        }

        return $data;
    }

    /**
     * Attach related data to a record
     */
    private function attachRelatedData(array $record): array
    {
        if (!isset($record['id'])) {
            return $record;
        }

        $ownerBuildingModel = model('OwnerBuildingOwnershipModel');
        $ownerLandModel = model('OwnerLandOwnershipModel');
        $buildingModel = model('BuildingModel');
        $landPlotModel = model('LandPlotModel');

        // Get building ownerships
        $buildingOwnerships = $ownerBuildingModel
            ->where('property_owner_id', $record['id'])
            ->findAll();

        $buildings = [];
        foreach ($buildingOwnerships as $ownership) {
            $building = $buildingModel->find($ownership['building_id']);
            if ($building) {
                $building['ownership_numerator'] = $ownership['ownership_numerator'];
                $building['ownership_denominator'] = $ownership['ownership_denominator'];
                $building['location'] = $building['county'] . '/' . $building['district'] . '/' . $building['section'];
                $buildings[] = $building;
            }
        }

        // Get land ownerships
        $landOwnerships = $ownerLandModel
            ->where('property_owner_id', $record['id'])
            ->findAll();

        $lands = [];
        foreach ($landOwnerships as $ownership) {
            $land = $landPlotModel->find($ownership['land_plot_id']);
            if ($land) {
                $land['ownership_numerator'] = $ownership['ownership_numerator'];
                $land['ownership_denominator'] = $ownership['ownership_denominator'];
                $land['plot_number'] = $land['land_number_main'] . '-' . $land['land_number_sub'];
                $lands[] = $land;
            }
        }

        $record['buildings'] = $buildings;
        $record['lands'] = $lands;

        return $record;
    }

    /**
     * Get property owners by urban renewal ID
     */
    public function getByUrbanRenewalId(int $urbanRenewalId): array
    {
        return $this->where('urban_renewal_id', $urbanRenewalId)
                    ->where('deleted_at', null)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get property owner with full details
     */
    public function getWithDetails(int $id): ?array
    {
        $owner = $this->find($id);
        if (!$owner) {
            return null;
        }

        return $owner; // Related data is already loaded by afterFind callback
    }

    /**
     * Calculate total areas for a property owner
     */
    public function calculateTotalAreas(int $ownerId): array
    {
        $ownerBuildingModel = model('OwnerBuildingOwnershipModel');
        $ownerLandModel = model('OwnerLandOwnershipModel');
        $buildingModel = model('BuildingModel');
        $landPlotModel = model('LandPlotModel');

        // Calculate total building area
        $buildingOwnerships = $ownerBuildingModel
            ->where('property_owner_id', $ownerId)
            ->findAll();

        $totalBuildingArea = 0;
        foreach ($buildingOwnerships as $ownership) {
            $building = $buildingModel->find($ownership['building_id']);
            if ($building && $building['building_area']) {
                $ownershipRatio = $ownership['ownership_numerator'] / $ownership['ownership_denominator'];
                $totalBuildingArea += $building['building_area'] * $ownershipRatio;
            }
        }

        // Calculate total land area
        $landOwnerships = $ownerLandModel
            ->where('property_owner_id', $ownerId)
            ->findAll();

        $totalLandArea = 0;
        foreach ($landOwnerships as $ownership) {
            $land = $landPlotModel->find($ownership['land_plot_id']);
            if ($land && $land['total_area']) {
                $ownershipRatio = $ownership['ownership_numerator'] / $ownership['ownership_denominator'];
                $totalLandArea += $land['total_area'] * $ownershipRatio;
            }
        }

        return [
            'total_land_area' => round($totalLandArea, 2),
            'total_building_area' => round($totalBuildingArea, 2)
        ];
    }

    /**
     * Update total areas for a property owner
     */
    public function updateTotalAreas(int $ownerId): bool
    {
        $totals = $this->calculateTotalAreas($ownerId);

        return $this->update($ownerId, [
            'total_land_area' => $totals['total_land_area'],
            'total_building_area' => $totals['total_building_area']
        ]);
    }
}