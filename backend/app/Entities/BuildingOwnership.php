<?php

namespace App\Entities;

/**
 * 建物持分值物件
 * 
 * 封裝所有權人對建物的持分資料
 */
class BuildingOwnership
{
    private ?int $id = null;
    private int $buildingId;
    private int $ownershipNumerator;
    private int $ownershipDenominator;
    
    // 建物資料（關聯載入）
    private ?string $county = null;
    private ?string $district = null;
    private ?string $section = null;
    private ?string $buildingNumberMain = null;
    private ?string $buildingNumberSub = null;
    private ?float $buildingArea = null;
    private ?string $buildingAddress = null;
    private ?string $location = null;

    public function __construct(
        int $buildingId,
        int $ownershipNumerator,
        int $ownershipDenominator
    ) {
        if ($ownershipDenominator <= 0) {
            throw new \InvalidArgumentException('持分分母必須大於 0');
        }
        if ($ownershipNumerator < 0) {
            throw new \InvalidArgumentException('持分分子不可為負數');
        }
        
        $this->buildingId = $buildingId;
        $this->ownershipNumerator = $ownershipNumerator;
        $this->ownershipDenominator = $ownershipDenominator;
    }

    /**
     * 計算持分比例
     */
    public function getOwnershipRatio(): float
    {
        return $this->ownershipNumerator / $this->ownershipDenominator;
    }

    /**
     * 計算所有權面積
     */
    public function calculateOwnedArea(): float
    {
        if ($this->buildingArea === null) {
            return 0;
        }
        return $this->buildingArea * $this->getOwnershipRatio();
    }

    /**
     * 取得建號顯示格式
     */
    public function getBuildingNumber(): string
    {
        $main = $this->buildingNumberMain ?? '';
        $sub = $this->buildingNumberSub ?? '';
        return $sub ? "{$main}-{$sub}" : $main;
    }

    // === Getters ===
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBuildingId(): int
    {
        return $this->buildingId;
    }

    public function getOwnershipNumerator(): int
    {
        return $this->ownershipNumerator;
    }

    public function getOwnershipDenominator(): int
    {
        return $this->ownershipDenominator;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function getBuildingNumberMain(): ?string
    {
        return $this->buildingNumberMain;
    }

    public function getBuildingNumberSub(): ?string
    {
        return $this->buildingNumberSub;
    }

    public function getBuildingArea(): ?float
    {
        return $this->buildingArea;
    }

    public function getBuildingAddress(): ?string
    {
        return $this->buildingAddress;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    // === Setters (用於 hydration) ===

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setBuildingData(array $data): self
    {
        $this->county = $data['county'] ?? null;
        $this->district = $data['district'] ?? null;
        $this->section = $data['section'] ?? null;
        $this->buildingNumberMain = $data['building_number_main'] ?? null;
        $this->buildingNumberSub = $data['building_number_sub'] ?? null;
        $this->buildingArea = isset($data['building_area']) ? (float)$data['building_area'] : null;
        $this->buildingAddress = $data['building_address'] ?? null;
        $this->location = $data['location'] ?? null;
        return $this;
    }

    /**
     * 轉換為陣列
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'building_id' => $this->buildingId,
            'county' => $this->county,
            'district' => $this->district,
            'section' => $this->section,
            'building_number_main' => (string)($this->buildingNumberMain ?? ''),
            'building_number_sub' => (string)($this->buildingNumberSub ?? ''),
            'building_number' => $this->getBuildingNumber(),
            'building_area' => $this->buildingArea,
            'building_address' => $this->buildingAddress,
            'location' => $this->location,
            'ownership_numerator' => $this->ownershipNumerator,
            'ownership_denominator' => $this->ownershipDenominator,
            'ownership_ratio' => $this->getOwnershipRatio(),
            'owned_area' => $this->calculateOwnedArea(),
        ];
    }

    /**
     * 從陣列建立
     */
    public static function fromArray(array $data): self
    {
        $entity = new self(
            $data['building_id'] ?? $data['building']['id'] ?? 0,
            (int)($data['ownership_numerator'] ?? 1),
            (int)($data['ownership_denominator'] ?? 1)
        );

        if (isset($data['id'])) {
            $entity->setId($data['id']);
        }

        // 載入建物資料
        $buildingData = $data['building'] ?? $data;
        $entity->setBuildingData($buildingData);

        return $entity;
    }
}
