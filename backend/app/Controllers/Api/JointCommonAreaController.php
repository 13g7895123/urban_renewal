<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\JointCommonAreaModel;
use App\Models\UrbanRenewalModel;
use App\Models\BuildingModel;
use CodeIgniter\HTTP\ResponseInterface;

class JointCommonAreaController extends BaseController
{
    protected $jointCommonAreaModel;
    protected $urbanRenewalModel;
    protected $buildingModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->jointCommonAreaModel = new JointCommonAreaModel();
        $this->urbanRenewalModel = new UrbanRenewalModel();
        $this->buildingModel = new BuildingModel();
    }

    /**
     * Get all joint common areas for a specific urban renewal
     * GET /api/urban-renewals/{urbanRenewalId}/joint-common-areas
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
                if (!$userCompanyId || $urbanRenewal['company_id'] != $userCompanyId) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '您沒有權限查看此更新會的共有部分資料'
                    ]);
                }
            }

            $jointAreas = $this->jointCommonAreaModel->getByUrbanRenewalId($urbanRenewalId);

            // Format joint areas for frontend
            $formattedAreas = array_map(function($area) {
                // Get corresponding building info
                $correspondingBuilding = null;
                if ($area['corresponding_building_id']) {
                    $correspondingBuilding = $this->buildingModel->find($area['corresponding_building_id']);
                }

                return [
                    'id' => $area['id'],
                    'urban_renewal_id' => $area['urban_renewal_id'],
                    'county' => $area['county'],
                    'district' => $area['district'],
                    'section' => $area['section'],
                    'building_number_main' => $area['building_number_main'],
                    'building_number_sub' => $area['building_number_sub'],
                    'building_number' => $this->jointCommonAreaModel->formatBuildingNumber($area),
                    'total_area' => $area['building_total_area'],
                    'corresponding_building_id' => $area['corresponding_building_id'],
                    'corresponding_building_number_1' => $correspondingBuilding ? $this->buildingModel->formatBuildingNumber($correspondingBuilding) : null,
                    'corresponding_building_number_2' => null, // Reserved for future use
                    'ownership_numerator' => $area['ownership_numerator'],
                    'ownership_denominator' => $area['ownership_denominator'],
                    'created_at' => $area['created_at'],
                    'updated_at' => $area['updated_at']
                ];
            }, $jointAreas);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '查詢成功',
                'data' => $formattedAreas
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '查詢失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create new joint common area
     * POST /api/urban-renewals/{urbanRenewalId}/joint-common-areas
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
                if (!$userCompanyId || $urbanRenewal['company_id'] != $userCompanyId) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '您沒有權限新增此更新會的共有部分資料'
                    ]);
                }
            }

            $data = $this->request->getJSON(true);
            
            $insertData = [
                'urban_renewal_id' => $urbanRenewalId,
                'county' => $data['county'] ?? '',
                'district' => $data['district'] ?? '',
                'section' => $data['section'] ?? '',
                'building_number_main' => $data['building_number_main'] ?? '',
                'building_number_sub' => $data['building_number_sub'] ?? '',
                'building_total_area' => $data['building_total_area'] ?? null,
                'corresponding_building_id' => $data['corresponding_building_id'] ?? null,
                'ownership_numerator' => $data['ownership_numerator'] ?? null,
                'ownership_denominator' => $data['ownership_denominator'] ?? null
            ];

            if (!$this->jointCommonAreaModel->insert($insertData)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '新增失敗',
                    'errors' => $this->jointCommonAreaModel->errors()
                ]);
            }

            $newId = $this->jointCommonAreaModel->getInsertID();

            return $this->response->setStatusCode(201)->setJSON([
                'status' => 'success',
                'message' => '新增成功',
                'data' => ['id' => $newId]
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '新增失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get single joint common area
     * GET /api/joint-common-areas/{id}
     */
    public function show($id = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少共有部分ID參數'
                ]);
            }

            $jointArea = $this->jointCommonAreaModel->find($id);
            if (!$jointArea) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的共有部分'
                ]);
            }

            // Check permission
            if (!$isAdmin && $isCompanyManager) {
                $urbanRenewal = $this->urbanRenewalModel->find($jointArea['urban_renewal_id']);
                $userCompanyId = $user['company_id'] ?? null;
                if (!$userCompanyId || ($urbanRenewal && $urbanRenewal['company_id'] != $userCompanyId)) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '您沒有權限查看此共有部分資料'
                    ]);
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $jointArea
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '查詢失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update joint common area
     * PUT /api/joint-common-areas/{id}
     */
    public function update($id = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少共有部分ID參數'
                ]);
            }

            $jointArea = $this->jointCommonAreaModel->find($id);
            if (!$jointArea) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的共有部分'
                ]);
            }

            // Check permission
            if (!$isAdmin && $isCompanyManager) {
                $urbanRenewal = $this->urbanRenewalModel->find($jointArea['urban_renewal_id']);
                $userCompanyId = $user['company_id'] ?? null;
                if (!$userCompanyId || ($urbanRenewal && $urbanRenewal['company_id'] != $userCompanyId)) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '您沒有權限修改此共有部分資料'
                    ]);
                }
            }

            $data = $this->request->getJSON(true);

            $updateData = [];
            if (isset($data['county'])) $updateData['county'] = $data['county'];
            if (isset($data['district'])) $updateData['district'] = $data['district'];
            if (isset($data['section'])) $updateData['section'] = $data['section'];
            if (isset($data['building_number_main'])) $updateData['building_number_main'] = $data['building_number_main'];
            if (isset($data['building_number_sub'])) $updateData['building_number_sub'] = $data['building_number_sub'];
            if (isset($data['building_total_area'])) $updateData['building_total_area'] = $data['building_total_area'];
            if (isset($data['corresponding_building_id'])) $updateData['corresponding_building_id'] = $data['corresponding_building_id'];
            if (isset($data['ownership_numerator'])) $updateData['ownership_numerator'] = $data['ownership_numerator'];
            if (isset($data['ownership_denominator'])) $updateData['ownership_denominator'] = $data['ownership_denominator'];

            if (!$this->jointCommonAreaModel->update($id, $updateData)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '更新失敗',
                    'errors' => $this->jointCommonAreaModel->errors()
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => '更新成功'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '更新失敗：' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete joint common area
     * DELETE /api/joint-common-areas/{id}
     */
    public function delete($id = null)
    {
        try {
            $user = $_SERVER['AUTH_USER'] ?? null;
            $isAdmin = $user && isset($user['role']) && $user['role'] === 'admin';
            $isCompanyManager = $user && isset($user['is_company_manager']) && $user['is_company_manager'] == 1;

            if (!$id) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => '缺少共有部分ID參數'
                ]);
            }

            $jointArea = $this->jointCommonAreaModel->find($id);
            if (!$jointArea) {
                return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => '找不到指定的共有部分'
                ]);
            }

            // Check permission
            if (!$isAdmin && $isCompanyManager) {
                $urbanRenewal = $this->urbanRenewalModel->find($jointArea['urban_renewal_id']);
                $userCompanyId = $user['company_id'] ?? null;
                if (!$userCompanyId || ($urbanRenewal && $urbanRenewal['company_id'] != $userCompanyId)) {
                    return $this->response->setStatusCode(403)->setJSON([
                        'status' => 'error',
                        'message' => '您沒有權限刪除此共有部分資料'
                    ]);
                }
            }

            $this->jointCommonAreaModel->delete($id);

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
}
