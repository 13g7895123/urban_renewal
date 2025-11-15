<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UrbanRenewalModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Traits\HasRbacPermissions;

class UrbanRenewalController extends BaseController
{
    use HasRbacPermissions;

    protected $urbanRenewalModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->urbanRenewalModel = new UrbanRenewalModel();

        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    }

    /**
     * Handle preflight OPTIONS requests
     */
    public function options()
    {
        return $this->response->setStatusCode(200);
    }

    /**
     * Get all urban renewals
     * GET /api/urban-renewals
     */
    public function index()
    {
        try {
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('per_page') ?? 10;
            $search = $this->request->getGet('search');

            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            // 新架構：企業管理者通過 company_id 查看旗下更新會
            $companyId = null;
            if (!$isAdmin) {
                $isCompanyManager = isset($user['is_company_manager']) && 
                                   ($user['is_company_manager'] === 1 || 
                                    $user['is_company_manager'] === '1' || 
                                    $user['is_company_manager'] === true);
                
                if (!$isCompanyManager) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '權限不足：您沒有管理更新會的權限'
                    ]);
                }

                // 新架構：取得 company_id (過渡期也支持 urban_renewal_id)
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
            }

            // 根據權限查詢資料
            if ($search) {
                $data = $this->urbanRenewalModel->searchByName($search, $page, $perPage, $companyId);
            } else {
                $data = $this->urbanRenewalModel->getUrbanRenewals($page, $perPage, $companyId);
            }

            // Add calculated member count and land area to all results
            foreach ($data as &$renewal) {
                $renewal['member_count'] = $this->urbanRenewalModel->calculateMemberCount($renewal['id']);
                $renewal['area'] = $this->urbanRenewalModel->calculateTotalLandArea($renewal['id']);
            }
            unset($renewal);

            $pager = $this->urbanRenewalModel->pager;

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '查詢成功',
                'data' => $data,
                'pagination' => [
                    'current_page' => $pager->getCurrentPage(),
                    'per_page' => $pager->getPerPage(),
                    'total' => $pager->getTotal(),
                    'total_pages' => $pager->getPageCount()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '查詢失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single urban renewal
     * GET /api/urban-renewals/{id}
     */
    public function show($id = null)
    {
        try {
            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少ID參數'
                ]);
            }

            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            $isAdmin = isset($user['role']) && $user['role'] === 'admin';

            // 非管理員需要檢查權限
            if (!$isAdmin) {
                $isCompanyManager = isset($user['is_company_manager']) && 
                                   ($user['is_company_manager'] === 1 || 
                                    $user['is_company_manager'] === '1' || 
                                    $user['is_company_manager'] === true);
                
                if (!$isCompanyManager) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '權限不足：您沒有查看此資料的權限'
                    ]);
                }

                // 新架構：檢查該更新會是否屬於用戶的公司
                $requestedRenewal = $this->urbanRenewalModel->find($id);
                if (!$requestedRenewal) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 'error',
                        'message' => '找不到指定的更新會'
                    ]);
                }

                // 獲取用戶的 company_id
                $userCompanyId = $user['company_id'] ?? null;
                if (!$userCompanyId && isset($user['urban_renewal_id'])) {
                    // 過渡期兼容
                    $userRenewal = $this->urbanRenewalModel->find($user['urban_renewal_id']);
                    if ($userRenewal && $userRenewal['company_id']) {
                        $userCompanyId = $userRenewal['company_id'];
                    }
                }

                // 檢查該更新會是否屬於用戶的公司
                if (!$userCompanyId || (int)$requestedRenewal['company_id'] !== (int)$userCompanyId) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '權限不足：您無權存取此更新會的資料'
                    ]);
                }
            }

            $data = $this->urbanRenewalModel->getWithMemberCount($id);

            if (!$data) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的更新會'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '查詢成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '查詢失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new urban renewal
     * POST /api/urban-renewals
     */
    public function create()
    {
        try {
            // 權限驗證
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            // 非管理員需要檢查企業管理者權限
            if (!$isAdmin) {
                $isCompanyManager = isset($user['is_company_manager']) && 
                                   ($user['is_company_manager'] === 1 || 
                                    $user['is_company_manager'] === '1' || 
                                    $user['is_company_manager'] === true);
                
                if (!$isCompanyManager) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '權限不足：您沒有建立更新會的權限'
                    ]);
                }
            }

            $data = [
                'name' => $this->request->getPost('name'),
                'chairman_name' => $this->request->getPost('chairmanName') ?? $this->request->getPost('chairman_name'),
                'chairman_phone' => $this->request->getPost('chairmanPhone') ?? $this->request->getPost('chairman_phone'),
                'address' => $this->request->getPost('address'),
                'representative' => $this->request->getPost('representative'),
            ];

            // Handle JSON requests
            if ($this->request->getHeaderLine('Content-Type') === 'application/json') {
                $json = $this->request->getJSON(true);
                $data = [
                    'name' => $json['name'] ?? null,
                    'chairman_name' => $json['chairmanName'] ?? $json['chairman_name'] ?? null,
                    'chairman_phone' => $json['chairmanPhone'] ?? $json['chairman_phone'] ?? null,
                    'address' => $json['address'] ?? null,
                    'representative' => $json['representative'] ?? null,
                    'company_id' => $json['company_id'] ?? $json['companyId'] ?? null,
                ];
                
                // 企業管理者建立新更新會時，自動關聯到自己的企業
                if (!$isAdmin && empty($data['company_id'])) {
                    $companyId = $user['company_id'] ?? null;
                    if (!$companyId && isset($user['urban_renewal_id'])) {
                        // 過渡期兼容：從 urban_renewal_id 推導 company_id
                        $existingRenewal = $this->urbanRenewalModel->find($user['urban_renewal_id']);
                        if ($existingRenewal && $existingRenewal['company_id']) {
                            $companyId = $existingRenewal['company_id'];
                        }
                    }
                    
                    if ($companyId) {
                        $data['company_id'] = $companyId;
                    }
                }
            }

            if (!$this->urbanRenewalModel->insert($data)) {
                $errors = $this->urbanRenewalModel->errors();
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '資料驗證失敗',
                    'errors' => $errors
                ]);
            }

            $insertId = $this->urbanRenewalModel->getInsertID();
            $newData = $this->urbanRenewalModel->getWithMemberCount($insertId);

            return $this->response->setStatusCode(201)->setJSON([
                'status' => 'success',
                'message' => '新增更新會成功',
                'data' => $newData
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '新增失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update urban renewal
     * PUT /api/urban-renewals/{id}
     */
    public function update($id = null)
    {
        try {
            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少ID參數'
                ]);
            }

            // 權限驗證：檢查用戶是否有權修改此企業資料
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            // 系統管理員可以修改所有資料
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';

            // 非管理員只能修改自己所屬企業的資料
            if (!$isAdmin) {
                $userUrbanRenewalId = $user['urban_renewal_id'] ?? null;

                if (!$userUrbanRenewalId) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '權限不足：您的帳號未關聯任何企業'
                    ]);
                }

                if ((int)$userUrbanRenewalId !== (int)$id) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '權限不足：您無權修改其他企業的資料'
                    ]);
                }
            }

            $existing = $this->urbanRenewalModel->find($id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的更新會'
                ]);
            }

            // Handle JSON requests
            $data = [];
            if ($this->request->getHeaderLine('Content-Type') === 'application/json') {
                $json = $this->request->getJSON(true);
                $data = [
                    'name' => $json['name'] ?? null,
                    'chairman_name' => $json['chairmanName'] ?? $json['chairman_name'] ?? null,
                    'chairman_phone' => $json['chairmanPhone'] ?? $json['chairman_phone'] ?? null,
                    'address' => $json['address'] ?? null,
                    'representative' => $json['representative'] ?? null,
                ];
            } else {
                $data = [
                    'name' => $this->request->getPost('name'),
                    'chairman_name' => $this->request->getPost('chairmanName') ?? $this->request->getPost('chairman_name'),
                    'chairman_phone' => $this->request->getPost('chairmanPhone') ?? $this->request->getPost('chairman_phone'),
                    'address' => $this->request->getPost('address'),
                    'representative' => $this->request->getPost('representative'),
                ];
            }

            // Remove null values
            $data = array_filter($data, function($value) {
                return $value !== null;
            });

            if (!$this->urbanRenewalModel->update($id, $data)) {
                $errors = $this->urbanRenewalModel->errors();
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '資料驗證失敗',
                    'errors' => $errors
                ]);
            }

            $updatedData = $this->urbanRenewalModel->getWithMemberCount($id);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '更新成功',
                'data' => $updatedData
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '更新失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete urban renewal
     * DELETE /api/urban-renewals/{id}
     */
    public function delete($id = null)
    {
        try {
            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少ID參數'
                ]);
            }

            $existing = $this->urbanRenewalModel->find($id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的更新會'
                ]);
            }

            if (!$this->urbanRenewalModel->delete($id)) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => '刪除失敗'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '刪除成功'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '刪除失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Batch assign admins to urban renewals
     * POST /api/urban-renewals/batch-assign
     */
    public function batchAssign()
    {
        try {
            // 權限驗證：檢查用戶身份
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權訪問'
                ]);
            }

            // 系統管理員和企業管理者都可以分配更新會
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = isset($user['is_company_manager']) &&
                               ($user['is_company_manager'] === 1 ||
                                $user['is_company_manager'] === '1' ||
                                $user['is_company_manager'] === true);

            if (!$isAdmin && !$isCompanyManager) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => '權限不足，只有系統管理員或企業管理者可以分配更新會'
                ]);
            }

            $data = $this->request->getJSON(true);

            if (!isset($data['assignments']) || !is_array($data['assignments'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '請提供有效的分配資料'
                ]);
            }

            // 企業管理者只能分配自己所屬的更新會
            if (!$isAdmin && $isCompanyManager) {
                $userUrbanRenewalId = $user['urban_renewal_id'] ?? null;

                foreach ($data['assignments'] as $urbanRenewalId => $adminId) {
                    if ((int)$urbanRenewalId !== (int)$userUrbanRenewalId) {
                        return $this->response->setStatusCode(403)->setJSON([
                            'status' => 'error',
                            'message' => '權限不足，您只能分配自己所屬的更新會'
                        ]);
                    }
                }
            }

            // 驗證每個分配的管理者是否存在且為企業管理者
            $userModel = new \App\Models\UserModel();
            foreach ($data['assignments'] as $urbanRenewalId => $adminId) {
                if ($adminId !== null && $adminId !== '') {
                    $admin = $userModel->find($adminId);
                    if (!$admin) {
                        return $this->response->setStatusCode(400)->setJSON([
                            'status' => 'error',
                            'message' => "管理者 ID {$adminId} 不存在"
                        ]);
                    }
                    if (!$admin['is_company_manager']) {
                        return $this->response->setStatusCode(400)->setJSON([
                            'status' => 'error',
                            'message' => "使用者 {$admin['full_name']} 不是企業管理者"
                        ]);
                    }
                }
            }

            $result = $this->urbanRenewalModel->batchAssignAdmin($data['assignments']);

            if ($result) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => '分配成功'
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => '分配失敗'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Batch assign error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '分配失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get company managers list grouped by urban_renewal_id
     * GET /api/urban-renewals/company-managers
     */
    public function getCompanyManagers()
    {
        try {
            // 權限驗證
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權訪問'
                ]);
            }

            $userModel = new \App\Models\UserModel();
            $managers = $userModel->where('is_company_manager', 1)
                                  ->where('is_active', 1)
                                  ->where('urban_renewal_id IS NOT NULL')
                                  ->orderBy('full_name', 'ASC')
                                  ->findAll();

            // 按 urban_renewal_id 分組
            $groupedManagers = [];
            foreach ($managers as $manager) {
                $renewalId = $manager['urban_renewal_id'];
                if (!isset($groupedManagers[$renewalId])) {
                    $groupedManagers[$renewalId] = [];
                }
                $groupedManagers[$renewalId][] = $manager;
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $groupedManagers
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '取得企業管理者列表失敗：' . $e->getMessage()
            ]);
        }
    }
}