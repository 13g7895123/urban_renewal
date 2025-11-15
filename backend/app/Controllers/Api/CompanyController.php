<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\UrbanRenewalModel;
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

            $renewals = $this->companyModel->getRenewals($companyId, $page, $perPage);

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
                'pager' => [
                    'total' => $pager->getTotal(),
                    'count' => count($renewals),
                    'per_page' => $pager->getPerPage(),
                    'current_page' => $pager->getCurrentPage(),
                    'last_page' => $pager->getLastPage(),
                    'from' => $pager->getFirstRow() + 1,
                    'to' => $pager->getLastRow() + 1
                ]
            ]);

        } catch (\Exception $e) {
            log_message('error', '[CompanyController::getRenewals] Exception: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '伺服器錯誤：' . $e->getMessage()
            ]);
        }
    }
}
