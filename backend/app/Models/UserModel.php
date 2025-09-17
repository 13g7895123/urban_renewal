<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;

    protected $allowedFields = [
        'username',
        'email',
        'password_hash',
        'role',
        'urban_renewal_id',
        'property_owner_id',
        'full_name',
        'phone',
        'is_active',
        'last_login_at',
        'login_attempts',
        'locked_until',
        'password_reset_token',
        'password_reset_expires'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'username' => 'required|max_length[100]|is_unique[users.username,id,{id}]',
        'email' => 'permit_empty|valid_email|is_unique[users.email,id,{id}]',
        'password_hash' => 'required|min_length[6]',
        'role' => 'required|in_list[admin,chairman,member,observer]',
        'full_name' => 'permit_empty|max_length[100]',
        'phone' => 'permit_empty|max_length[20]'
    ];

    protected $validationMessages = [
        'username' => [
            'required' => '使用者名稱為必填',
            'max_length' => '使用者名稱不可超過100字元',
            'is_unique' => '使用者名稱已存在'
        ],
        'email' => [
            'valid_email' => '請輸入有效的電子信箱',
            'is_unique' => '電子信箱已存在'
        ],
        'password_hash' => [
            'required' => '密碼為必填',
            'min_length' => '密碼至少需要6個字元'
        ],
        'role' => [
            'required' => '角色為必填',
            'in_list' => '角色必須為：admin, chairman, member, observer'
        ],
        'full_name' => [
            'max_length' => '姓名不可超過100字元'
        ],
        'phone' => [
            'max_length' => '電話號碼不可超過20字元'
        ]
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * 在插入或更新前加密密碼
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            unset($data['data']['password']);
        }
        return $data;
    }

    /**
     * 取得使用者列表 (分頁)
     */
    public function getUsers($page = 1, $perPage = 10, $filters = [])
    {
        $builder = $this->select('users.*, urban_renewals.name as urban_renewal_name')
                       ->join('urban_renewals', 'urban_renewals.id = users.urban_renewal_id', 'left');

        // 篩選條件
        if (!empty($filters['role'])) {
            $builder->where('users.role', $filters['role']);
        }

        if (!empty($filters['urban_renewal_id'])) {
            $builder->where('users.urban_renewal_id', $filters['urban_renewal_id']);
        }

        if (!empty($filters['is_active'])) {
            $builder->where('users.is_active', $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $builder->groupStart()
                   ->like('users.username', $filters['search'])
                   ->orLike('users.full_name', $filters['search'])
                   ->orLike('users.email', $filters['search'])
                   ->groupEnd();
        }

        return $builder->paginate($perPage, 'default', $page);
    }

    /**
     * 取得特定更新會的使用者
     */
    public function getUsersByUrbanRenewal($urbanRenewalId, $page = 1, $perPage = 10)
    {
        return $this->where('urban_renewal_id', $urbanRenewalId)
                   ->where('is_active', 1)
                   ->paginate($perPage, 'default', $page);
    }

    /**
     * 搜尋使用者
     */
    public function searchUsers($keyword, $page = 1, $perPage = 10)
    {
        return $this->select('users.*, urban_renewals.name as urban_renewal_name')
                   ->join('urban_renewals', 'urban_renewals.id = users.urban_renewal_id', 'left')
                   ->groupStart()
                   ->like('users.username', $keyword)
                   ->orLike('users.full_name', $keyword)
                   ->orLike('users.email', $keyword)
                   ->groupEnd()
                   ->paginate($perPage, 'default', $page);
    }

    /**
     * 檢查使用者名稱是否存在
     */
    public function usernameExists($username, $excludeId = null)
    {
        $builder = $this->where('username', $username);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * 檢查電子信箱是否存在
     */
    public function emailExists($email, $excludeId = null)
    {
        $builder = $this->where('email', $email);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * 啟用/停用使用者
     */
    public function toggleUserStatus($userId)
    {
        $user = $this->find($userId);
        if (!$user) {
            return false;
        }

        return $this->update($userId, [
            'is_active' => !$user['is_active']
        ]);
    }

    /**
     * 重設使用者登入失敗次數
     */
    public function resetLoginAttempts($userId)
    {
        return $this->update($userId, [
            'login_attempts' => 0,
            'locked_until' => null
        ]);
    }

    /**
     * 取得角色統計
     */
    public function getRoleStatistics()
    {
        return $this->select('role, COUNT(*) as count')
                   ->where('is_active', 1)
                   ->groupBy('role')
                   ->findAll();
    }

    /**
     * 建立新使用者
     */
    public function createUser($data)
    {
        // 驗證資料
        if (!$this->validate($data)) {
            return false;
        }

        // 加密密碼
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        return $this->insert($data);
    }

    /**
     * 更新使用者資料
     */
    public function updateUser($userId, $data)
    {
        // 移除不允許直接更新的欄位
        unset($data['password_hash'], $data['password_reset_token'], $data['login_attempts']);

        // 處理密碼更新
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        return $this->update($userId, $data);
    }

    /**
     * 取得使用者權限
     */
    public function getUserWithPermissions($userId)
    {
        $user = $this->find($userId);
        if (!$user) {
            return null;
        }

        // 取得使用者權限
        $permissionModel = model('UserPermissionModel');
        $permissions = $permissionModel->getUserPermissions($userId);

        $user['permissions'] = $permissions;
        return $user;
    }

    /**
     * 驗證密碼強度
     */
    public function validatePasswordStrength($password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = '密碼至少需要8個字元';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = '密碼需包含至少一個大寫字母';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = '密碼需包含至少一個小寫字母';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = '密碼需包含至少一個數字';
        }

        return empty($errors) ? true : $errors;
    }
}