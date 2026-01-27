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

    /**
     * Get company managers for assignment dropdown
     * GET /api/urban-renewals/company-managers
     */
    public function getCompanyManagers()
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            $userRole = $user['role'] ?? '';
            $isCompanyManager = ($user['is_company_manager'] ?? 0) == 1;

            if (!$user || (!$isCompanyManager && !in_array($userRole, ['admin', 'land_owner']))) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => '您沒有權限存取此功能'
                ]);
            }

            // Get all company managers (filter by company_id if not admin)
            $userModel = model('UserModel');
            $builder = $userModel->where('is_company_manager', 1)
                ->where('is_active', 1);

            if ($userRole !== 'admin') {
                $companyId = auth_get_user_company_id($user);
                if ($companyId) {
                    $builder->where('company_id', $companyId);
                }
            }

            $managers = $builder->select('id, full_name as name, email, company_id')
                ->findAll();

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'managers' => $managers
                ],
                'message' => '取得企業管理員列表成功'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Get company managers error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '取得企業管理員列表失敗'
            ]);
        }
    }

    /**
     * Batch assign urban renewals to managers
     * POST /api/urban-renewals/batch-assign
     */
    public function batchAssign()
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            $userRole = $user['role'] ?? '';
            $isAdmin = $userRole === 'admin';
            $isCompanyManager = ($user['is_company_manager'] ?? 0) == 1;

            if (!$user || (!$isAdmin && !$isCompanyManager)) {
                return $this->response->setStatusCode(403)->setJSON([
                    'status' => 'error',
                    'message' => '您沒有權限存取此功能'
                ]);
            }

            $data = $this->request->getJSON(true);
            $assignments = $data['assignments'] ?? [];

            if (empty($assignments)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '請提供分配資料'
                ]);
            }

            $userModel = model('UserModel');
            $assignmentModel = model('UserRenewalAssignmentModel');
            $updatedCount = 0;

            foreach ($assignments as $urbanRenewalId => $managerId) {
                // 獲取當前更新會的資料，檢查是否有舊的管理員
                $currentRenewal = $this->urbanRenewalModel->find($urbanRenewalId);
                $oldAdminId = $currentRenewal['assigned_admin_id'] ?? null;
                
                if (empty($managerId)) {
                    // 取消分配：移除舊管理員的指派記錄
                    if ($oldAdminId) {
                        $assignmentModel->unassign($oldAdminId, $urbanRenewalId);
                        
                        // 清除舊管理員的 urban_renewal_id
                        // 檢查舊管理員是否還有其他更新會的負責管理
                        $otherAssignments = $this->urbanRenewalModel
                            ->where('assigned_admin_id', $oldAdminId)
                            ->where('id !=', $urbanRenewalId)
                            ->countAllResults();
                        
                        // 如果舊管理員沒有其他負責的更新會，清除其 urban_renewal_id
                        if ($otherAssignments === 0) {
                            $oldAdmin = $userModel->find($oldAdminId);
                            if ($oldAdmin && $oldAdmin['urban_renewal_id'] == $urbanRenewalId) {
                                $userModel->update($oldAdminId, ['urban_renewal_id' => null]);
                            }
                        }
                    }
                    $this->urbanRenewalModel->update($urbanRenewalId, ['assigned_admin_id' => null]);
                    $updatedCount++;
                    continue;
                }

                // 驗證管理員是否存在
                $manager = $userModel->find($managerId);
                if (!$manager || $manager['is_company_manager'] != 1) {
                    continue;
                }

                // 驗證權限（企業管理者只能指派自己公司的成員）
                if (!$isAdmin) {
                    $companyId = auth_get_user_company_id($user);
                    if ($manager['company_id'] != $companyId) {
                        continue;
                    }
                }

                $result = $this->urbanRenewalModel->update($urbanRenewalId, [
                    'assigned_admin_id' => (int)$managerId
                ]);

                if ($result) {
                    // 如果更換管理員，先移除舊管理員的指派記錄
                    if ($oldAdminId && $oldAdminId != $managerId) {
                        $assignmentModel->unassign($oldAdminId, $urbanRenewalId);
                        
                        // 清除舊管理員的 urban_renewal_id
                        // 檢查舊管理員是否還有其他更新會的負責管理
                        $otherAssignments = $this->urbanRenewalModel
                            ->where('assigned_admin_id', $oldAdminId)
                            ->where('id !=', $urbanRenewalId)
                            ->countAllResults();
                        
                        // 如果舊管理員沒有其他負責的更新會，清除其 urban_renewal_id
                        if ($otherAssignments === 0) {
                            $oldAdmin = $userModel->find($oldAdminId);
                            if ($oldAdmin && $oldAdmin['urban_renewal_id'] == $urbanRenewalId) {
                                $userModel->update($oldAdminId, ['urban_renewal_id' => null]);
                            }
                        }
                    }
                    
                    // 同步在 user_renewal_assignments 表中創建新管理員的指派記錄
                    // 確保管理責任分配與成員指派資料一致
                    $assignmentModel->assign(
                        (int)$managerId, 
                        (int)$urbanRenewalId, 
                        $user['id'],
                        ['role' => 'assigned_admin'] // 標記為負責管理員
                    );
                    
                    // 設置新管理員的 urban_renewal_id 為此更新會
                    // 如果新管理員目前沒有預設更新會，或者是負責這個更新會，就設為此更新會
                    if (empty($manager['urban_renewal_id']) || $manager['urban_renewal_id'] == $urbanRenewalId) {
                        $userModel->update($managerId, ['urban_renewal_id' => $urbanRenewalId]);
                    }
                    
                    $updatedCount++;
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => ['updated_count' => $updatedCount],
                'message' => "成功更新 {$updatedCount} 個項目的指派內容"
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Batch assign error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '批次指派失敗'
            ]);
        }
    }
}
