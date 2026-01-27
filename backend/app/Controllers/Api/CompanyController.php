<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\UrbanRenewalModel;
use App\Models\UserModel;
use App\Models\UserRenewalAssignmentModel;
use CodeIgniter\HTTP\ResponseInterface;

class CompanyController extends BaseController
{
    protected $companyModel;
    protected $urbanRenewalModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->urbanRenewalModel = new UrbanRenewalModel();
    }

    /**
     * Get current user's company profile
     * GET /api/companies/me
     */
    public function me()
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            $isCompanyManager = isset($user['is_company_manager']) &&
                ($user['is_company_manager'] === 1 ||
                    $user['is_company_manager'] === '1' ||
                    $user['is_company_manager'] === true);

            if (!$isCompanyManager) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => '權限不足：您沒有管理企業的權限'
                ]);
            }

            // 新架構：取得用戶的 company_id (過渡期也支持 urban_renewal_id)
            $companyId = $user['company_id'] ?? null;
            if (!$companyId && isset($user['urban_renewal_id'])) {
                // 過渡期兼容：從 urban_renewal_id 推導 company_id
                $urbanRenewal = $this->urbanRenewalModel->find($user['urban_renewal_id']);
                if ($urbanRenewal && $urbanRenewal['company_id']) {
                    $companyId = $urbanRenewal['company_id'];
                }
            }

            if (!$companyId) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => '權限不足：您的帳號未關聯任何企業'
                ]);
            }

            $company = $this->companyModel->find($companyId);

            if (!$company) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '查無企業資料'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '查詢成功',
                'data' => $company
            ]);
        } catch (\Exception $e) {
            log_message('error', '[CompanyController::me] Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '伺服器錯誤：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update current user's company profile
     * PUT /api/companies/me
     */
    public function update()
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            $isCompanyManager = isset($user['is_company_manager']) &&
                ($user['is_company_manager'] === 1 ||
                    $user['is_company_manager'] === '1' ||
                    $user['is_company_manager'] === true);

            if (!$isCompanyManager) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => '權限不足：您沒有管理企業的權限'
                ]);
            }

            // 新架構：取得用戶的 company_id (過渡期也支持 urban_renewal_id)
            $companyId = $user['company_id'] ?? null;
            if (!$companyId && isset($user['urban_renewal_id'])) {
                // 過渡期兼容：從 urban_renewal_id 推導 company_id
                $urbanRenewal = $this->urbanRenewalModel->find($user['urban_renewal_id']);
                if ($urbanRenewal && $urbanRenewal['company_id']) {
                    $companyId = $urbanRenewal['company_id'];
                }
            }

            if (!$companyId) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => '權限不足：您的帳號未關聯任何企業'
                ]);
            }

            $company = $this->companyModel->find($companyId);

            if (!$company) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '查無企業資料'
                ]);
            }

            $data = $this->request->getJSON(true);

            if (empty($data)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '無效的請求資料'
                ]);
            }

            $result = $this->companyModel->updateCompany($company['id'], $data);

            if (!$result) {
                $errors = $this->companyModel->errors();
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '更新失敗',
                    'errors' => $errors
                ]);
            }

            $updatedCompany = $this->companyModel->find($company['id']);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '更新成功',
                'data' => $updatedCompany
            ]);
        } catch (\Exception $e) {
            log_message('error', '[CompanyController::update] Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '伺服器錯誤：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get all urban renewals managed by current company
     * GET /api/companies/me/renewals
     */
    public function getRenewals()
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            $isCompanyManager = isset($user['is_company_manager']) &&
                ($user['is_company_manager'] === 1 ||
                    $user['is_company_manager'] === '1' ||
                    $user['is_company_manager'] === true);

            if (!$isCompanyManager) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => '權限不足：您沒有管理企業的權限'
                ]);
            }

            // 新架構：取得用戶的 company_id
            $companyId = $user['company_id'] ?? null;
            if (!$companyId && isset($user['urban_renewal_id'])) {
                // 過渡期兼容：從 urban_renewal_id 推導 company_id
                $urbanRenewal = $this->urbanRenewalModel->find($user['urban_renewal_id']);
                if ($urbanRenewal && $urbanRenewal['company_id']) {
                    $companyId = $urbanRenewal['company_id'];
                }
            }

            if (!$companyId) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => '權限不足：您的帳號未關聯任何企業'
                ]);
            }

            $company = $this->companyModel->find($companyId);
            if (!$company) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '查無企業資料'
                ]);
            }

            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;

            // Use urbanRenewalModel directly to maintain pager reference
            $renewals = $this->urbanRenewalModel
                ->where('company_id', $companyId)
                ->orderBy('created_at', 'DESC')
                ->paginate($perPage, 'default', $page);

            // Add calculated member count and area to each renewal
            foreach ($renewals as &$renewal) {
                $renewal['member_count'] = $this->urbanRenewalModel->calculateMemberCount($renewal['id']);
                $renewal['area'] = $this->urbanRenewalModel->calculateTotalLandArea($renewal['id']);
            }
            unset($renewal);

            $pager = $this->urbanRenewalModel->pager;

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '查詢成功',
                'data' => $renewals,
                'pager' => $pager ? $pager->getDetails() : null
            ]);
        } catch (\Exception $e) {
            log_message('error', '[CompanyController::getRenewals] Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '伺服器錯誤：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get company managers list
     * GET /api/companies/{id}/managers
     */
    public function getManagers($companyId = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            if (!$companyId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '請提供企業 ID'
                ]);
            }

            // Check access permission
            if ($user['role'] !== 'admin') {
                if (empty($user['company_id']) || $user['company_id'] != $companyId) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '權限不足：無法查看其他企業的管理者'
                    ]);
                }
            }

            $userModel = new \App\Models\UserModel();
            $managers = $userModel
                ->where('company_id', $companyId)
                ->where('is_company_manager', 1)
                ->findAll();

            // Remove sensitive fields
            foreach ($managers as &$manager) {
                unset($manager['password_hash'], $manager['password_reset_token']);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $managers,
                'message' => '企業管理者列表'
            ]);
        } catch (\Exception $e) {
            log_message('error', '[CompanyController::getManagers] Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '伺服器錯誤：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get company users list
     * GET /api/companies/{id}/users
     */
    public function getUsers($companyId = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            if (!$companyId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '請提供企業 ID'
                ]);
            }

            // Check access permission
            if ($user['role'] !== 'admin') {
                if (empty($user['company_id']) || $user['company_id'] != $companyId) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '權限不足：無法查看其他企業的使用者'
                    ]);
                }
            }

            $userModel = new \App\Models\UserModel();
            $users = $userModel
                ->where('company_id', $companyId)
                ->findAll();

            // Remove sensitive fields
            foreach ($users as &$u) {
                unset($u['password_hash'], $u['password_reset_token']);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $users,
                'message' => '企業使用者列表'
            ]);
        } catch (\Exception $e) {
            log_message('error', '[CompanyController::getUsers] Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '伺服器錯誤：' . $e->getMessage()
            ]);
        }
    }

    /**
     * 取得待審核的使用者列表
     * GET /api/companies/me/pending-users
     */
    public function getPendingUsers()
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user || !($user['is_company_manager'] ?? false)) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '權限不足']);
            }

            $userModel = new \App\Models\UserModel();
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;

            $pendingUsers = $userModel->getPendingApprovalUsers($user['company_id'], $page, $perPage);

            foreach ($pendingUsers as &$u) {
                unset($u['password_hash'], $u['password_reset_token']);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $pendingUsers,
                'pager' => $userModel->pager->getDetails()
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * 審核使用者
     * POST /api/companies/me/approve-user/{userId}
     */
    public function approveUser($userId = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user || !($user['is_company_manager'] ?? false)) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '權限不足']);
            }

            $data = $this->request->getJSON(true);
            $action = $data['action'] ?? 'approve'; // 'approve' or 'reject'

            $userModel = new \App\Models\UserModel();
            $targetUser = $userModel->find($userId);

            if (!$targetUser || $targetUser['company_id'] != $user['company_id']) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => '找不到該使用者或無權限']);
            }

            if ($action === 'approve') {
                $userModel->approveUser($userId, $user['id']);
                $message = '已核准使用者申請';
            } else {
                $userModel->rejectUser($userId, $user['id']);
                $message = '已拒絕使用者申請';
            }

            return $this->response->setJSON(['status' => 'success', 'message' => $message]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * 取得公司邀請碼
     * GET /api/companies/me/invite-code
     */
    public function getInviteCode()
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user || !($user['is_company_manager'] ?? false)) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '權限不足']);
            }

            $company = $this->companyModel->find($user['company_id']);
            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'invite_code' => $company['invite_code'],
                    'invite_code_active' => $company['invite_code_active']
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * 產生/重整公司邀請碼
     * POST /api/companies/me/generate-invite-code
     */
    public function generateInviteCode()
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user || !($user['is_company_manager'] ?? false)) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '權限不足']);
            }

            $newCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            $this->companyModel->update($user['company_id'], [
                'invite_code' => $newCode,
                'invite_code_active' => 1
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '邀請碼已更新',
                'data' => ['invite_code' => $newCode]
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * 取得更新會的成員指派列表
     * GET /api/companies/me/renewals/{renewalId}/members
     */
    public function getRenewalMembers($renewalId = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user || !($user['is_company_manager'] ?? false)) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '權限不足']);
            }

            // 驗證更新會是否屬於該公司
            $renewal = $this->urbanRenewalModel->find($renewalId);
            if (!$renewal || $renewal['company_id'] != $user['company_id']) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => '找不到該更新會或無權限']);
            }

            $assignmentModel = new UserRenewalAssignmentModel();
            $members = $assignmentModel->getAssignedUsers($renewalId);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $members
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * 指派成員至更新會
     * POST /api/companies/me/renewals/{renewalId}/assign
     */
    public function assignMemberToRenewal($renewalId = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user || !($user['is_company_manager'] ?? false)) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '權限不足']);
            }

            $data = $this->request->getJSON(true);
            $targetUserId = $data['user_id'] ?? null;
            $permissions = $data['permissions'] ?? [];

            if (!$targetUserId) {
                return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => '缺少使用者 ID']);
            }

            // 驗證更新會是否屬於該公司
            $renewal = $this->urbanRenewalModel->find($renewalId);
            if (!$renewal || $renewal['company_id'] != $user['company_id']) {
                return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => '找不到該更新會或無權限']);
            }

            // 驗證被指派的使用者是否屬於該公司且已審核通過
            $userModel = new UserModel();
            $targetUser = $userModel->find($targetUserId);
            if (!$targetUser || $targetUser['company_id'] != $user['company_id'] || $targetUser['approval_status'] !== 'approved') {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '無法指派此使用者（未核准或不屬於該公司）']);
            }

            $assignmentModel = new UserRenewalAssignmentModel();
            $assignmentModel->assign($targetUserId, $renewalId, $user['id'], $permissions);

            // 如果該使用者目前沒有關聯任何更新會，則設為此更新會（向下相容舊邏輯）
            if (empty($targetUser['urban_renewal_id'])) {
                $userModel->update($targetUserId, ['urban_renewal_id' => $renewalId]);
            }

            return $this->response->setJSON(['status' => 'success', 'message' => '指派成功']);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * 取消指派成員
     * DELETE /api/companies/me/renewals/{renewalId}/members/{userId}
     */
    public function unassignMemberFromRenewal($renewalId = null, $userId = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user || !($user['is_company_manager'] ?? false)) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '權限不足']);
            }

            // 驗證更新會權限
            $renewal = $this->urbanRenewalModel->find($renewalId);
            if (!$renewal || $renewal['company_id'] != $user['company_id']) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '無權限操作此更新會']);
            }

            // 檢查該使用者是否為此更新會的負責管理員
            if (!empty($renewal['assigned_admin_id']) && $renewal['assigned_admin_id'] == $userId) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error', 
                    'message' => '無法移除指派：該使用者是此更新會的負責管理員，請先在「分配更新會」功能中取消分配後再操作'
                ]);
            }

            $assignmentModel = new UserRenewalAssignmentModel();
            $assignmentModel->unassign($userId, $renewalId);

            // 更新使用者的預設更新會 ID（如果剛好是這一個）
            $userModel = new UserModel();
            $targetUser = $userModel->find($userId);
            if ($targetUser && $targetUser['urban_renewal_id'] == $renewalId) {
                // 尋找下一個可用的指派
                $otherAssignments = $assignmentModel->where('user_id', $userId)->first();
                $nextRenewalId = $otherAssignments ? $otherAssignments['urban_renewal_id'] : null;
                $userModel->update($userId, ['urban_renewal_id' => $nextRenewalId]);
            }

            return $this->response->setJSON(['status' => 'success', 'message' => '已取消指派']);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * 取得可供指派的成員列表（已核准的公司成員）
     * GET /api/companies/me/available-members
     */
    public function getAvailableMembers()
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user || !($user['is_company_manager'] ?? false)) {
                return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '權限不足']);
            }

            $userModel = new UserModel();
            $members = $userModel->where('company_id', $user['company_id'])
                ->where('approval_status', 'approved')
                ->findAll();

            foreach ($members as &$m) {
                unset($m['password_hash'], $m['password_reset_token']);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $members
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
