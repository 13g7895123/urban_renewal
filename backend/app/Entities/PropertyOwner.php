<?php

namespace App\Entities;

/**
 * 所有權人實體
 * 
 * 封裝所有權人的核心業務資料與行為
 */
class PropertyOwner
{
    private ?int $id = null;
    private int $urbanRenewalId;
    private string $name;
    private ?string $idNumber = null;
    private ?string $ownerCode = null;
    private ?string $phone1 = null;
    private ?string $phone2 = null;
    private ?string $contactAddress = null;
    private ?string $householdAddress = null;
    private ?string $exclusionType = null;
    private ?string $notes = null;
    
    /** @var BuildingOwnership[] */
    private array $buildings = [];
    
    /** @var LandOwnership[] */
    private array $lands = [];
    
    private ?\DateTime $createdAt = null;
    private ?\DateTime $updatedAt = null;

    // 有效的排除類型
    private const VALID_EXCLUSION_TYPES = [
        '法院囑託查封',
        '假扣押',
        '假處分',
        '破產登記',
        '未經繼承'
    ];

    // === 建構子 ===
    
    public function __construct(int $urbanRenewalId, string $name)
    {
        $this->urbanRenewalId = $urbanRenewalId;
        $this->setName($name);
    }

    // === 業務邏輯方法 ===
    
    /**
     * 檢查是否被排除（法院囑託查封等）
     */
    public function isExcluded(): bool
    {
        return !empty($this->exclusionType);
    }

    /**
     * 檢查是否有效的投票人（未被排除）
     */
    public function isEligibleVoter(): bool
    {
        return !$this->isExcluded();
    }

    /**
     * 計算土地總持分面積
     */
    public function calculateTotalLandArea(): float
    {
        $total = 0;
        foreach ($this->lands as $land) {
            $total += $land->calculateOwnedArea();
        }
        return round($total, 2);
    }

    /**
     * 計算建物總持分面積
     */
    public function calculateTotalBuildingArea(): float
    {
        $total = 0;
        foreach ($this->buildings as $building) {
            $total += $building->calculateOwnedArea();
        }
        return round($total, 2);
    }

    /**
     * 驗證身分證字號格式（台灣）
     */
    public function hasValidIdNumber(): bool
    {
        if (empty($this->idNumber)) {
            return false;
        }
        // 台灣身分證驗證邏輯
        return preg_match('/^[A-Z][12]\d{8}$/', $this->idNumber) === 1;
    }

    /**
     * 新增建物持分
     */
    public function addBuilding(BuildingOwnership $building): void
    {
        $this->buildings[] = $building;
    }

    /**
     * 清空建物持分
     */
    public function clearBuildings(): void
    {
        $this->buildings = [];
    }

    /**
     * 新增土地持分
     */
    public function addLand(LandOwnership $land): void
    {
        $this->lands[] = $land;
    }

    /**
     * 清空土地持分
     */
    public function clearLands(): void
    {
        $this->lands = [];
    }

    /**
     * 取得有效排除類型列表
     */
    public static function getValidExclusionTypes(): array
    {
        return self::VALID_EXCLUSION_TYPES;
    }

    // === Getters ===
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrbanRenewalId(): int
    {
        return $this->urbanRenewalId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIdNumber(): ?string
    {
        return $this->idNumber;
    }

    public function getOwnerCode(): ?string
    {
        return $this->ownerCode;
    }

    public function getPhone1(): ?string
    {
        return $this->phone1;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function getContactAddress(): ?string
    {
        return $this->contactAddress;
    }

    public function getHouseholdAddress(): ?string
    {
        return $this->householdAddress;
    }

    public function getExclusionType(): ?string
    {
        return $this->exclusionType;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getBuildings(): array
    {
        return $this->buildings;
    }

    public function getLands(): array
    {
        return $this->lands;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    // === Setters ===

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setName(string $name): self
    {
        $name = trim($name);
        if (empty($name)) {
            throw new \InvalidArgumentException('所有權人名稱不可為空');
        }
        if (mb_strlen($name) > 100) {
            throw new \InvalidArgumentException('所有權人名稱不可超過 100 字元');
        }
        $this->name = $name;
        return $this;
    }

    public function setIdNumber(?string $idNumber): self
    {
        $this->idNumber = $idNumber ? trim($idNumber) : null;
        return $this;
    }

    public function setOwnerCode(?string $ownerCode): self
    {
        $this->ownerCode = $ownerCode ? trim($ownerCode) : null;
        return $this;
    }

    public function setPhone1(?string $phone1): self
    {
        $this->phone1 = $phone1 ? trim($phone1) : null;
        return $this;
    }

    public function setPhone2(?string $phone2): self
    {
        $this->phone2 = $phone2 ? trim($phone2) : null;
        return $this;
    }

    public function setContactAddress(?string $contactAddress): self
    {
        $this->contactAddress = $contactAddress ? trim($contactAddress) : null;
        return $this;
    }

    public function setHouseholdAddress(?string $householdAddress): self
    {
        $this->householdAddress = $householdAddress ? trim($householdAddress) : null;
        return $this;
    }

    public function setExclusionType(?string $type): self
    {
        if ($type !== null && $type !== '' && !in_array($type, self::VALID_EXCLUSION_TYPES)) {
            throw new \InvalidArgumentException(
                '無效的排除類型，必須是以下其中之一：' . implode('、', self::VALID_EXCLUSION_TYPES)
            );
        }
        $this->exclusionType = $type ?: null;
        return $this;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes ? trim($notes) : null;
        return $this;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // === 序列化 ===
    
    /**
     * 轉換為陣列（供 API 回應使用）
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'urban_renewal_id' => $this->urbanRenewalId,
            'name' => $this->name,
            'owner_code' => $this->ownerCode,
            'id_number' => $this->idNumber,
            'phone1' => $this->phone1,
            'phone2' => $this->phone2,
            'contact_address' => $this->contactAddress,
            'household_address' => $this->householdAddress,
            'exclusion_type' => $this->exclusionType,
            'notes' => $this->notes,
            'total_land_area' => $this->calculateTotalLandArea(),
            'total_building_area' => $this->calculateTotalBuildingArea(),
            'is_eligible_voter' => $this->isEligibleVoter(),
            'buildings' => array_map(fn($b) => $b->toArray(), $this->buildings),
            'lands' => array_map(fn($l) => $l->toArray(), $this->lands),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 從陣列建立實體
     */
    public static function fromArray(array $data): self
    {
        $entity = new self(
            (int)$data['urban_renewal_id'],
            $data['name'] ?? $data['owner_name'] ?? ''
        );
        
        if (isset($data['id'])) {
            $entity->setId((int)$data['id']);
        }
        
        $entity->setOwnerCode($data['owner_code'] ?? null);
        $entity->setIdNumber($data['id_number'] ?? $data['identity_number'] ?? null);
        $entity->setPhone1($data['phone1'] ?? null);
        $entity->setPhone2($data['phone2'] ?? null);
        $entity->setContactAddress($data['contact_address'] ?? null);
        $entity->setHouseholdAddress($data['household_address'] ?? $data['registered_address'] ?? null);
        $entity->setExclusionType($data['exclusion_type'] ?? null);
        $entity->setNotes($data['notes'] ?? null);
        
        if (isset($data['created_at'])) {
            $entity->setCreatedAt(new \DateTime($data['created_at']));
        }
        if (isset($data['updated_at'])) {
            $entity->setUpdatedAt(new \DateTime($data['updated_at']));
        }
        
        return $entity;
    }
}
