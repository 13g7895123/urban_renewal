<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use CodeIgniter\HTTP\ResponseInterface;

class CompanyController extends BaseController
{
    protected $companyModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->companyModel = new CompanyModel();

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
     * Get current user's company profile
     * GET /api/companies/me
     */
    public function me()
    {
        try {
            // 權限驗證：檢查用戶身份
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            // 檢查是否為企業管理者
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

            // 取得用戶的 urban_renewal_id
            $urbanRenewalId = $user['urban_renewal_id'] ?? null;
            if (!$urbanRenewalId) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => '權限不足：您的帳號未關聯任何企業'
                ]);
            }

            // 透過 urban_renewal_id 查詢對應的企業資料
            $company = $this->companyModel->getByUrbanRenewalId($urbanRenewalId);

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
            // 權限驗證：檢查用戶身份
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ]);
            }

            // 檢查是否為企業管理者
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

            // 取得用戶的 urban_renewal_id
            $urbanRenewalId = $user['urban_renewal_id'] ?? null;
            if (!$urbanRenewalId) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => '權限不足：您的帳號未關聯任何企業'
                ]);
            }

            // 透過 urban_renewal_id 查詢對應的企業資料
            $company = $this->companyModel->getByUrbanRenewalId($urbanRenewalId);

            if (!$company) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '查無企業資料'
                ]);
            }

            // 取得請求資料
            $data = $this->request->getJSON(true);

            if (empty($data)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '無效的請求資料'
                ]);
            }

            // 更新企業資料
            $result = $this->companyModel->updateCompany($company['id'], $data);

            if (!$result) {
                $errors = $this->companyModel->errors();
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '更新失敗',
                    'errors' => $errors
                ]);
            }

            // 查詢更新後的資料
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
}
