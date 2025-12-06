<?php

namespace App\Services;

use App\Entities\PropertyOwner;
use App\Entities\BuildingOwnership;
use App\Entities\LandOwnership;
use App\Repositories\PropertyOwnerRepository;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;

/**
 * 所有權人服務
 * 
 * 處理所有權人相關的業務邏輯
 */
class PropertyOwnerService
{
    private PropertyOwnerRepository $repository;
    private AuthorizationService $authService;
    private $buildingModel;
    private $landPlotModel;

    public function __construct(
        ?PropertyOwnerRepository $repository = null,
        ?AuthorizationService $authService = null
    ) {
        $this->repository = $repository ?? new PropertyOwnerRepository();
        $this->authService = $authService ?? new AuthorizationService();
        $this->buildingModel = model('BuildingModel');
        $this->landPlotModel = model('LandPlotModel');
    }

    /**
     * 取得更新會的所有權人列表
     */
    public function getByUrbanRenewal(array $user, int $urbanRenewalId): array
    {
        // 權限檢查
        $this->authService->assertCanAccessUrbanRenewal($user, $urbanRenewalId);

        $entities = $this->repository->findByUrbanRenewalId($urbanRenewalId);
        
        return array_map(fn($e) => $e->toArray(), $entities);
    }

    /**
     * 取得單一所有權人詳情
     */
    public function getDetail(array $user, int $id): array
    {
        $entity = $this->repository->findById($id);
        
        if (!$entity) {
            throw new NotFoundException('所有權人不存在');
        }

        // 權限檢查
        $this->authService->assertCanAccessUrbanRenewal($user, $entity->getUrbanRenewalId());

        return $entity->toArray();
    }

    /**
     * 建立所有權人
     */
    public function create(array $user, array $data): array
    {
        // 驗證必填欄位
        $this->validateRequiredFields($data);

        // 權限檢查
        $this->authService->assertCanAccessUrbanRenewal($user, (int)$data['urban_renewal_id']);

        // 建立 Entity
        $entity = PropertyOwner::fromArray($data);

        // 處理建物
        if (!empty($data['buildings'])) {
            foreach ($data['buildings'] as $index => $buildingData) {
                try {
                    $building = $this->resolveBuildingOwnership((int)$data['urban_renewal_id'], $buildingData);
                    if ($building) {
                        $entity->addBuilding($building);
                    }
                } catch (\Exception $e) {
                    throw new ValidationException("建物資料驗證失敗 (建物 #" . ($index + 1) . "): " . $e->getMessage());
                }
            }
        }

        // 處理土地
        if (!empty($data['lands'])) {
            foreach ($data['lands'] as $index => $landData) {
                try {
                    $land = $this->resolveLandOwnership((int)$data['urban_renewal_id'], $landData);
                    if ($land) {
                        $entity->addLand($land);
                    }
                } catch (\Exception $e) {
                    throw new ValidationException("土地資料驗證失敗 (土地 #" . ($index + 1) . "): " . $e->getMessage());
                }
            }
        }

        // 儲存
        $saved = $this->repository->save($entity);

        return $saved->toArray();
    }

    /**
     * 更新所有權人
     */
    public function update(array $user, int $id, array $data): array
    {
        $entity = $this->repository->findById($id);
        
        if (!$entity) {
            throw new NotFoundException('所有權人不存在');
        }

        // 權限檢查
        $this->authService->assertCanAccessUrbanRenewal($user, $entity->getUrbanRenewalId());

        // 更新 Entity 屬性
        if (isset($data['owner_name']) || isset($data['name'])) {
            $entity->setName($data['owner_name'] ?? $data['name']);
        }
        if (array_key_exists('identity_number', $data) || array_key_exists('id_number', $data)) {
            $entity->setIdNumber($data['identity_number'] ?? $data['id_number'] ?? null);
        }
        if (array_key_exists('phone1', $data)) {
            $entity->setPhone1($data['phone1']);
        }
        if (array_key_exists('phone2', $data)) {
            $entity->setPhone2($data['phone2']);
        }
        if (array_key_exists('contact_address', $data)) {
            $entity->setContactAddress($data['contact_address']);
        }
        if (array_key_exists('registered_address', $data) || array_key_exists('household_address', $data)) {
            $entity->setHouseholdAddress($data['registered_address'] ?? $data['household_address'] ?? null);
        }
        if (array_key_exists('exclusion_type', $data)) {
            $entity->setExclusionType($data['exclusion_type']);
        }
        if (array_key_exists('notes', $data)) {
            $entity->setNotes($data['notes']);
        }

        // 處理建物更新
        if (isset($data['buildings'])) {
            $entity->clearBuildings();
            foreach ($data['buildings'] as $index => $buildingData) {
                try {
                    $building = $this->resolveBuildingOwnership($entity->getUrbanRenewalId(), $buildingData);
                    if ($building) {
                        $entity->addBuilding($building);
                    }
                } catch (\Exception $e) {
                    throw new ValidationException("建物資料驗證失敗 (建物 #" . ($index + 1) . "): " . $e->getMessage());
                }
            }
        }

        // 處理土地更新
        if (isset($data['lands'])) {
            $entity->clearLands();
            foreach ($data['lands'] as $index => $landData) {
                try {
                    $land = $this->resolveLandOwnership($entity->getUrbanRenewalId(), $landData);
                    if ($land) {
                        $entity->addLand($land);
                    }
                } catch (\Exception $e) {
                    throw new ValidationException("土地資料驗證失敗 (土地 #" . ($index + 1) . "): " . $e->getMessage());
                }
            }
        }

        // 儲存
        $saved = $this->repository->save($entity);

        return $saved->toArray();
    }

