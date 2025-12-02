<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Traits\HasRbacPermissions;

class UserController extends ResourceController
{
    use HasRbacPermissions;

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
            // 驗證用戶已登入
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            // 檢查權限：只有管理員、主席、或企業管理者可以查看使用者列表
            $isAdmin = $user['role'] === 'admin';
            $isChairman = $user['role'] === 'chairman';
            $isCompanyManager = isset($user['is_company_manager']) && ($user['is_company_manager'] == 1 || $user['is_company_manager'] === '1');

            if (!$isAdmin && !$isChairman && !$isCompanyManager) {
                return $this->failForbidden('您沒有權限查看使用者列表');
            }

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;
            $filters = [
                'role' => $this->request->getGet('role'),
                'user_type' => $this->request->getGet('user_type'),
                'company_id' => $this->request->getGet('company_id'),          // 新架構：使用 company_id
                'is_active' => $this->request->getGet('is_active'),
                'search' => $this->request->getGet('search')
            ];

            // 新架構：企業管理者查詢自己企業的使用者
            if ($isCompanyManager) {
                // 如果前端傳了 company_id，驗證是否為自己的企業
                if (!empty($filters['company_id'])) {
                    if ($filters['company_id'] != $user['company_id']) {
                        return $this->failForbidden('您只能查看自己企業的使用者');
                    }
                } else {
                    // 如果前端未傳，自動使用當前用戶的 company_id
                    $filters['company_id'] = $user['company_id'];
                }
            } elseif ($isChairman) {
                // 主席基於 urban_renewal_id 查詢（舊架構保留相容）
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
            // 驗證用戶已登入
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            // 檢查權限：只有管理員、主席、或企業管理者可以建立使用者
            $isAdmin = $user['role'] === 'admin';
            $isChairman = $user['role'] === 'chairman';
            $isCompanyManager = isset($user['is_company_manager']) && ($user['is_company_manager'] == 1 || $user['is_company_manager'] === '1');

            if (!$isAdmin && !$isChairman && !$isCompanyManager) {
                return $this->failForbidden('您沒有權限建立使用者');
            }

            $data = $this->request->getJSON(true);

            // DEBUG: 檢查收到的數據
            log_message('debug', '[UserController::create] Received data: ' . json_encode($data));

            // 提早設定 user_type 和 company_id（在驗證前）
            // 如果是企業管理者，自動設定企業系統參數
            if ($isCompanyManager && empty($data['user_type'])) {
                $data['user_type'] = 'enterprise';
            }
            if ($isCompanyManager && empty($data['company_id'])) {
                $data['company_id'] = $user['company_id'];
            }

            // 驗證必填欄位
            $validation = \Config\Services::validation();
            $validation->setRules([
                'username' => 'required|max_length[100]|is_unique[users.username]',
                'email' => 'permit_empty|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[6]',
                'role' => 'required|in_list[admin,chairman,member,observer]',
                'full_name' => 'permit_empty|max_length[100]',
                'phone' => 'permit_empty|max_length[20]',
                'nickname' => 'permit_empty|max_length[100]',
                'line_account' => 'permit_empty|max_length[100]',
                'position' => 'permit_empty|max_length[100]',
                'company_id' => 'permit_empty|integer',
                'user_type' => 'permit_empty|in_list[general,enterprise]',
                'is_company_manager' => 'permit_empty|in_list[0,1]'
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

            // 新架構邏輯：處理企業相關使用者建立
            // 如果是管理員以外的使用者（企業管理者或主席），需要帶入企業資料
            if (!$isAdmin) {
                // 企業管理者建立的使用者都屬於企業系統
                if ($isCompanyManager) {
                    // user_type 已在驗證前設定為 'enterprise'
                    // company_id 已在驗證前設定或驗證
                    
                    // 驗證前端傳入的 company_id 是否為自己的企業
                    if (isset($data['company_id']) && $data['company_id'] != $user['company_id']) {
                        return $this->failForbidden('只能建立同企業的使用者');
                    }
                    
                    // 如果標記為企業管理者，進行額外驗證
                    if (isset($data['is_company_manager']) && $data['is_company_manager'] == 1) {
                        // 企業管理者不能是管理員或主席
                        if ($data['role'] === 'admin' || $data['role'] === 'chairman') {
                            return $this->failForbidden('企業管理者的角色只能是 member 或 observer');
                        }
                    }
                }
                
                // 完全移除 urban_renewal_id（用不到）
                unset($data['urban_renewal_id']);
            }

            // 設定預設值
            $data['is_active'] = $data['is_active'] ?? true;
            $data['login_attempts'] = 0;

            // DEBUG: 檢查要寫入的數據
            log_message('debug', '[UserController::create] Data before insert: ' . json_encode($data));

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
            // 驗證用戶已登入
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            // 檢查權限：只有管理員、主席、或企業管理者可以刪除使用者
            $isAdmin = $user['role'] === 'admin';
            $isChairman = $user['role'] === 'chairman';
            $isCompanyManager = isset($user['is_company_manager']) && ($user['is_company_manager'] == 1 || $user['is_company_manager'] === '1');

            if (!$isAdmin && !$isChairman && !$isCompanyManager) {
                return $this->failForbidden('您沒有權限刪除使用者');
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

            // 企業管理者不能刪除管理員或主席，也不能刪除其他企業的使用者
            if ($isCompanyManager && !$isAdmin) {
                if ($targetUser['role'] === 'admin' || $targetUser['role'] === 'chairman') {
                    return $this->failForbidden('無權限刪除管理員或主席帳號');
                }
                // 使用 company_id 驗證
                helper('auth');
                $userCompanyId = auth_get_user_company_id($user);
                $targetCompanyId = auth_get_user_company_id($targetUser);
                if (!$userCompanyId || $userCompanyId !== $targetCompanyId) {
                    return $this->failForbidden('只能刪除同企業的使用者');
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

    /**
     * 取得企業管理者列表
     */
    public function getCompanyManagers($companyId = null)
    {
        try {
            // 驗證用戶已登入
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$companyId) {
                return response_error('企業ID為必填', 400);
            }

            // 權限驗證：系統管理員可以查看所有企業管理者
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';

            // 非管理員只能查看自己所屬企業的管理者
            if (!$isAdmin) {
                helper('auth');
                $userCompanyId = auth_get_user_company_id($user);

                if (!$userCompanyId) {
                    return $this->failForbidden('您的帳號未關聯任何企業');
                }

                if ((int)$userCompanyId !== (int)$companyId) {
                    return $this->failForbidden('無權限查看其他企業的管理者資料');
                }
            }

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;

            $managers = $this->model->getCompanyManagers($companyId, $page, $perPage);

            // 移除敏感資訊
            $managers = array_map(function($userData) {
                unset($userData['password_hash'], $userData['password_reset_token']);
                return $userData;
            }, $managers);

            return response_success('企業管理者列表', [
                'managers' => $managers,
                'pager' => $this->model->pager->getDetails()
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得企業管理者列表失敗: ' . $e->getMessage());
            return response_error('取得企業管理者列表失敗', 500);
        }
    }

    /**
     * 取得企業使用者列表
     */
    public function getCompanyUsers($companyId = null)
    {
        try {
            // 驗證用戶已登入
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$companyId) {
                return response_error('企業ID為必填', 400);
            }

            // 權限驗證：系統管理員可以查看所有企業使用者
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';

            // 非管理員只能查看自己所屬企業的使用者
            if (!$isAdmin) {
                helper('auth');
                $userCompanyId = auth_get_user_company_id($user);

                if (!$userCompanyId) {
                    return $this->failForbidden('您的帳號未關聯任何企業');
                }

                if ((int)$userCompanyId !== (int)$companyId) {
                    return $this->failForbidden('無權限查看其他企業的使用者資料');
                }
            }

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;

            $users = $this->model->getCompanyUsers($companyId, $page, $perPage);

            // 移除敏感資訊
            $users = array_map(function($userData) {
                unset($userData['password_hash'], $userData['password_reset_token']);
                return $userData;
            }, $users);

            return response_success('企業使用者列表', [
                'users' => $users,
                'pager' => $this->model->pager->getDetails()
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得企業使用者列表失敗: ' . $e->getMessage());
            return response_error('取得企業使用者列表失敗', 500);
        }
    }

    /**
     * 取得所有企業成員（管理者 + 使用者）
     */
    public function getAllCompanyMembers($companyId = null)
    {
        try {
            // 驗證用戶已登入
            $user = auth_validate_request();
            if (!$user) {
                return $this->failUnauthorized('請重新登入');
            }

            if (!$companyId) {
                return response_error('企業ID為必填', 400);
            }

            // 權限驗證：系統管理員可以查看所有企業成員
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';

            // 非管理員只能查看自己所屬企業的成員
            if (!$isAdmin) {
                helper('auth');
                $userCompanyId = auth_get_user_company_id($user);

                if (!$userCompanyId) {
                    return $this->failForbidden('您的帳號未關聯任何企業');
                }

                if ((int)$userCompanyId !== (int)$companyId) {
                    return $this->failForbidden('無權限查看其他企業的成員資料');
                }
            }

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 100;

            $members = $this->model->getAllCompanyMembers($companyId, $page, $perPage);

            // 移除敏感資訊
            $members = array_map(function($userData) {
                unset($userData['password_hash'], $userData['password_reset_token']);
                return $userData;
            }, $members);

            return response_success('企業成員列表', [
                'members' => $members,
                'pager' => $this->model->pager->getDetails()
            ]);

        } catch (\Exception $e) {
            log_message('error', '取得企業成員列表失敗: ' . $e->getMessage());
            return response_error('取得企業成員列表失敗', 500);
        }
    }

    /**
     * 設定為企業使用者（移除管理者權限）
     */
    public function setAsCompanyUser($id = null)
    {
        try {
            // 驗證用戶已登入
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

            // 不能操作自己
            if ($user['id'] === (int)$id) {
                return response_error('不能移除自己的管理者權限', 400);
            }

            // 檢查權限
            $isAdmin = $user['role'] === 'admin';
            $isCompanyManager = isset($user['is_company_manager']) && ($user['is_company_manager'] == 1 || $user['is_company_manager'] === '1');

            if (!$isAdmin && !$isCompanyManager) {
                return $this->failForbidden('您沒有權限設定企業使用者');
            }

            // 企業管理者只能管理同企業的使用者
            if ($isCompanyManager && !$isAdmin) {
                if ($targetUser['role'] === 'admin' || $targetUser['role'] === 'chairman') {
                    return $this->failForbidden('無權限操作管理員或主席帳號');
                }
                // 使用 company_id 驗證
                helper('auth');
                $userCompanyId = auth_get_user_company_id($user);
                $targetCompanyId = auth_get_user_company_id($targetUser);
                if (!$userCompanyId || $userCompanyId !== $targetCompanyId) {
                    return $this->failForbidden('只能管理同企業的使用者');
                }
                if (($targetUser['user_type'] ?? '') !== 'enterprise') {
                    return $this->failForbidden('只能管理企業類型的使用者');
                }
            }

            $success = $this->model->setAsCompanyUser($id);
            if (!$success) {
                return response_error('設定失敗', 500);
            }

            $updatedUser = $this->model->find($id);
            unset($updatedUser['password_hash'], $updatedUser['password_reset_token']);

            return response_success('已設定為企業使用者', $updatedUser);

        } catch (\Exception $e) {
            log_message('error', '設定企業使用者失敗: ' . $e->getMessage());
            return response_error('設定企業使用者失敗', 500);
        }
    }

    /**
     * 設定為企業管理者
     */
    public function setAsCompanyManager($id = null)
    {
        try {
            // 驗證用戶已登入
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
            $isAdmin = $user['role'] === 'admin';
            $isCompanyManager = isset($user['is_company_manager']) && ($user['is_company_manager'] == 1 || $user['is_company_manager'] === '1');

            if (!$isAdmin && !$isCompanyManager) {
                return $this->failForbidden('您沒有權限設定企業管理者');
            }

            // 企業管理者只能管理同企業的使用者
            if ($isCompanyManager && !$isAdmin) {
                if ($targetUser['role'] === 'admin' || $targetUser['role'] === 'chairman') {
                    return $this->failForbidden('無權限操作管理員或主席帳號');
                }
                // 使用 company_id 驗證
                helper('auth');
                $userCompanyId = auth_get_user_company_id($user);
                $targetCompanyId = auth_get_user_company_id($targetUser);
                if (!$userCompanyId || $userCompanyId !== $targetCompanyId) {
                    return $this->failForbidden('只能管理同企業的使用者');
                }
                if (($targetUser['user_type'] ?? '') !== 'enterprise') {
                    return $this->failForbidden('只能管理企業類型的使用者');
                }
            }

            $success = $this->model->setAsCompanyManager($id);
            if (!$success) {
                return response_error('設定失敗', 500);
            }

            $updatedUser = $this->model->find($id);
            unset($updatedUser['password_hash'], $updatedUser['password_reset_token']);

            return response_success('已設定為企業管理者', $updatedUser);

        } catch (\Exception $e) {
            log_message('error', '設定企業管理者失敗: ' . $e->getMessage());
            return response_error('設定企業管理者失敗', 500);
        }
    }
}