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
        ],
        'exclusion_type' => [
            'in_list' => '排除類型必須是以下其中之一：法院囑託查封、假扣押、假處分、破產登記、未經繼承'
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
        $countyModel = model('CountyModel');
        $districtModel = model('DistrictModel');
        $sectionModel = model('SectionModel');
        
        foreach ($buildingOwnerships as $ownership) {
            $building = $buildingModel->find($ownership['building_id']);
            if ($building) {
                $building['ownership_numerator'] = $ownership['ownership_numerator'];
                $building['ownership_denominator'] = $ownership['ownership_denominator'];
                
                // 將代碼轉換為中文名稱
                $countyName = $building['county'];
                $districtName = $building['district'];
                $sectionName = $building['section'];
                
                try {
                    if ($countyModel && !empty($building['county'])) {
                        $county = $countyModel->where('code', $building['county'])->first();
                        if ($county) {
                            $countyName = $county['name'];
                        }
                    }
                } catch (\Exception $e) {
                    log_message('warning', 'Failed to fetch county name: ' . $e->getMessage());
                }

                try {
                    if ($districtModel && !empty($building['district']) && !empty($building['county'])) {
                        // Get county ID first
                        $county = $countyModel->where('code', $building['county'])->first();
                        if ($county) {
                            // Use the existing method from DistrictModel
                            $district = $districtModel->getByCodeAndCounty($building['district'], $county['id']);
                            if ($district) {
                                $districtName = $district['name'];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    log_message('warning', 'Failed to fetch district name: ' . $e->getMessage());
                }

                try {
                    if ($sectionModel && !empty($building['section']) && !empty($building['county']) && !empty($building['district'])) {
                        // Get county ID first
                        $county = $countyModel->where('code', $building['county'])->first();
                        if ($county) {
                            // Get district ID
                            $district = $districtModel->getByCodeAndCounty($building['district'], $county['id']);
                            if ($district) {
                                // Use the existing method from SectionModel
                                $section = $sectionModel->getByCodeAndDistrict($building['section'], $district['id']);
                                if ($section) {
                                    $sectionName = $section['name'];
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    log_message('warning', 'Failed to fetch section name: ' . $e->getMessage());
                }
                
                $building['location'] = $countyName . '/' . $districtName . '/' . $sectionName;
                
                // 確保建號欄位格式正確（前端相容性）
                $building['building_number_main'] = (string)($building['building_number_main'] ?? '');
                $building['building_number_sub'] = (string)($building['building_number_sub'] ?? '');
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
                // 確保地號欄位格式正確（前端相容性）
                $land['land_number_main'] = (string)($land['land_number_main'] ?? '');
                $land['land_number_sub'] = (string)($land['land_number_sub'] ?? '');
                $land['plot_number'] = $land['land_number_main'] . '-' . $land['land_number_sub'];
                $land['total_area'] = $land['land_area']; // 為前端相容性添加 total_area 別名
                $lands[] = $land;
            }
        }

        $record['buildings'] = $buildings;
        $record['lands'] = $lands;

        // Calculate and add total areas for display in the list
        $totalAreas = $this->calculateTotalAreas($record['id']);
        $record['total_land_area'] = $totalAreas['total_land_area'];
        $record['total_building_area'] = $totalAreas['total_building_area'];

        return $record;
    }

    /**
     * Get property owners by urban renewal ID
     */
    public function getByUrbanRenewalId(int $urbanRenewalId): array
    {
        return $this->where('urban_renewal_id', $urbanRenewalId)
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
            if ($land && isset($land['land_area']) && $land['land_area']) {
                $ownershipRatio = $ownership['ownership_numerator'] / $ownership['ownership_denominator'];
                $totalLandArea += $land['land_area'] * $ownershipRatio;
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
        try {
            $totals = $this->calculateTotalAreas($ownerId);

            log_message('info', 'Calculated totals for owner ' . $ownerId . ': ' . json_encode($totals, JSON_UNESCAPED_UNICODE));

            // Check if the property owner exists
            $owner = $this->find($ownerId);
            if (!$owner) {
                log_message('warning', 'Property owner not found for ID: ' . $ownerId);
                return false;
            }

            // Only update if we have meaningful totals to update
            $updateData = [];
            if (isset($totals['total_land_area']) && $totals['total_land_area'] >= 0) {
                $updateData['total_land_area'] = $totals['total_land_area'];
            }
            if (isset($totals['total_building_area']) && $totals['total_building_area'] >= 0) {
                $updateData['total_building_area'] = $totals['total_building_area'];
            }

            // If no data to update, consider it successful (no change needed)
            if (empty($updateData)) {
                log_message('info', 'No total area updates needed for owner: ' . $ownerId);
                return true;
            }

            log_message('info', 'Updating owner ' . $ownerId . ' with data: ' . json_encode($updateData, JSON_UNESCAPED_UNICODE));

            $result = $this->update($ownerId, $updateData);

            if (!$result) {
                $errors = $this->errors();
                log_message('error', 'Failed to update total areas for owner ' . $ownerId . ': ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
            }

            return $result;

        } catch (\Exception $e) {
            log_message('error', 'Exception in updateTotalAreas for owner ' . $ownerId . ': ' . $e->getMessage());
            return false; // Don't let this break the main operation
        }
    }
}