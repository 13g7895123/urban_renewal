<?php

namespace App\Repositories;

use App\Entities\PropertyOwner;
use App\Entities\BuildingOwnership;
use App\Entities\LandOwnership;
use App\Repositories\Contracts\PropertyOwnerRepositoryInterface;

/**
 * 所有權人 Repository 實作
 * 
 * 負責所有權人實體的資料持久化與查詢
 */
class PropertyOwnerRepository implements PropertyOwnerRepositoryInterface
{
    private $propertyOwnerModel;
    private $ownerBuildingModel;
    private $ownerLandModel;
    private $buildingModel;
    private $landPlotModel;
    private $countyModel;
    private $districtModel;
    private $sectionModel;

    public function __construct()
    {
        $this->propertyOwnerModel = model('PropertyOwnerModel');
        $this->ownerBuildingModel = model('OwnerBuildingOwnershipModel');
        $this->ownerLandModel = model('OwnerLandOwnershipModel');
        $this->buildingModel = model('BuildingModel');
        $this->landPlotModel = model('LandPlotModel');
        $this->countyModel = model('CountyModel');
        $this->districtModel = model('DistrictModel');
        $this->sectionModel = model('SectionModel');
    }

    /**
     * @inheritDoc
     */
    public function findById(int $id): ?PropertyOwner
    {
        // 暫時停用 afterFind callback 以避免重複載入
        $this->propertyOwnerModel->allowCallbacks(false);
        $data = $this->propertyOwnerModel->find($id);
        $this->propertyOwnerModel->allowCallbacks(true);

        if (!$data) {
            return null;
        }

        return $this->hydrateWithRelations($data);
    }

