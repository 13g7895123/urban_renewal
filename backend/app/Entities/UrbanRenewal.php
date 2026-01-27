<?php

namespace App\Entities;

/**
 * 更新會實體
 */
class UrbanRenewal
{
    private ?int $id = null;
    private ?int $companyId = null;
    private string $name;
    private ?string $address = null;
    private ?string $phone = null;
    private ?string $fax = null;
    private ?string $email = null;
    private ?string $contactPerson = null;
    private ?string $chairmanName = null;
    private ?string $chairmanPhone = null;
    private ?string $representative = null;
    private ?string $notes = null;
    private ?int $assignedAdminId = null;
    private ?string $assignedAdminName = null;
    private ?int $memberCount = null;
    private ?float $totalLandArea = null;
    private ?float $totalBuildingArea = null;

    private ?\DateTime $createdAt = null;
    private ?\DateTime $updatedAt = null;

    // 關聯資料
    private ?string $companyName = null;
    private ?int $propertyOwnerCount = null;
    private ?int $meetingCount = null;

    public function __construct(string $name)
    {
        $this->setName($name);
    }

    // === Getters ===

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyId(): ?int
    {
        return $this->companyId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getContactPerson(): ?string
    {
        return $this->contactPerson;
    }

    public function getChairmanName(): ?string
    {
        return $this->chairmanName;
    }

    public function getChairmanPhone(): ?string
    {
        return $this->chairmanPhone;
    }

    public function getRepresentative(): ?string
    {
        return $this->representative;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getAssignedAdminId(): ?int
    {
        return $this->assignedAdminId;
    }

    public function getAssignedAdminName(): ?string
    {
        return $this->assignedAdminName;
    }

    public function getMemberCount(): ?int
    {
        return $this->memberCount;
    }

    public function getTotalLandArea(): ?float
    {
        return $this->totalLandArea;
    }

    public function getTotalBuildingArea(): ?float
    {
        return $this->totalBuildingArea;
    }

    // === Setters ===

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setCompanyId(?int $companyId): self
    {
        $this->companyId = $companyId;
        return $this;
    }

    public function setName(string $name): self
    {
        $name = trim($name);
        if (empty($name)) {
            throw new \InvalidArgumentException('更新會名稱不可為空');
        }
        $this->name = $name;
        return $this;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address ? trim($address) : null;
        return $this;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone ? trim($phone) : null;
        return $this;
    }

    public function setFax(?string $fax): self
    {
        $this->fax = $fax ? trim($fax) : null;
        return $this;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email ? trim($email) : null;
        return $this;
    }

    public function setContactPerson(?string $contactPerson): self
    {
        $this->contactPerson = $contactPerson ? trim($contactPerson) : null;
        return $this;
    }

    public function setChairmanName(?string $chairmanName): self
    {
        $this->chairmanName = $chairmanName ? trim($chairmanName) : null;
        return $this;
    }

    public function setChairmanPhone(?string $chairmanPhone): self
    {
        $this->chairmanPhone = $chairmanPhone ? trim($chairmanPhone) : null;
        return $this;
    }

    public function setRepresentative(?string $representative): self
    {
        $this->representative = $representative ? trim($representative) : null;
        return $this;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes ? trim($notes) : null;
        return $this;
    }

    public function setAssignedAdminId(?int $assignedAdminId): self
    {
        $this->assignedAdminId = $assignedAdminId;
        return $this;
    }

    public function setAssignedAdminName(?string $assignedAdminName): self
    {
        $this->assignedAdminName = $assignedAdminName;
        return $this;
    }

    public function setMemberCount(?int $count): self
    {
        $this->memberCount = $count;
        return $this;
    }

    public function setTotalLandArea(?float $area): self
    {
        $this->totalLandArea = $area;
        return $this;
    }

    public function setTotalBuildingArea(?float $area): self
    {
        $this->totalBuildingArea = $area;
        return $this;
    }

    public function setCompanyName(?string $name): self
    {
        $this->companyName = $name;
        return $this;
    }

    public function setPropertyOwnerCount(?int $count): self
    {
        $this->propertyOwnerCount = $count;
        return $this;
    }

    public function setMeetingCount(?int $count): self
    {
        $this->meetingCount = $count;
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

    /**
     * 轉換為陣列
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'company_name' => $this->companyName,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'fax' => $this->fax,
            'email' => $this->email,
            'contact_person' => $this->contactPerson,
            'chairman_name' => $this->chairmanName,
            'chairman_phone' => $this->chairmanPhone,
            'representative' => $this->representative,
            'notes' => $this->notes,
            'assigned_admin_id' => $this->assignedAdminId,
            'assigned_admin_name' => $this->assignedAdminName,
            'member_count' => $this->memberCount,
            'total_land_area' => $this->totalLandArea,
            'total_building_area' => $this->totalBuildingArea,
            'property_owner_count' => $this->propertyOwnerCount,
            'meeting_count' => $this->meetingCount,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 從陣列建立實體
     */
    public static function fromArray(array $data): self
    {
        $entity = new self($data['name']);

        if (isset($data['id'])) $entity->setId((int)$data['id']);
        if (isset($data['company_id'])) $entity->setCompanyId((int)$data['company_id']);

        $entity->setAddress($data['address'] ?? null);
        $entity->setPhone($data['phone'] ?? null);
        $entity->setFax($data['fax'] ?? null);
        $entity->setEmail($data['email'] ?? null);
        $entity->setContactPerson($data['contact_person'] ?? null);
        $entity->setChairmanName($data['chairman_name'] ?? null);
        $entity->setChairmanPhone($data['chairman_phone'] ?? null);
        $entity->setRepresentative($data['representative'] ?? null);
        $entity->setNotes($data['notes'] ?? null);

        if (isset($data['assigned_admin_id'])) $entity->setAssignedAdminId((int)$data['assigned_admin_id']);
        if (isset($data['assigned_admin_name'])) $entity->setAssignedAdminName($data['assigned_admin_name']);
        if (isset($data['member_count'])) $entity->setMemberCount((int)$data['member_count']);
        if (isset($data['total_land_area'])) $entity->setTotalLandArea((float)$data['total_land_area']);
        if (isset($data['total_building_area'])) $entity->setTotalBuildingArea((float)$data['total_building_area']);
        if (isset($data['company_name'])) $entity->setCompanyName($data['company_name']);
        if (isset($data['property_owner_count'])) $entity->setPropertyOwnerCount((int)$data['property_owner_count']);
        if (isset($data['meeting_count'])) $entity->setMeetingCount((int)$data['meeting_count']);

        if (isset($data['created_at'])) $entity->setCreatedAt(new \DateTime($data['created_at']));
        if (isset($data['updated_at'])) $entity->setUpdatedAt(new \DateTime($data['updated_at']));

        return $entity;
    }
}