    /**
     * 刪除所有權人
     */
    public function delete(array $user, int $id): bool
    {
        $entity = $this->repository->findById($id);
        
        if (!$entity) {
            throw new NotFoundException('所有權人不存在');
        }

        // 權限檢查
        $this->authService->assertCanAccessUrbanRenewal($user, $entity->getUrbanRenewalId());

        return $this->repository->delete($id);
    }

    /**
     * 取得合格投票人列表
     */
    public function getEligibleVoters(array $user, int $urbanRenewalId): array
    {
        $this->authService->assertCanAccessUrbanRenewal($user, $urbanRenewalId);

        $entities = $this->repository->findEligibleVoters($urbanRenewalId);
        
        return array_map(fn($e) => $e->toArray(), $entities);
    }

    /**
     * 計算更新會統計資料
     */
    public function calculateStatistics(int $urbanRenewalId): array
    {
        $entities = $this->repository->findByUrbanRenewalId($urbanRenewalId);
        
        $totalLandArea = 0;
        $totalBuildingArea = 0;
        $eligibleCount = 0;

        foreach ($entities as $entity) {
            $totalLandArea += $entity->calculateTotalLandArea();
            $totalBuildingArea += $entity->calculateTotalBuildingArea();
            if ($entity->isEligibleVoter()) {
                $eligibleCount++;
            }
        }

        return [
            'total_members' => count($entities),
            'eligible_voters' => $eligibleCount,
            'total_land_area' => round($totalLandArea, 2),
            'total_building_area' => round($totalBuildingArea, 2),
        ];
    }

    /**
     * 取得更新會的所有建物（不重複）
     */
    public function getAllBuildingsByUrbanRenewal(array $user, int $urbanRenewalId): array
    {
        $this->authService->assertCanAccessUrbanRenewal($user, $urbanRenewalId);

        $entities = $this->repository->findByUrbanRenewalId($urbanRenewalId);
        
        $allBuildings = [];
        $buildingIds = [];

        foreach ($entities as $entity) {
            foreach ($entity->getBuildings() as $building) {
                $buildingId = $building->getBuildingId();
                if (!in_array($buildingId, $buildingIds)) {
                    $buildingIds[] = $buildingId;
                    $allBuildings[] = $building->toArray();
                }
            }
        }

        return $allBuildings;
    }

    // === 私有方法 ===

    /**
     * 驗證必填欄位
     */
    private function validateRequiredFields(array $data): void
    {
        $errors = [];

        if (empty($data['urban_renewal_id'])) {
            $errors['urban_renewal_id'] = '所屬更新會為必填項目';
        }

        if (empty($data['owner_name']) && empty($data['name'])) {
            $errors['owner_name'] = '所有權人名稱為必填項目';
        }

        if (!empty($errors)) {
            throw new ValidationException('資料驗證失敗', $errors);
        }
    }

    /**
     * 解析並建立建物持分
     */
    private function resolveBuildingOwnership(int $urbanRenewalId, array $data): ?BuildingOwnership
    {
        // 建立或取得建物
        $buildingId = $this->buildingModel->createIfNotExists([
            'urban_renewal_id' => $urbanRenewalId,
            'county' => $data['county'],
            'district' => $data['district'],
            'section' => $data['section'],
            'building_number_main' => $data['building_number_main'],
            'building_number_sub' => $data['building_number_sub'] ?? '',
            'building_area' => $data['building_area'] ?? null,
            'building_address' => $data['building_address'] ?? null,
        ]);

        if (!$buildingId) {
            $errors = $this->buildingModel->errors();
            $errorMsg = !empty($errors) ? implode('; ', $errors) : '建物建立失敗';
            throw new \RuntimeException($errorMsg);
        }

        $building = $this->buildingModel->find($buildingId);

        return BuildingOwnership::fromArray([
            'building_id' => $buildingId,
            'building' => $building,
            'ownership_numerator' => $data['ownership_numerator'] ?? 1,
            'ownership_denominator' => $data['ownership_denominator'] ?? 1,
        ]);
    }

    /**
     * 解析並建立土地持分
     */
    private function resolveLandOwnership(int $urbanRenewalId, array $data): ?LandOwnership
    {
        // 查找土地
        $landPlot = null;
        $plotNumber = $data['plot_number'] ?? null;

        if ($plotNumber) {
            // 方法 1: 直接用 land_number_main 查詢
            $landPlot = $this->landPlotModel
                ->where('land_number_main', $plotNumber)
                ->where('urban_renewal_id', $urbanRenewalId)
                ->first();

            // 方法 2: 嘗試分解地號查詢
            if (!$landPlot && strpos($plotNumber, '-') !== false) {
                $parts = explode('-', $plotNumber);
                $landNumberMain = $parts[0];
                $landNumberSub = $parts[1] ?? '';

                $query = $this->landPlotModel
                    ->where('land_number_main', $landNumberMain)
                    ->where('urban_renewal_id', $urbanRenewalId);

                if (!empty($landNumberSub)) {
                    $query->where('land_number_sub', $landNumberSub);
                }

                $landPlot = $query->first();
            }

            // 方法 3: CONCAT 查詢
            if (!$landPlot) {
                $landPlot = $this->landPlotModel
                    ->where("CONCAT(land_number_main, '-', land_number_sub)", $plotNumber)
                    ->where('urban_renewal_id', $urbanRenewalId)
                    ->first();
            }
        }

        if (!$landPlot) {
            log_message('warning', "找不到土地: plot_number={$plotNumber}, urban_renewal_id={$urbanRenewalId}");
            return null;
        }

        return LandOwnership::fromArray([
            'land_plot_id' => $landPlot['id'],
            'land' => $landPlot,
            'ownership_numerator' => $data['ownership_numerator'] ?? 1,
            'ownership_denominator' => $data['ownership_denominator'] ?? 1,
        ]);
    }
}
