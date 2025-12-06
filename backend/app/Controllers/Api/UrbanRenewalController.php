<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\UrbanRenewalService;
use App\Services\AuthorizationService;
use App\Exceptions\NotFoundException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use App\Traits\HasRbacPermissions;

/**
 * 更新會 Controller
 * 
 * 使用 Entity + Repository + Service 架構
 */
class UrbanRenewalController extends BaseController
{
    use HasRbacPermissions;

    protected UrbanRenewalService $urbanRenewalService;
    protected AuthorizationService $authService;
    protected $urbanRenewalModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->urbanRenewalService = service('urbanRenewalService');
        $this->authService = service('authorizationService');
        $this->urbanRenewalModel = model('UrbanRenewalModel');

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
            $user = $_SERVER['AUTH_USER'] ?? null;
            
            $page = (int)($this->request->getGet('page') ?? 1);
            $perPage = (int)($this->request->getGet('per_page') ?? 10);
            
            $filters = [];
            if ($search = $this->request->getGet('search')) {
                $filters['search'] = $search;
            }

            $result = $this->urbanRenewalService->getList($user, $page, $perPage, $filters);

            // Add calculated member count and land area to all results
            foreach ($result['data'] as &$renewal) {
                $renewal['member_count'] = $this->urbanRenewalModel->calculateMemberCount($renewal['id']);
                $renewal['area'] = $this->urbanRenewalModel->calculateTotalLandArea($renewal['id']);
            }

            $pager = $this->urbanRenewalModel->pager;

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $result['data'],
                'pagination' => $result['pagination'],
                'message' => '取得更新會列表成功'
            ]);

        } catch (UnauthorizedException $e) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (ForbiddenException $e) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get urban renewals error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '取得更新會列表失敗'
            ]);
        }
    }

    /**
     * Get urban renewal by ID
     * GET /api/urban-renewals/{id}
     */
    public function show($id = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            
            $data = $this->urbanRenewalService->getDetail($user, (int)$id);

            // Add calculated statistics
            $data['member_count'] = $this->urbanRenewalModel->calculateMemberCount($id);
            $data['area'] = $this->urbanRenewalModel->calculateTotalLandArea($id);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $data,
                'message' => '取得更新會資料成功'
            ]);

        } catch (NotFoundException $e) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (UnauthorizedException $e) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (ForbiddenException $e) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get urban renewal error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '取得更新會資料失敗'
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
            $user = $_SERVER['AUTH_USER'] ?? null;
            $data = $this->request->getJSON(true);

            if (!$data || empty($data['name'])) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => 'error',
                    'message' => '更新會名稱為必填項目'
                ]);
            }

            $result = $this->urbanRenewalService->create($user, $data);

            return $this->response->setStatusCode(201)->setJSON([
                'status' => 'success',
                'data' => $result,
                'message' => '更新會建立成功'
            ]);

        } catch (UnauthorizedException $e) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (ForbiddenException $e) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (ValidationException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Create urban renewal error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '更新會建立失敗'
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
            $user = $_SERVER['AUTH_USER'] ?? null;
            $data = $this->request->getJSON(true);

            $result = $this->urbanRenewalService->update($user, (int)$id, $data);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $result,
                'message' => '更新會更新成功'
            ]);

        } catch (NotFoundException $e) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (UnauthorizedException $e) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (ForbiddenException $e) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (ValidationException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Update urban renewal error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '更新會更新失敗'
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
            $user = $_SERVER['AUTH_USER'] ?? null;

            $this->urbanRenewalService->delete($user, (int)$id);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '更新會刪除成功'
            ]);

        } catch (NotFoundException $e) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (UnauthorizedException $e) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (ForbiddenException $e) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (ValidationException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Delete urban renewal error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '更新會刪除失敗'
            ]);
        }
    }

    /**
     * Get urban renewal statistics
     * GET /api/urban-renewals/{id}/statistics
     */
    public function statistics($id = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            
            $data = $this->urbanRenewalService->getStatistics($user, (int)$id);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $data,
                'message' => '取得更新會統計成功'
            ]);

        } catch (NotFoundException $e) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (ForbiddenException $e) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get urban renewal statistics error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '取得更新會統計失敗'
            ]);
        }
    }

    /**
     * Get urban renewals by company
     * GET /api/companies/{id}/urban-renewals
     */
    public function getByCompany($companyId = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            
            $data = $this->urbanRenewalService->getByCompany($user, (int)$companyId);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $data,
                'message' => '取得企業更新會列表成功'
            ]);

        } catch (ForbiddenException $e) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get urban renewals by company error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '取得企業更新會列表失敗'
            ]);
        }
    }
}
