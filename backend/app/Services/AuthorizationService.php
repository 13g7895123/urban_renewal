<?php

namespace App\Services;

use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;

/**
 * 授權服務
 * 
 * 統一處理權限檢查邏輯，消除 Controller 中的重複程式碼
 */
class AuthorizationService
{
    private $urbanRenewalModel;

    public function __construct()
    {
        $this->urbanRenewalModel = model('UrbanRenewalModel');
    }

    /**
     * 檢查用戶是否為系統管理員
     */
    public function isAdmin(?array $user): bool
    {
        return $user && isset($user['role']) && $user['role'] === 'admin';
    }

    /**
     * 檢查用戶是否為企業管理者
     */
    public function isCompanyManager(?array $user): bool
    {
        if (!$user) {
            return false;
        }

        return isset($user['is_company_manager']) && 
               ($user['is_company_manager'] === 1 || 
                $user['is_company_manager'] === '1' || 
                $user['is_company_manager'] === true);
    }

    /**
     * 取得用戶的企業 ID
     */
    public function getUserCompanyId(?array $user): ?int
    {
        if (!$user) {
            return null;
        }

        // 直接使用 company_id
        if (!empty($user['company_id'])) {
            return (int)$user['company_id'];
        }

        return null;
    }

    /**
     * 檢查用戶是否可以存取指定更新會
     */
    public function canAccessUrbanRenewal(?array $user, int $urbanRenewalId): bool
    {
        if (!$user) {
            return false;
        }

        // 管理員可以存取所有資源
        if ($this->isAdmin($user)) {
            return true;
        }

        // 必須是企業管理者
        if (!$this->isCompanyManager($user)) {
            return false;
        }

        // 取得用戶的企業 ID
        $userCompanyId = $this->getUserCompanyId($user);
        if (!$userCompanyId) {
            return false;
        }

        // 檢查目標更新會是否屬於用戶的企業
        $targetRenewal = $this->urbanRenewalModel->find($urbanRenewalId);
        if (!$targetRenewal || empty($targetRenewal['company_id'])) {
            return false;
        }

        return (int)$targetRenewal['company_id'] === $userCompanyId;
    }

    /**
     * 取得用戶可存取的更新會 ID 列表
     * 
     * @return int[]|null null 表示可存取所有（管理員）
     */
    public function getAccessibleRenewalIds(?array $user): ?array
    {
        if (!$user) {
            return [];
        }

        // 管理員可以存取所有
        if ($this->isAdmin($user)) {
            return null;
        }

        // 取得企業 ID
        $companyId = $this->getUserCompanyId($user);
        if (!$companyId) {
            return [];
        }

        // 查詢該企業下的所有更新會
        $renewals = $this->urbanRenewalModel
            ->where('company_id', $companyId)
            ->findAll();

        return array_column($renewals, 'id');
    }

    /**
     * 斷言用戶已認證
     * 
     * @throws UnauthorizedException
     */
    public function assertAuthenticated(?array $user): void
    {
        if (!$user) {
            throw new UnauthorizedException('未授權：無法識別用戶身份');
        }
    }

    /**
     * 斷言用戶可以存取指定更新會
     * 
     * @throws UnauthorizedException
     * @throws ForbiddenException
     */
    public function assertCanAccessUrbanRenewal(?array $user, int $urbanRenewalId): void
    {
        $this->assertAuthenticated($user);

        if (!$this->canAccessUrbanRenewal($user, $urbanRenewalId)) {
            // 區分權限不足的原因
            if (!$this->isCompanyManager($user)) {
                throw new ForbiddenException('權限不足：您沒有管理此資料的權限');
            }

            $companyId = $this->getUserCompanyId($user);
            if (!$companyId) {
                throw new ForbiddenException('權限不足：您的帳號未關聯任何企業');
            }

            throw new ForbiddenException('權限不足：您無權存取其他企業的資料');
        }
    }

    /**
     * 斷言用戶是企業管理者
     * 
     * @throws UnauthorizedException
     * @throws ForbiddenException
     */
    public function assertIsCompanyManager(?array $user): void
    {
        $this->assertAuthenticated($user);

        if (!$this->isAdmin($user) && !$this->isCompanyManager($user)) {
            throw new ForbiddenException('權限不足：您沒有企業管理權限');
        }
    }

    /**
     * 斷言用戶是系統管理員
     * 
     * @throws UnauthorizedException
     * @throws ForbiddenException
     */
    public function assertIsAdmin(?array $user): void
    {
        $this->assertAuthenticated($user);

        if (!$this->isAdmin($user)) {
            throw new ForbiddenException('權限不足：此操作需要系統管理員權限');
        }
    }

    /**
     * 取得用戶有權存取的更新會過濾條件
     * 
     * 用於查詢時自動加入權限過濾
     * 
     * @return array ['company_id' => int] 或 [] (管理員無限制)
     */
    public function getRenewalFilterForUser(?array $user): array
    {
        if ($this->isAdmin($user)) {
            return [];
        }

        $companyId = $this->getUserCompanyId($user);
        if ($companyId) {
            return ['company_id' => $companyId];
        }

        // 無權限時返回不可能匹配的條件
        return ['company_id' => -1];
    }
}
