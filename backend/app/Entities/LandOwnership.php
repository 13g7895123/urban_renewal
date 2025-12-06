<?php

namespace App\Entities;

/**
 * 土地持分值物件
 * 
 * 封裝所有權人對土地的持分資料
 */
class LandOwnership
{
    private ?int $id = null;
    private int $landPlotId;
    private int $ownershipNumerator;
    private int $ownershipDenominator;
    
    // 土地資料（關聯載入）
    private ?string $county = null;
    private ?string $district = null;
    private ?string $section = null;
    private ?string $landNumberMain = null;
    private ?string $landNumberSub = null;
    private ?float $landArea = null;

    public function __construct(
        int $landPlotId,
        int $ownershipNumerator,
        int $ownershipDenominator
    ) {
        if ($ownershipDenominator <= 0) {
            throw new \InvalidArgumentException('持分分母必須大於 0');
        }
        if ($ownershipNumerator < 0) {
            throw new \InvalidArgumentException('持分分子不可為負數');
        }
        
        $this->landPlotId = $landPlotId;
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
        if ($this->landArea === null) {
            return 0;
        }
        return $this->landArea * $this->getOwnershipRatio();
    }

    /**
     * 取得地號顯示格式
     */
    public function getPlotNumber(): string
    {
        $main = $this->landNumberMain ?? '';
        $sub = $this->landNumberSub ?? '';
        return $sub ? "{$main}-{$sub}" : $main;
    }

    // === Getters ===
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLandPlotId(): int
    {
        return $this->landPlotId;
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

    public function getLandNumberMain(): ?string
    {
        return $this->landNumberMain;
    }

    public function getLandNumberSub(): ?string
    {
        return $this->landNumberSub;
    }

    public function getLandArea(): ?float
    {
        return $this->landArea;
    }

    // === Setters (用於 hydration) ===

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setLandData(array $data): self
    {
        $this->county = $data['county'] ?? null;
        $this->district = $data['district'] ?? null;
        $this->section = $data['section'] ?? null;
        $this->landNumberMain = $data['land_number_main'] ?? null;
        $this->landNumberSub = $data['land_number_sub'] ?? null;
        $this->landArea = isset($data['land_area']) ? (float)$data['land_area'] : null;
        return $this;
    }

    /**
     * 轉換為陣列
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'land_plot_id' => $this->landPlotId,
            'county' => $this->county,
            'district' => $this->district,
            'section' => $this->section,
            'land_number_main' => (string)($this->landNumberMain ?? ''),
            'land_number_sub' => (string)($this->landNumberSub ?? ''),
            'plot_number' => $this->getPlotNumber(),
            'land_area' => $this->landArea,
            'total_area' => $this->landArea, // 前端相容性別名
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
            $data['land_plot_id'] ?? $data['land']['id'] ?? 0,
            (int)($data['ownership_numerator'] ?? 1),
            (int)($data['ownership_denominator'] ?? 1)
        );

        if (isset($data['id'])) {
            $entity->setId($data['id']);
        }

        // 載入土地資料
        $landData = $data['land'] ?? $data;
        $entity->setLandData($landData);

        return $entity;
    }
}
