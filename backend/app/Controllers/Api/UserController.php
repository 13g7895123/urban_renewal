<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class UserController extends ResourceController
{
    protected $modelName = 'App\Models\UserModel';
    protected $format = 'json';

    public function __construct()
    {
        $this->loadHelpers();
    }

    private function loadHelpers()
    {
        helper(['auth', 'response']);
    }

    /**
     * 取得使用者列表
     */
    public function index()
    {
        try {
            // 驗證用戶權限（只有管理員和主席可以查看使用者列表）
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;
            $filters = [
                'role' => $this->request->getGet('role'),
                'urban_renewal_id' => $this->request->getGet('urban_renewal_id'),
                'is_active' => $this->request->getGet('is_active'),
                'search' => $this->request->getGet('search')
            ];

            // 如果是主席，只能查看自己更新會的使用者
            if ($user['role'] === 'chairman') {
                $filters['urban_renewal_id'] = $user['urban_renewal_id'];
            }

            $users = $this->model->getUsers($page, $perPage, $filters);

            // 移除敏感資訊
            $users = array_map(function($userData) {
                unset($userData['password_hash'], $userData['password_reset_token']);
                return $userData;
            }, $users);

            return response_success('使用者列表', [
                'users' => $users,
                'pager' => $this->model->pager->getDetails()
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得使用者列表失敗: ' . $e->getMessage());
            return response_error('取得使用者列表失敗', 500);
        }
    }

    /**
     * 取得使用者詳情
     */
    public function show($id = null)
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('使用者ID為必填', 400);
            }

            $targetUser = $this->model->find($id);
            if (!$targetUser) {
                return response_error('找不到該使用者', 404);
            }

            // 檢查權限（只能查看自己或管理員/主席可以查看同更新會的使用者）
            if ($user['id'] !== (int)$id) {
                if ($user['role'] === 'member' || $user['role'] === 'observer') {
                    return $this->failForbidden('無權限查看此使用者資料');
                }
                if ($user['role'] === 'chairman' && $user['urban_renewal_id'] !== $targetUser['urban_renewal_id']) {
                    return $this->failForbidden('無權限查看此使用者資料');
                }
            }

            // 移除敏感資訊
            unset($targetUser['password_hash'], $targetUser['password_reset_token']);

            // 取得權限資訊（如果有的話）
            $targetUser = $this->model->getUserWithPermissions($id);
            unset($targetUser['password_hash'], $targetUser['password_reset_token']);

            return response_success('使用者詳情', $targetUser);

        } catch (\Exception $e) {
            log_message('error', '取得使用者詳情失敗: ' . $e->getMessage());
            return response_error('取得使用者詳情失敗', 500);
        }
    }

    /**
     * 建立使用者
     */
    public function create()
    {
        try {
            // 驗證用戶權限（只有管理員和主席可以建立使用者）
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $data = $this->request->getJSON(true);

            // 驗證必填欄位
            $validation = \Config\Services::validation();
            $validation->setRules([
                'username' => 'required|max_length[100]|is_unique[users.username]',
                'email' => 'permit_empty|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[6]',
                'role' => 'required|in_list[admin,chairman,member,observer]',
                'full_name' => 'permit_empty|max_length[100]',
                'phone' => 'permit_empty|max_length[20]'
            ]);

            if (!$validation->run($data)) {
                return response_error('資料驗證失敗', 400, $validation->getErrors());
            }

            // 檢驗密碼強度
            $passwordValidation = $this->model->validatePasswordStrength($data['password']);
            if ($passwordValidation !== true) {
                return response_error('密碼強度不足', 400, ['password' => $passwordValidation]);
            }

            // 權限檢查：主席只能建立同更新會的使用者
            if ($user['role'] === 'chairman') {
                if (isset($data['urban_renewal_id']) && $data['urban_renewal_id'] !== $user['urban_renewal_id']) {
                    return $this->failForbidden('只能建立同更新會的使用者');
                }
                $data['urban_renewal_id'] = $user['urban_renewal_id'];

                // 主席不能建立管理員
                if ($data['role'] === 'admin') {
                    return $this->failForbidden('無權限建立管理員帳號');
                }
            }

            // 設定預設值
            $data['is_active'] = $data['is_active'] ?? true;
            $data['login_attempts'] = 0;

            $userId = $this->model->createUser($data);
            if (!$userId) {
                return response_error('建立使用者失敗', 500, $this->model->errors());
            }

            $newUser = $this->model->find($userId);
            unset($newUser['password_hash'], $newUser['password_reset_token']);

            return response_success('使用者建立成功', $newUser, 201);

        } catch (\Exception $e) {
            log_message('error', '建立使用者失敗: ' . $e->getMessage());
            return response_error('建立使用者失敗', 500);
        }
    }

    /**
     * 更新使用者
     */
    public function update($id = null)
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('使用者ID為必填', 400);
            }

            $targetUser = $this->model->find($id);
            if (!$targetUser) {
                return response_error('找不到該使用者', 404);
            }

            // 檢查權限
            $canEdit = false;
            if ($user['id'] === (int)$id) {
                $canEdit = true; // 可以編輯自己的資料
            } elseif ($user['role'] === 'admin') {
                $canEdit = true; // 管理員可以編輯所有人
            } elseif ($user['role'] === 'chairman' && $user['urban_renewal_id'] === $targetUser['urban_renewal_id']) {
                $canEdit = true; // 主席可以編輯同更新會的使用者
            }

            if (!$canEdit) {
                return $this->failForbidden('無權限修改此使用者');
            }

            $data = $this->request->getJSON(true);

            // 一般使用者只能修改自己的基本資料
            if ($user['id'] === (int)$id && $user['role'] !== 'admin' && $user['role'] !== 'chairman') {
                $allowedFields = ['full_name', 'phone', 'password'];
                $data = array_intersect_key($data, array_flip($allowedFields));
            }

            // 主席不能修改管理員的角色
            if ($user['role'] === 'chairman' && $targetUser['role'] === 'admin') {
                unset($data['role'], $data['urban_renewal_id']);
            }

            // 檢驗密碼強度（如果有提供密碼）
            if (isset($data['password'])) {
                $passwordValidation = $this->model->validatePasswordStrength($data['password']);
                if ($passwordValidation !== true) {
                    return response_error('密碼強度不足', 400, ['password' => $passwordValidation]);
                }
            }

            // 檢驗使用者名稱唯一性
            if (isset($data['username']) && $this->model->usernameExists($data['username'], $id)) {
                return response_error('使用者名稱已存在', 400);
            }

            // 檢驗電子信箱唯一性
            if (isset($data['email']) && $data['email'] && $this->model->emailExists($data['email'], $id)) {
                return response_error('電子信箱已存在', 400);
            }

            $success = $this->model->updateUser($id, $data);
            if (!$success) {
                return response_error('更新使用者失敗', 500, $this->model->errors());
            }

            $updatedUser = $this->model->find($id);
            unset($updatedUser['password_hash'], $updatedUser['password_reset_token']);

            return response_success('使用者更新成功', $updatedUser);

        } catch (\Exception $e) {
            log_message('error', '更新使用者失敗: ' . $e->getMessage());
            return response_error('更新使用者失敗', 500);
        }
    }

    /**
     * 刪除使用者（軟刪除）
     */
    public function delete($id = null)
    {
        try {
            // 驗證用戶權限（只有管理員和主席可以刪除使用者）
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('使用者ID為必填', 400);
            }

            $targetUser = $this->model->find($id);
            if (!$targetUser) {
                return response_error('找不到該使用者', 404);
            }

            // 不能刪除自己
            if ($user['id'] === (int)$id) {
                return response_error('不能刪除自己的帳號', 400);
            }

            // 主席不能刪除管理員，也不能刪除其他更新會的使用者
            if ($user['role'] === 'chairman') {
                if ($targetUser['role'] === 'admin') {
                    return $this->failForbidden('無權限刪除管理員帳號');
                }
                if ($user['urban_renewal_id'] !== $targetUser['urban_renewal_id']) {
                    return $this->failForbidden('只能刪除同更新會的使用者');
                }
            }

            $success = $this->model->delete($id);
            if (!$success) {
                return response_error('刪除使用者失敗', 500);
            }

            return response_success('使用者刪除成功');

        } catch (\Exception $e) {
            log_message('error', '刪除使用者失敗: ' . $e->getMessage());
            return response_error('刪除使用者失敗', 500);
        }
    }

    /**
     * 啟用/停用使用者
     */
    public function toggleStatus($id = null)
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('使用者ID為必填', 400);
            }

            $targetUser = $this->model->find($id);
            if (!$targetUser) {
                return response_error('找不到該使用者', 404);
            }

            // 不能停用自己
            if ($user['id'] === (int)$id) {
                return response_error('不能停用自己的帳號', 400);
            }

            // 主席權限檢查
            if ($user['role'] === 'chairman') {
                if ($targetUser['role'] === 'admin') {
                    return $this->failForbidden('無權限操作管理員帳號');
                }
                if ($user['urban_renewal_id'] !== $targetUser['urban_renewal_id']) {
                    return $this->failForbidden('只能操作同更新會的使用者');
                }
            }

            $success = $this->model->toggleUserStatus($id);
            if (!$success) {
                return response_error('狀態切換失敗', 500);
            }

            $updatedUser = $this->model->find($id);
            unset($updatedUser['password_hash'], $updatedUser['password_reset_token']);

            return response_success('使用者狀態更新成功', $updatedUser);

        } catch (\Exception $e) {
            log_message('error', '切換使用者狀態失敗: ' . $e->getMessage());
            return response_error('切換使用者狀態失敗', 500);
        }
    }

    /**
     * 重設登入失敗次數
     */
    public function resetLoginAttempts($id = null)
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$id) {
                return response_error('使用者ID為必填', 400);
            }

            $targetUser = $this->model->find($id);
            if (!$targetUser) {
                return response_error('找不到該使用者', 404);
            }

            // 主席權限檢查
            if ($user['role'] === 'chairman') {
                if ($targetUser['role'] === 'admin') {
                    return $this->failForbidden('無權限操作管理員帳號');
                }
                if ($user['urban_renewal_id'] !== $targetUser['urban_renewal_id']) {
                    return $this->failForbidden('只能操作同更新會的使用者');
                }
            }

            $success = $this->model->resetLoginAttempts($id);
            if (!$success) {
                return response_error('重設失敗', 500);
            }

            return response_success('登入失敗次數已重設');

        } catch (\Exception $e) {
            log_message('error', '重設登入失敗次數失敗: ' . $e->getMessage());
            return response_error('重設登入失敗次數失敗', 500);
        }
    }

    /**
     * 搜尋使用者
     */
    public function search()
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $keyword = $this->request->getGet('keyword');
            if (!$keyword) {
                return response_error('搜尋關鍵字為必填', 400);
            }

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;

            $users = $this->model->searchUsers($keyword, $page, $perPage);

            // 如果是主席，過濾只顯示同更新會的使用者
            if ($user['role'] === 'chairman') {
                $users = array_filter($users, function($userData) use ($user) {
                    return $userData['urban_renewal_id'] === $user['urban_renewal_id'];
                });
            }

            // 移除敏感資訊
            $users = array_map(function($userData) {
                unset($userData['password_hash'], $userData['password_reset_token']);
                return $userData;
            }, $users);

            return response_success('搜尋結果', [
                'users' => array_values($users),
                'pager' => $this->model->pager->getDetails()
            ]);

        } catch (\Exception $e) {
            log_message('error', '搜尋使用者失敗: ' . $e->getMessage());
            return response_error('搜尋使用者失敗', 500);
        }
    }

    /**
     * 取得角色統計
     */
    public function roleStatistics()
    {
        try {
            // 驗證用戶權限
            $user = auth_validate_request(['admin', 'chairman']);
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $statistics = $this->model->getRoleStatistics();

            return response_success('角色統計', $statistics);

        } catch (\Exception $e) {
            log_message('error', '取得角色統計失敗: ' . $e->getMessage());
            return response_error('取得角色統計失敗', 500);
        }
    }

    /**
     * 取得當前使用者資訊
     */
    public function profile()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $userProfile = $this->model->getUserWithPermissions($user['id']);
            unset($userProfile['password_hash'], $userProfile['password_reset_token']);

            return response_success('使用者資料', $userProfile);

        } catch (\Exception $e) {
            log_message('error', '取得使用者資料失敗: ' . $e->getMessage());
            return response_error('取得使用者資料失敗', 500);
        }
    }

    /**
     * 更新使用者密碼
     */
    public function changePassword()
    {
        try {
            // 驗證用戶
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            $data = $this->request->getJSON(true);

            // 驗證必填欄位
            $validation = \Config\Services::validation();
            $validation->setRules([
                'current_password' => 'required',
                'new_password' => 'required|min_length[6]',
                'confirm_password' => 'required|matches[new_password]'
            ]);

            if (!$validation->run($data)) {
                return response_error('資料驗證失敗', 400, $validation->getErrors());
            }

            // 驗證當前密碼
            $currentUser = $this->model->find($user['id']);
            if (!password_verify($data['current_password'], $currentUser['password_hash'])) {
                return response_error('當前密碼錯誤', 400);
            }

            // 檢驗新密碼強度
            $passwordValidation = $this->model->validatePasswordStrength($data['new_password']);
            if ($passwordValidation !== true) {
                return response_error('新密碼強度不足', 400, ['new_password' => $passwordValidation]);
            }

            $success = $this->model->updateUser($user['id'], [
                'password' => $data['new_password']
            ]);

            if (!$success) {
                return response_error('密碼更新失敗', 500);
            }

            return response_success('密碼更新成功');

        } catch (\Exception $e) {
            log_message('error', '更新密碼失敗: ' . $e->getMessage());
            return response_error('更新密碼失敗', 500);
        }
    }
}