    /**
     * @inheritDoc
     */
    public function findByUrbanRenewalId(int $urbanRenewalId): array
    {
        // 暫時停用 afterFind callback 以避免重複載入
        $this->propertyOwnerModel->allowCallbacks(false);
        $records = $this->propertyOwnerModel
            ->where('urban_renewal_id', $urbanRenewalId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
        $this->propertyOwnerModel->allowCallbacks(true);

        return array_map(
            fn($data) => $this->hydrateWithRelations($data),
            $records
        );
    }

    /**
     * @inheritDoc
     */
    public function findEligibleVoters(int $urbanRenewalId): array
    {
        // 暫時停用 afterFind callback 以避免重複載入
        $this->propertyOwnerModel->allowCallbacks(false);
        $records = $this->propertyOwnerModel
            ->where('urban_renewal_id', $urbanRenewalId)
            ->groupStart()
            ->where('exclusion_type IS NULL')
            ->orWhere('exclusion_type', '')
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->findAll();
        $this->propertyOwnerModel->allowCallbacks(true);

        return array_map(
            fn($data) => $this->hydrateWithRelations($data),
            $records
        );
    }

    /**
     * @inheritDoc
     */
    public function save(PropertyOwner $entity): PropertyOwner
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 停用 callbacks 以避免衝突
            $this->propertyOwnerModel->allowCallbacks(false);

            // 準備主實體資料
            $data = $this->dehydrate($entity);

            if ($entity->getId()) {
                // 更新
                $result = $this->propertyOwnerModel->update($entity->getId(), $data);
                if ($result === false) {
                    $errors = $this->propertyOwnerModel->errors();
                    throw new \RuntimeException('更新所有權人失敗: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
                }
                $id = $entity->getId();
            } else {
                // 新增 - 確保有 owner_code
                if (empty($data['owner_code'])) {
                    $data['owner_code'] = $this->getNextOwnerCode($entity->getUrbanRenewalId());
                }
                $id = $this->propertyOwnerModel->insert($data);
                if ($id === false) {
                    $errors = $this->propertyOwnerModel->errors();
                    throw new \RuntimeException('新增所有權人失敗: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
                }
                $entity->setId($id);
            }

            $this->propertyOwnerModel->allowCallbacks(true);

            // 同步建物關聯
            $this->syncBuildings($id, $entity->getBuildings());

            // 同步土地關聯
            $this->syncLands($id, $entity->getLands());

            $db->transComplete();

            if (!$db->transStatus()) {
                throw new \RuntimeException('儲存所有權人失敗');
            }

            $saved = $this->findById($id);
            if ($saved === null) {
                throw new \RuntimeException('無法取得已儲存的所有權人資料');
            }

            return $saved;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'PropertyOwnerRepository::save 錯誤: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 刪除關聯
            $this->ownerBuildingModel->where('property_owner_id', $id)->delete();
            $this->ownerLandModel->where('property_owner_id', $id)->delete();

            // 刪除主實體
            $this->propertyOwnerModel->delete($id);

            $db->transComplete();
            return $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'PropertyOwnerRepository::delete 錯誤: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function countByUrbanRenewalId(int $urbanRenewalId): int
    {
        return $this->propertyOwnerModel
            ->where('urban_renewal_id', $urbanRenewalId)
            ->countAllResults();
    }

    /**
     * @inheritDoc
     */
    public function findByOwnerCode(string $ownerCode, int $urbanRenewalId): ?PropertyOwner
    {
        $this->propertyOwnerModel->allowCallbacks(false);
        $data = $this->propertyOwnerModel
            ->where('owner_code', $ownerCode)
            ->where('urban_renewal_id', $urbanRenewalId)
            ->first();
        $this->propertyOwnerModel->allowCallbacks(true);

        if (!$data) {
            return null;
        }

        return $this->hydrateWithRelations($data);
    }

    /**
     * @inheritDoc
     */
    public function findByIdNumber(string $idNumber, int $urbanRenewalId): ?PropertyOwner
    {
        $this->propertyOwnerModel->allowCallbacks(false);
        $data = $this->propertyOwnerModel
            ->where('id_number', $idNumber)
            ->where('urban_renewal_id', $urbanRenewalId)
            ->first();
        $this->propertyOwnerModel->allowCallbacks(true);

        if (!$data) {
            return null;
        }

        return $this->hydrateWithRelations($data);
    }

    /**
     * @inheritDoc
     */
    public function getNextOwnerCode(int $urbanRenewalId): string
    {
        $maxCode = $this->propertyOwnerModel
            ->where('urban_renewal_id', $urbanRenewalId)
            ->selectMax('owner_code')
            ->first();

        $nextNumber = 1;
        if ($maxCode && $maxCode['owner_code']) {
            $currentMax = intval($maxCode['owner_code']);
            $nextNumber = $currentMax + 1;
        }

        return (string)$nextNumber;
    }

    // === 私有方法 ===

    /**
     * 將資料庫記錄轉換為 Entity（含關聯資料）
     */
    private function hydrateWithRelations(array $data): PropertyOwner
    {
        $entity = PropertyOwner::fromArray($data);

        // 載入建物關聯
        $buildingOwnerships = $this->ownerBuildingModel
            ->where('property_owner_id', $data['id'])
            ->findAll();

        foreach ($buildingOwnerships as $ownership) {
            $building = $this->buildingModel->find($ownership['building_id']);
            if ($building) {
                $buildingOwnership = new BuildingOwnership(
                    (int)$building['id'],
                    (int)$ownership['ownership_numerator'],
                    (int)$ownership['ownership_denominator']
                );

                // 轉換地點代碼為名稱
                $location = $this->resolveLocationNames(
                    $building['county'] ?? null,
                    $building['district'] ?? null,
                    $building['section'] ?? null
                );

                $building['location'] = $location;
                $buildingOwnership->setBuildingData($building);
                $entity->addBuilding($buildingOwnership);
            }
        }

        // 載入土地關聯
        $landOwnerships = $this->ownerLandModel
            ->where('property_owner_id', $data['id'])
            ->findAll();

        foreach ($landOwnerships as $ownership) {
            $land = $this->landPlotModel->find($ownership['land_plot_id']);
            if ($land) {
                $landOwnership = new LandOwnership(
                    (int)$land['id'],
                    (int)$ownership['ownership_numerator'],
                    (int)$ownership['ownership_denominator']
                );
                $landOwnership->setLandData($land);
                $entity->addLand($landOwnership);
            }
        }

        return $entity;
    }

    /**
     * 解析地點代碼為中文名稱
     */
    private function resolveLocationNames(?string $countyCode, ?string $districtCode, ?string $sectionCode): string
    {
        $countyName = $countyCode ?? '';
        $districtName = $districtCode ?? '';
        $sectionName = $sectionCode ?? '';

        try {
            if ($countyCode) {
                $county = $this->countyModel->where('code', $countyCode)->first();
                if ($county) {
                    $countyName = $county['name'];

                    if ($districtCode) {
                        $district = $this->districtModel->getByCodeAndCounty($districtCode, $county['id']);
                        if ($district) {
                            $districtName = $district['name'];

                            if ($sectionCode) {
                                $section = $this->sectionModel->getByCodeAndDistrict($sectionCode, $district['id']);
                                if ($section) {
                                    $sectionName = $section['name'];
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('warning', 'resolveLocationNames 錯誤: ' . $e->getMessage());
        }

        return "{$countyName}/{$districtName}/{$sectionName}";
    }

    /**
     * 將 Entity 轉換為資料庫格式
     */
    private function dehydrate(PropertyOwner $entity): array
    {
        return [
            'urban_renewal_id' => $entity->getUrbanRenewalId(),
            'name' => $entity->getName(),
            'owner_code' => $entity->getOwnerCode(),
            'id_number' => $entity->getIdNumber(),
            'phone1' => $entity->getPhone1(),
            'phone2' => $entity->getPhone2(),
            'contact_address' => $entity->getContactAddress(),
            'household_address' => $entity->getHouseholdAddress(),
            'exclusion_type' => $entity->getExclusionType(),
            'notes' => $entity->getNotes(),
        ];
    }

    /**
     * 同步建物關聯
     */
    private function syncBuildings(int $ownerId, array $buildings): void
    {
        // 刪除舊關聯
        $this->ownerBuildingModel->where('property_owner_id', $ownerId)->delete();

        // 新增新關聯
        foreach ($buildings as $building) {
            $this->ownerBuildingModel->insert([
                'property_owner_id' => $ownerId,
                'building_id' => $building->getBuildingId(),
                'ownership_numerator' => $building->getOwnershipNumerator(),
                'ownership_denominator' => $building->getOwnershipDenominator(),
            ]);
        }
    }

    /**
     * 同步土地關聯
     */
    private function syncLands(int $ownerId, array $lands): void
    {
        // 刪除舊關聯
        $this->ownerLandModel->where('property_owner_id', $ownerId)->delete();

        // 新增新關聯
        foreach ($lands as $land) {
            $this->ownerLandModel->insert([
                'property_owner_id' => $ownerId,
                'land_plot_id' => $land->getLandPlotId(),
                'ownership_numerator' => $land->getOwnershipNumerator(),
                'ownership_denominator' => $land->getOwnershipDenominator(),
            ]);
        }
    }
}
