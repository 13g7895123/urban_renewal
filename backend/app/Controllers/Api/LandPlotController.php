<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\LandPlotModel;
use App\Models\UrbanRenewalModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Traits\HasRbacPermissions;

class LandPlotController extends BaseController
{
    use HasRbacPermissions;

    protected $landPlotModel;
    protected $urbanRenewalModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->landPlotModel = new LandPlotModel();
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
     * Get all land plots for a specific urban renewal
     * GET /api/urban-renewals/{urbanRenewalId}/land-plots
     */
    public function index($urbanRenewalId = null)
    {
        try {
            // Get authenticated user
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            if (!$urbanRenewalId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少更新會ID參數'
                ]);
            }

            // Check if urban renewal exists
            $urbanRenewal = $this->urbanRenewalModel->find($urbanRenewalId);
            if (!$urbanRenewal) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的更新會'
                ]);
            }

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                $userCompanyId = $user['company_id'] ?? null;
                // Fallback for legacy
                if (!$userCompanyId && isset($user['urban_renewal_id'])) {
                    $userRenewal = $this->urbanRenewalModel->find($user['urban_renewal_id']);
                    if ($userRenewal) $userCompanyId = $userRenewal['company_id'];
                }

                if (!$userCompanyId || $urbanRenewal['company_id'] != $userCompanyId) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '您沒有權限查看此更新會的地號資料'
                    ]);
                }
            }

            $landPlots = $this->landPlotModel->getLandPlotsByUrbanRenewal($urbanRenewalId);            // Format land plots for frontend
            $formattedPlots = array_map(function($plot) {
                return [
                    'id' => $plot['id'],
                    'county' => $plot['county'],
                    'district' => $plot['district'],
                    'section' => $plot['section'],
                    'landNumberMain' => $plot['land_number_main'],
                    'landNumberSub' => $plot['land_number_sub'],
                    'landNumber' => $this->landPlotModel->formatLandNumber($plot),
                    'fullLandNumber' => $this->landPlotModel->getFullLandNumber($plot),
                    'landArea' => $plot['land_area'],
                    'isRepresentative' => (bool) $plot['is_representative'],
                    'createdAt' => $plot['created_at'],
                    'updatedAt' => $plot['updated_at']
                ];
            }, $landPlots);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '查詢成功',
                'data' => $formattedPlots
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '查詢失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single land plot
     * GET /api/land-plots/{id}
     */
    public function show($id = null)
    {
        try {
            // Get authenticated user
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少地號ID參數'
                ]);
            }

            $landPlot = $this->landPlotModel->getLandPlot($id);

            if (!$landPlot) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的地號'
                ]);
            }

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                $urbanRenewal = $this->urbanRenewalModel->find($landPlot['urban_renewal_id']);
                $userCompanyId = $user['company_id'] ?? null;
                
                // Fallback for legacy
                if (!$userCompanyId && isset($user['urban_renewal_id'])) {
                    $userRenewal = $this->urbanRenewalModel->find($user['urban_renewal_id']);
                    if ($userRenewal) $userCompanyId = $userRenewal['company_id'];
                }

                if (!$urbanRenewal || !$userCompanyId || $urbanRenewal['company_id'] != $userCompanyId) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '您沒有權限查看此地號'
                    ]);
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '查詢成功',
                'data' => $landPlot
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '查詢失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new land plot
     * POST /api/urban-renewals/{urbanRenewalId}/land-plots
     */
    public function create($urbanRenewalId = null)
    {
        try {
            // Get authenticated user
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            if (!$urbanRenewalId) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少更新會ID參數'
                ]);
            }

            // Check if urban renewal exists
            $urbanRenewal = $this->urbanRenewalModel->find($urbanRenewalId);
            if (!$urbanRenewal) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的更新會'
                ]);
            }

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                $userCompanyId = $user['company_id'] ?? null;
                // Fallback for legacy
                if (!$userCompanyId && isset($user['urban_renewal_id'])) {
                    $userRenewal = $this->urbanRenewalModel->find($user['urban_renewal_id']);
                    if ($userRenewal) $userCompanyId = $userRenewal['company_id'];
                }

                if (!$userCompanyId || $urbanRenewal['company_id'] != $userCompanyId) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '您沒有權限為此更新會新增地號'
                    ]);
                }
            }

            // Get data from request
            $data = [];
            if ($this->request->getHeaderLine('Content-Type') === 'application/json') {
                $json = $this->request->getJSON(true);
                $data = [
                    'urban_renewal_id' => $urbanRenewalId,
                    'county' => $json['county'] ?? null,
                    'district' => $json['district'] ?? null,
                    'section' => $json['section'] ?? null,
                    'land_number_main' => $json['landNumberMain'] ?? $json['land_number_main'] ?? null,
                    'land_number_sub' => $json['landNumberSub'] ?? $json['land_number_sub'] ?? null,
                    'land_area' => $json['landArea'] ?? $json['land_area'] ?? null,
                    'is_representative' => $json['isRepresentative'] ?? $json['is_representative'] ?? 0,
                ];
            } else {
                $data = [
                    'urban_renewal_id' => $urbanRenewalId,
                    'county' => $this->request->getPost('county'),
                    'district' => $this->request->getPost('district'),
                    'section' => $this->request->getPost('section'),
                    'land_number_main' => $this->request->getPost('landNumberMain') ?? $this->request->getPost('land_number_main'),
                    'land_number_sub' => $this->request->getPost('landNumberSub') ?? $this->request->getPost('land_number_sub'),
                    'land_area' => $this->request->getPost('landArea') ?? $this->request->getPost('land_area'),
                    'is_representative' => $this->request->getPost('isRepresentative') ?? $this->request->getPost('is_representative') ?? 0,
                ];
            }

            // Check if land number already exists
            if ($this->landPlotModel->landNumberExists(
                $urbanRenewalId,
                $data['county'],
                $data['district'],
                $data['section'],
                $data['land_number_main'],
                $data['land_number_sub']
            )) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '此地號已存在，請檢查後重新輸入'
                ]);
            }

            if (!$this->landPlotModel->insert($data)) {
                $errors = $this->landPlotModel->errors();
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '資料驗證失敗',
                    'errors' => $errors
                ]);
            }

            $insertId = $this->landPlotModel->getInsertID();
            $newData = $this->landPlotModel->find($insertId);

            return $this->response->setStatusCode(201)->setJSON([
                'status' => 'success',
                'message' => '新增地號成功',
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
     * Update land plot
     * PUT /api/land-plots/{id}
     */
    public function update($id = null)
    {
        try {
            // Get authenticated user
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少地號ID參數'
                ]);
            }

            $existing = $this->landPlotModel->find($id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的地號'
                ]);
            }

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                $urbanRenewal = $this->urbanRenewalModel->find($existing['urban_renewal_id']);
                $userCompanyId = $user['company_id'] ?? null;
                
                // Fallback for legacy
                if (!$userCompanyId && isset($user['urban_renewal_id'])) {
                    $userRenewal = $this->urbanRenewalModel->find($user['urban_renewal_id']);
                    if ($userRenewal) $userCompanyId = $userRenewal['company_id'];
                }

                if (!$urbanRenewal || !$userCompanyId || $urbanRenewal['company_id'] != $userCompanyId) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '您沒有權限修改此地號'
                    ]);
                }
            }

            // Get data from request
            $data = [];
            if ($this->request->getHeaderLine('Content-Type') === 'application/json') {
                $json = $this->request->getJSON(true);
                $data = [
                    'land_area' => $json['landArea'] ?? $json['land_area'] ?? null,
                    'is_representative' => $json['isRepresentative'] ?? $json['is_representative'] ?? null,
                ];
            } else {
                $data = [
                    'land_area' => $this->request->getPost('landArea') ?? $this->request->getPost('land_area'),
                    'is_representative' => $this->request->getPost('isRepresentative') ?? $this->request->getPost('is_representative'),
                ];
            }

            // Remove null values
            $data = array_filter($data, function($value) {
                return $value !== null;
            });

            if (!$this->landPlotModel->updateLandPlot($id, $data)) {
                $errors = $this->landPlotModel->errors();
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '資料驗證失敗',
                    'errors' => $errors
                ]);
            }

            $updatedData = $this->landPlotModel->find($id);

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
     * Delete land plot
     * DELETE /api/land-plots/{id}
     */
    public function delete($id = null)
    {
        try {
            // Get authenticated user
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少地號ID參數'
                ]);
            }

            $existing = $this->landPlotModel->find($id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的地號'
                ]);
            }

            // Check permission for company managers
            if (!$isAdmin && $isCompanyManager) {
                $urbanRenewal = $this->urbanRenewalModel->find($existing['urban_renewal_id']);
                $userCompanyId = $user['company_id'] ?? null;
                
                // Fallback for legacy
                if (!$userCompanyId && isset($user['urban_renewal_id'])) {
                    $userRenewal = $this->urbanRenewalModel->find($user['urban_renewal_id']);
                    if ($userRenewal) $userCompanyId = $userRenewal['company_id'];
                }

                if (!$urbanRenewal || !$userCompanyId || $urbanRenewal['company_id'] != $userCompanyId) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '您沒有權限刪除此地號'
                    ]);
                }
            }

            // TODO: Check if any owners are linked to this land plot
            // This would require additional models/checks

            if (!$this->landPlotModel->deleteLandPlot($id)) {
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
     * Set land plot as representative
     * PUT /api/land-plots/{id}/representative
     */
    public function setRepresentative($id = null)
    {
        try {
            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少地號ID參數'
                ]);
            }

            $existing = $this->landPlotModel->find($id);
            if (!$existing) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的地號'
                ]);
            }

            if (!$this->landPlotModel->setAsRepresentative($id)) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => '設定代表號失敗'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '設定代表號成功'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '設定代表號失敗：' . $e->getMessage()
            ]);
        }
    }
}