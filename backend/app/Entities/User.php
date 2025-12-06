<?php

namespace App\Entities;

/**
 * 使用者實體
 */
class User
{
    private ?int $id = null;
    private string $username;
    private ?string $email = null;
    private ?string $password = null;
    private string $role = 'member';
    private ?int $urbanRenewalId = null;
    private ?int $companyId = null;
    private ?int $propertyOwnerId = null;
    private bool $isActive = true;
    private bool $isCompanyManager = false;
    private ?string $displayName = null;
    private ?string $phone = null;
    
    private ?\DateTime $createdAt = null;
    private ?\DateTime $updatedAt = null;
    private ?\DateTime $lastLoginAt = null;

    // 關聯資料
    private ?string $urbanRenewalName = null;
    private ?string $companyName = null;

    // 有效的角色
    public const VALID_ROLES = ['admin', 'chairman', 'member', 'observer'];

    public function __construct(string $username)
    {
        $this->setUsername($username);
    }

    // === 業務邏輯方法 ===

    /**
     * 檢查是否為系統管理員
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * 檢查是否為企業管理者
     */
    public function isCompanyManager(): bool
    {
        return $this->isCompanyManager;
    }

    /**
     * 檢查是否可以管理指定使用者
     */
    public function canManageUser(User $targetUser): bool
    {
        // 不能管理自己
        if ($this->id === $targetUser->getId()) {
            return false;
        }

        // 管理員可以管理所有人
        if ($this->isAdmin()) {
            return true;
        }

        // 企業管理者可以管理同企業的非管理員
        if ($this->isCompanyManager()) {
            if ($targetUser->isAdmin()) {
                return false;
            }
            return $this->companyId === $targetUser->getCompanyId();
        }

        return false;
    }

    // === Getters ===
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getUrbanRenewalId(): ?int
    {
        return $this->urbanRenewalId;
    }

    public function getCompanyId(): ?int
    {
        return $this->companyId;
    }

    public function getPropertyOwnerId(): ?int
    {
        return $this->propertyOwnerId;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getUrbanRenewalName(): ?string
    {
        return $this->urbanRenewalName;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    // === Setters ===

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setUsername(string $username): self
    {
        $username = trim($username);
        if (empty($username)) {
            throw new \InvalidArgumentException('使用者名稱不可為空');
        }
        $this->username = $username;
        return $this;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email ? trim($email) : null;
        return $this;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function setRole(string $role): self
    {
        if (!in_array($role, self::VALID_ROLES)) {
            throw new \InvalidArgumentException('無效的使用者角色');
        }
        $this->role = $role;
        return $this;
    }

    public function setUrbanRenewalId(?int $id): self
    {
        $this->urbanRenewalId = $id;
        return $this;
    }

    public function setCompanyId(?int $id): self
    {
        $this->companyId = $id;
        return $this;
    }

    public function setPropertyOwnerId(?int $id): self
    {
        $this->propertyOwnerId = $id;
        return $this;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function setIsCompanyManager(bool $isCompanyManager): self
    {
        $this->isCompanyManager = $isCompanyManager;
        return $this;
    }

    public function setDisplayName(?string $name): self
    {
        $this->displayName = $name ? trim($name) : null;
        return $this;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone ? trim($phone) : null;
        return $this;
    }

    public function setUrbanRenewalName(?string $name): self
    {
        $this->urbanRenewalName = $name;
        return $this;
    }

    public function setCompanyName(?string $name): self
    {
        $this->companyName = $name;
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

    public function setLastLoginAt(?\DateTime $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;
        return $this;
    }

    /**
     * 轉換為陣列（不含密碼）
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'urban_renewal_id' => $this->urbanRenewalId,
            'urban_renewal_name' => $this->urbanRenewalName,
            'company_id' => $this->companyId,
            'company_name' => $this->companyName,
            'property_owner_id' => $this->propertyOwnerId,
            'is_active' => $this->isActive,
            'is_company_manager' => $this->isCompanyManager,
            'display_name' => $this->displayName,
            'phone' => $this->phone,
            'is_admin' => $this->isAdmin(),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'last_login_at' => $this->lastLoginAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 從陣列建立實體
     */
    public static function fromArray(array $data): self
    {
        $entity = new self($data['username']);
        
        if (isset($data['id'])) $entity->setId((int)$data['id']);
        
        $entity->setEmail($data['email'] ?? null);
        if (isset($data['role'])) $entity->setRole($data['role']);
        if (isset($data['urban_renewal_id'])) $entity->setUrbanRenewalId((int)$data['urban_renewal_id']);
        if (isset($data['company_id'])) $entity->setCompanyId((int)$data['company_id']);
        if (isset($data['property_owner_id'])) $entity->setPropertyOwnerId((int)$data['property_owner_id']);
        if (isset($data['is_active'])) $entity->setIsActive((bool)$data['is_active']);
        if (isset($data['is_company_manager'])) $entity->setIsCompanyManager((bool)$data['is_company_manager']);
        
        $entity->setDisplayName($data['display_name'] ?? null);
        $entity->setPhone($data['phone'] ?? null);
        
        if (isset($data['urban_renewal_name'])) $entity->setUrbanRenewalName($data['urban_renewal_name']);
        if (isset($data['company_name'])) $entity->setCompanyName($data['company_name']);
        
        if (isset($data['created_at'])) $entity->setCreatedAt(new \DateTime($data['created_at']));
        if (isset($data['updated_at'])) $entity->setUpdatedAt(new \DateTime($data['updated_at']));
        if (isset($data['last_login_at'])) $entity->setLastLoginAt(new \DateTime($data['last_login_at']));
        
        return $entity;
    }
}
