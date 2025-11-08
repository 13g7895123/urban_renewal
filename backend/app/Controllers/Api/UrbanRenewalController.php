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

            // 權限驗證：檢查用戶身份
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            // 系統管理員可以查看所有資料
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            // 企業管理者只能查看自己管理的企業資料
            $urbanRenewalId = null;
            if (!$isAdmin) {
                // 檢查是否為企業管理者
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

                $urbanRenewalId = $user['urban_renewal_id'] ?? null;
                if (!$urbanRenewalId) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '權限不足：您的帳號未關聯任何更新會'
                    ]);
                }
            }

            // 根據權限查詢資料
            if ($search) {
                $data = $this->urbanRenewalModel->searchByName($search, $page, $perPage, $urbanRenewalId);
            } else {
                $data = $this->urbanRenewalModel->getUrbanRenewals($page, $perPage, $urbanRenewalId);
            }

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

            // 權限驗證：檢查用戶是否有權存取此企業資料
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            // 系統管理員可以存取所有資料
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';

            // 非管理員只能存取自己所屬企業的資料
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
                        'message' => '權限不足：您無權存取其他企業的資料'
                    ]);
                }
            }

            $data = $this->urbanRenewalModel->getUrbanRenewal($id);

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
            $data = [
                'name' => $this->request->getPost('name'),
                'area' => $this->request->getPost('area'),
                'member_count' => $this->request->getPost('memberCount') ?? $this->request->getPost('member_count'),
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
                    'area' => $json['area'] ?? null,
                    'member_count' => $json['memberCount'] ?? $json['member_count'] ?? null,
                    'chairman_name' => $json['chairmanName'] ?? $json['chairman_name'] ?? null,
                    'chairman_phone' => $json['chairmanPhone'] ?? $json['chairman_phone'] ?? null,
                    'address' => $json['address'] ?? null,
                    'representative' => $json['representative'] ?? null,
                ];
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
            $newData = $this->urbanRenewalModel->find($insertId);

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
                    'area' => $json['area'] ?? null,
                    'member_count' => $json['memberCount'] ?? $json['member_count'] ?? null,
                    'chairman_name' => $json['chairmanName'] ?? $json['chairman_name'] ?? null,
                    'chairman_phone' => $json['chairmanPhone'] ?? $json['chairman_phone'] ?? null,
                    'address' => $json['address'] ?? null,
                    'representative' => $json['representative'] ?? null,
                ];
            } else {
                $data = [
                    'name' => $this->request->getPost('name'),
                    'area' => $this->request->getPost('area'),
                    'member_count' => $this->request->getPost('memberCount') ?? $this->request->getPost('member_count'),
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

            $updatedData = $this->urbanRenewalModel->find($id);

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

            // 只有系統管理員可以分配更新會
            if ($user['role'] !== 'admin') {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => '權限不足，只有系統管理員可以分配更新會'
                ]);
            }

            $data = $this->request->getJSON(true);

            if (!isset($data['assignments']) || !is_array($data['assignments'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '請提供有效的分配資料'
                ]);
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
     * Get company managers list
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
                                  ->orderBy('full_name', 'ASC')
                                  ->findAll();

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $managers
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '取得企業管理者列表失敗：' . $e->getMessage()
            ]);
        }
    }
}