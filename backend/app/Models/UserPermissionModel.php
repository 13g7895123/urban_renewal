<?php

namespace App\Models;

use CodeIgniter\Model;

class UserPermissionModel extends Model
{
    protected $table = 'user_permissions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id',
        'permission_type',
        'resource_id',
        'granted_by',
        'granted_at',
        'expires_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'user_id' => 'required|integer',
        'permission_type' => 'required|in_list[urban_renewal_manage,meeting_manage,voting_manage,property_owner_manage,system_admin,report_view,document_manage]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => '使用者ID為必填項目',
            'integer' => '使用者ID必須為整數'
        ],
        'permission_type' => [
            'required' => '權限類型為必填項目',
            'in_list' => '無效的權限類型'
        ]
    ];

    /**
     * 取得使用者的所有權限
     *
     * @param int $userId 使用者ID
     * @return array 權限列表
     */
    public function getUserPermissions($userId)
    {
        $permissions = $this->where('user_id', $userId)
            ->where(function($builder) {
                $builder->where('expires_at IS NULL')
                        ->orWhere('expires_at >', date('Y-m-d H:i:s'));
            })
            ->findAll();

        // 返回權限類型陣列
        return array_column($permissions, 'permission_type');
    }

    /**
     * 檢查使用者是否有特定權限
     *
     * @param int $userId 使用者ID
     * @param string $permissionType 權限類型
     * @param int|null $resourceId 資源ID（可選）
     * @return bool
     */
    public function hasPermission($userId, $permissionType, $resourceId = null)
    {
        $builder = $this->where('user_id', $userId)
            ->where('permission_type', $permissionType)
            ->where(function($builder) {
                $builder->where('expires_at IS NULL')
                        ->orWhere('expires_at >', date('Y-m-d H:i:s'));
            });

        if ($resourceId !== null) {
            $builder->where('resource_id', $resourceId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * 授予使用者權限
     *
     * @param int $userId 使用者ID
     * @param string $permissionType 權限類型
     * @param int|null $resourceId 資源ID
     * @param int|null $grantedBy 授予者ID
     * @param string|null $expiresAt 過期時間
     * @return int|bool 插入的ID或false
     */
    public function grantPermission($userId, $permissionType, $resourceId = null, $grantedBy = null, $expiresAt = null)
    {
        // 檢查是否已存在相同權限
        $existing = $this->where('user_id', $userId)
            ->where('permission_type', $permissionType)
            ->where('resource_id', $resourceId)
            ->first();

        if ($existing) {
            // 更新過期時間
            return $this->update($existing['id'], [
                'expires_at' => $expiresAt,
                'granted_by' => $grantedBy,
                'granted_at' => date('Y-m-d H:i:s')
            ]);
        }

        // 新增權限
        return $this->insert([
            'user_id' => $userId,
            'permission_type' => $permissionType,
            'resource_id' => $resourceId,
            'granted_by' => $grantedBy,
            'granted_at' => date('Y-m-d H:i:s'),
            'expires_at' => $expiresAt
        ]);
    }

    /**
     * 撤銷使用者權限
     *
     * @param int $userId 使用者ID
     * @param string $permissionType 權限類型
     * @param int|null $resourceId 資源ID
     * @return bool
     */
    public function revokePermission($userId, $permissionType, $resourceId = null)
    {
        $builder = $this->where('user_id', $userId)
            ->where('permission_type', $permissionType);

        if ($resourceId !== null) {
            $builder->where('resource_id', $resourceId);
        }

        return $builder->delete();
    }

    /**
     * 撤銷使用者的所有權限
     *
     * @param int $userId 使用者ID
     * @return bool
     */
    public function revokeAllPermissions($userId)
    {
        return $this->where('user_id', $userId)->delete();
    }

    /**
     * 取得擁有特定權限的使用者列表
     *
     * @param string $permissionType 權限類型
     * @param int|null $resourceId 資源ID
     * @return array
     */
    public function getUsersByPermission($permissionType, $resourceId = null)
    {
        $builder = $this->select('user_id')
            ->where('permission_type', $permissionType)
            ->where(function($builder) {
                $builder->where('expires_at IS NULL')
                        ->orWhere('expires_at >', date('Y-m-d H:i:s'));
            });

        if ($resourceId !== null) {
            $builder->where('resource_id', $resourceId);
        }

        $results = $builder->findAll();
        return array_column($results, 'user_id');
    }

    /**
     * 清理過期的權限
     *
     * @return int 刪除的記錄數
     */
    public function cleanupExpiredPermissions()
    {
        return $this->where('expires_at <', date('Y-m-d H:i:s'))
            ->delete();
    }
}
