<?php

namespace App\Traits;

trait HasRbacPermissions
{
    /**
     * 檢查使用者是否可以存取特定資源
     *
     * @param int|null $resourceUrbanRenewalId 資源所屬的 urban_renewal_id
     * @param int|null $userUrbanRenewalId 使用者被指派的 urban_renewal_id
     * @param string $userRole 使用者角色
     * @return bool
     */
    protected function checkResourceScope(
        ?int $resourceUrbanRenewalId,
        ?int $userUrbanRenewalId,
        string $userRole
    ): bool {
        // 管理員可以存取所有資源
        if ($userRole === 'admin') {
            return true;
        }

        // 非管理員使用者只能存取其指派的 urban_renewal_id 資源
        if ($userUrbanRenewalId === null) {
            return false;
        }

        return $resourceUrbanRenewalId === $userUrbanRenewalId;
    }

    /**
     * 檢查使用者角色是否擁有特定權限
     *
     * @param string $userRole 使用者角色
     * @param string $requiredPermission 需要的權限
     * @return bool
     */
    protected function checkRolePermission(string $userRole, string $requiredPermission): bool
    {
        // 權限矩陣
        $permissions = [
            'admin' => [
                'urban_renewal_manage',
                'meeting_manage',
                'voting_manage',
                'property_owner_manage',
                'system_admin',
                'report_view',
                'document_manage',
                'user_manage',
            ],
            'chairman' => [
                'meeting_manage',
                'voting_manage',
                'report_view',
                'document_manage',
            ],
            'member' => [
                'voting_manage',
                'report_view',
                'document_manage',
            ],
            'observer' => [
                'report_view',
                'document_manage',
            ],
        ];

        if (!isset($permissions[$userRole])) {
            return false;
        }

        return in_array($requiredPermission, $permissions[$userRole]);
    }

    /**
     * 從資源取得 urban_renewal_id
     * 此方法應在使用此 trait 的控制器中覆寫
     *
     * @param mixed $resource 資源物件或 ID
     * @param string $resourceType 資源類型（meeting、voting 等）
     * @return int|null
     */
    protected function getResourceUrbanRenewalId($resource, string $resourceType): ?int
    {
        // 預設實作 - 控制器應覆寫此方法
        if (is_array($resource) && isset($resource['urban_renewal_id'])) {
            return $resource['urban_renewal_id'];
        }

        if (is_object($resource) && isset($resource->urban_renewal_id)) {
            return $resource->urban_renewal_id;
        }

        return null;
    }

    /**
     * 檢查並強制執行資源存取權限
     *
     * @param mixed $resource 資源
     * @param string $resourceType 資源類型
     * @return bool
     * @throws \CodeIgniter\Exceptions\PageNotFoundException
     */
    protected function enforceResourceAccess($resource, string $resourceType): bool
    {
        $user = $this->request->user ?? null;

        if (!$user) {
            return $this->failUnauthorized('未認證');
        }

        $resourceUrbanRenewalId = $this->getResourceUrbanRenewalId($resource, $resourceType);
        $userUrbanRenewalId = $user['urban_renewal_id'] ?? null;
        $userRole = $user['role'] ?? 'observer';

        if (!$this->checkResourceScope($resourceUrbanRenewalId, $userUrbanRenewalId, $userRole)) {
            return $this->failForbidden('無權訪問此資源');
        }

        return true;
    }

    /**
     * 檢查並強制執行操作權限
     *
     * @param string $requiredPermission 需要的權限
     * @return bool
     */
    protected function enforcePermission(string $requiredPermission): bool
    {
        $user = $this->request->user ?? null;

        if (!$user) {
            return $this->failUnauthorized('未認證');
        }

        $userRole = $user['role'] ?? 'observer';

        if (!$this->checkRolePermission($userRole, $requiredPermission)) {
            return $this->failForbidden('您沒有執行此操作的權限');
        }

        return true;
    }
}
