<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use JsonException;
use App\Traits\HasRbacPermissions;
use App\Services\ExcelExportService;
use App\Services\PropertyOwnerService;
use App\Services\AuthorizationService;
use App\Exceptions\NotFoundException;
use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * 所有權人 Controller
 * 
 * 重構版本：使用 Entity + Repository + Service 架構
 * 
 * 架構說明：
 * - Controller：負責 HTTP 請求/回應處理、輸入驗證、錯誤處理
 * - Service：負責業務邏輯編排
 * - Repository：負責資料存取
 * - Entity：封裝領域資料與行為
 */
class PropertyOwnerController extends ResourceController
{
    use HasRbacPermissions;

    protected PropertyOwnerService $propertyOwnerService;
    protected AuthorizationService $authService;
    
    // 保留 Model 引用供 import 方法使用
    protected $propertyOwnerModel;

    public function __construct()
    {
        $this->propertyOwnerService = service('propertyOwnerService');
        $this->authService = service('authorizationService');
        $this->propertyOwnerModel = model('PropertyOwnerModel');
    }

    /**
     * Get property owners by urban renewal ID
     * GET /api/urban-renewals/{id}/property-owners
     */
    public function getByUrbanRenewal($urbanRenewalId): ResponseInterface
    {
        try {
            if (!is_numeric($urbanRenewalId)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Invalid urban renewal ID'
                ], 400);
            }

            $user = $_SERVER['AUTH_USER'] ?? null;
            
            if (ENVIRONMENT !== 'production') {
                log_message('debug', 'PropertyOwnerController::getByUrbanRenewal - Request ID: ' . $urbanRenewalId);
            }

            $data = $this->propertyOwnerService->getByUrbanRenewal($user, (int)$urbanRenewalId);

            return $this->respond([
                'status' => 'success',
                'data' => $data,
                'message' => 'Property owners retrieved successfully'
            ]);

        } catch (UnauthorizedException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (ForbiddenException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching property owners: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Failed to retrieve property owners'], 500);
        }
    }

    /**
     * Get all buildings owned by all property owners in an urban renewal
     * GET /api/urban-renewals/{id}/property-owners/buildings
     */
    public function getAllBuildingsByUrbanRenewal($urbanRenewalId): ResponseInterface
    {
        try {
            if (!is_numeric($urbanRenewalId)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Invalid urban renewal ID'
                ], 400);
            }

            $user = $_SERVER['AUTH_USER'] ?? null;

            $allBuildings = $this->propertyOwnerService->getAllBuildingsByUrbanRenewal($user, (int)$urbanRenewalId);

            return $this->respond([
                'status' => 'success',
                'data' => $allBuildings,
                'message' => 'Buildings retrieved successfully',
                'meta' => [
                    'total_buildings' => count($allBuildings),
                    'urban_renewal_id' => (int)$urbanRenewalId
                ]
            ]);

        } catch (UnauthorizedException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (ForbiddenException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching all buildings: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Failed to retrieve buildings'], 500);
        }
    }

    /**
     * Get all property owners
     * GET /api/property-owners
     */
    public function index(): ResponseInterface
    {
        try {
            $propertyOwners = $this->propertyOwnerModel->findAll();

            return $this->respond([
                'status' => 'success',
                'data' => $propertyOwners,
                'message' => 'Property owners retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching all property owners: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Failed to retrieve property owners'], 500);
        }
    }

    /**
     * Get single property owner with details
     * GET /api/property-owners/{id}
     */
    public function show($id = null): ResponseInterface
    {
        try {
            if (!is_numeric($id)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Invalid property owner ID'
                ], 400);
            }

            $user = $_SERVER['AUTH_USER'] ?? null;
            $data = $this->propertyOwnerService->getDetail($user, (int)$id);

            return $this->respond([
                'status' => 'success',
                'data' => $data,
                'message' => 'Property owner retrieved successfully'
            ]);

        } catch (UnauthorizedException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (ForbiddenException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (NotFoundException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching property owner: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Failed to retrieve property owner'], 500);
        }
    }

    /**
     * Create new property owner
     * POST /api/property-owners
     */
    public function create(): ResponseInterface
    {
        try {
            $this->response->setHeader('Content-Type', 'application/json; charset=utf-8');

            $rawInput = $this->request->getBody();

            if (empty($rawInput)) {
                return $this->respond(['status' => 'error', 'message' => 'No data provided'], 400);
            }

            try {
                $data = json_decode($rawInput, true, 512, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                log_message('error', 'JSON decode error: ' . $e->getMessage());
                return $this->respond(['status' => 'error', 'message' => 'Invalid JSON format: ' . $e->getMessage()], 400);
            }

            if (!$data || !is_array($data)) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid data format'], 400);
            }

            $user = $_SERVER['AUTH_USER'] ?? null;
            $result = $this->propertyOwnerService->create($user, $data);

            return $this->respond([
                'status' => 'success',
                'data' => $result,
                'message' => 'Property owner created successfully'
            ], 201);

        } catch (UnauthorizedException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (ForbiddenException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (ValidationException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage(), 'errors' => $e->getErrors()], 400);
        } catch (\InvalidArgumentException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            log_message('error', 'Error creating property owner: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Failed to create property owner'], 500);
        }
    }

    /**
     * Update property owner
     * PUT /api/property-owners/{id}
     */
    public function update($id = null): ResponseInterface
    {
        try {
            if (!is_numeric($id)) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid property owner ID'], 400);
            }

            $data = $this->request->getJSON(true);
            if (!$data) {
                return $this->respond(['status' => 'error', 'message' => 'No data provided'], 400);
            }

            $user = $_SERVER['AUTH_USER'] ?? null;
            $result = $this->propertyOwnerService->update($user, (int)$id, $data);

            return $this->respond([
                'status' => 'success',
                'data' => $result,
                'message' => 'Property owner updated successfully'
            ]);

        } catch (UnauthorizedException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (ForbiddenException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (NotFoundException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (ValidationException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage(), 'errors' => $e->getErrors()], 400);
        } catch (\InvalidArgumentException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            log_message('error', 'Error updating property owner: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Failed to update property owner'], 500);
        }
    }

    /**
     * Delete property owner
     * DELETE /api/property-owners/{id}
     */
    public function delete($id = null): ResponseInterface
    {
        try {
            if (!is_numeric($id)) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid property owner ID'], 400);
            }

            $user = $_SERVER['AUTH_USER'] ?? null;
            $this->propertyOwnerService->delete($user, (int)$id);

            return $this->respond([
                'status' => 'success',
                'message' => 'Property owner deleted successfully'
            ]);

        } catch (UnauthorizedException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (ForbiddenException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (NotFoundException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            log_message('error', 'Error deleting property owner: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Failed to delete property owner: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export property owners to Excel
     * GET /api/urban-renewals/{id}/property-owners/export
     */
    public function export($urbanRenewalId = null): ResponseInterface
    {
        try {
            if (!is_numeric($urbanRenewalId)) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid urban renewal ID'], 400);
            }

            $user = $_SERVER['AUTH_USER'] ?? null;
            
            // 使用 AuthorizationService 進行權限檢查
            $this->authService->assertCanAccessUrbanRenewal($user, (int)$urbanRenewalId);

            // 使用 Service 取得資料
            $propertyOwners = $this->propertyOwnerService->getByUrbanRenewal($user, (int)$urbanRenewalId);

            if (empty($propertyOwners)) {
                return $this->respond(['status' => 'error', 'message' => '無所有權人資料可匯出'], 404);
            }

            // Get urban renewal name for filename
            $urbanRenewalModel = model('UrbanRenewalModel');
            $urbanRenewal = $urbanRenewalModel->find($urbanRenewalId);
            $urbanRenewalName = $urbanRenewal['name'] ?? '所有權人';

            // Create Excel export
            $excel = new ExcelExportService();
            $excel->setDocumentProperties('都市更新會管理系統', '所有權人清單', '所有權人資料匯出', '所有權人資料匯出');

            $excel->addTableHeader([
                '所有權人編號', '所有權人名稱', '身分證字號', '電話1', '電話2',
                '聯絡地址', '戶籍地址', '排除類型', '備註'
            ]);

            $data = [];
            foreach ($propertyOwners as $owner) {
                $data[] = [
                    $owner['owner_code'] ?? '',
                    $owner['name'] ?? '',
                    $owner['id_number'] ?? '',
                    $owner['phone1'] ?? '',
                    $owner['phone2'] ?? '',
                    $owner['contact_address'] ?? '',
                    $owner['household_address'] ?? '',
                    $owner['exclusion_type'] ?? '',
                    $owner['notes'] ?? ''
                ];
            }

            $excel->addTableData($data, true, 9);
            $excel->autoSizeColumns('A', 'I');

            $filename = '所有權人_' . $urbanRenewalName . '_' . date('Ymd') . '.xlsx';
            $excel->download($filename);

        } catch (UnauthorizedException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (ForbiddenException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            log_message('error', 'Error exporting property owners: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => '匯出失敗：' . $e->getMessage()], 500);
        }
    }

    /**
     * Download Excel template for property owners import
     * GET /api/property-owners/template
     */
    public function downloadTemplate(): ResponseInterface
    {
        try {
            $excel = new ExcelExportService();
            $excel->setDocumentProperties('都市更新會管理系統', '所有權人匯入範本', '所有權人匯入範本', '所有權人資料匯入範本');

            $excel->addTableHeader([
                '所有權人編號', '所有權人名稱', '身分證字號', '電話1', '電話2',
                '聯絡地址', '戶籍地址', '排除類型', '備註'
            ]);

            $exampleData = [[
                'OW001', '王小明', 'A123456789', '0912345678', '02-12345678',
                '台北市信義區信義路一段123號', '台北市大安區和平東路二段456號', '', '範例資料（此列不會被匯入）'
            ]];
            $excel->addTableData($exampleData, true, 9);

            $sheet = $excel->getSheet();
            $sheet->getStyle('A2:I2')->getFont()->getColor()->setARGB('FFAAAAAA');
            $sheet->getStyle('A2:I2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F5F5');

            $excel->autoSizeColumns('A', 'I');
            $filename = '所有權人匯入範本_' . date('Ymd') . '.xlsx';
            $excel->download($filename);

        } catch (\Exception $e) {
            log_message('error', 'Error generating template: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => '範本產生失敗：' . $e->getMessage()], 500);
        }
    }

    /**
     * Import property owners from Excel
     * POST /api/urban-renewals/{id}/property-owners/import
     */
    public function import($urbanRenewalId = null): ResponseInterface
    {
        try {
            if (!is_numeric($urbanRenewalId)) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid urban renewal ID'], 400);
            }

            $user = $_SERVER['AUTH_USER'] ?? null;
            
            // 使用 AuthorizationService 進行權限檢查
            $this->authService->assertCanAccessUrbanRenewal($user, (int)$urbanRenewalId);

            // Check if file was uploaded
            $file = $this->request->getFile('file');
            if (!$file || !$file->isValid()) {
                return $this->respond(['status' => 'error', 'message' => '請選擇要匯入的Excel檔案'], 400);
            }

            // Validate file extension
            $extension = $file->getClientExtension();
            if (!in_array($extension, ['xlsx', 'xls'])) {
                return $this->respond(['status' => 'error', 'message' => '僅支援 .xlsx 或 .xls 格式的檔案'], 400);
            }

            // Load spreadsheet
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Remove header row
            array_shift($rows);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $seenOwnerCodes = [];
            $seenIdNumbers = [];

            // 取得該更新會目前最大的編號
            $maxCodeResult = $this->propertyOwnerModel
                ->where('urban_renewal_id', $urbanRenewalId)
                ->selectMax('owner_code')
                ->first();
            $nextAutoNumber = 1;
            if ($maxCodeResult && $maxCodeResult['owner_code']) {
                $nextAutoNumber = intval($maxCodeResult['owner_code']) + 1;
            }

            // Process each row
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Skip example row
                $ownerCode = !empty($row[0]) ? trim($row[0]) : '';
                $notes = !empty($row[8]) ? trim($row[8]) : '';
                if ($ownerCode === 'OW001' && (strpos($notes, '範例') !== false || strpos($notes, '此列不會被匯入') !== false)) {
                    continue;
                }

                // 如果沒有提供 owner_code，自動產生順序編號
                $finalOwnerCode = !empty($row[0]) ? trim($row[0]) : (string)$nextAutoNumber++;

                // Prepare data
                $data = [
                    'urban_renewal_id' => (int)$urbanRenewalId,
                    'owner_code' => $finalOwnerCode,
                    'name' => !empty($row[1]) ? trim($row[1]) : null,
                    'id_number' => !empty($row[2]) ? trim($row[2]) : null,
                    'phone1' => !empty($row[3]) ? trim($row[3]) : null,
                    'phone2' => !empty($row[4]) ? trim($row[4]) : null,
                    'contact_address' => !empty($row[5]) ? trim($row[5]) : null,
                    'household_address' => !empty($row[6]) ? trim($row[6]) : null,
                    'exclusion_type' => !empty($row[7]) ? trim($row[7]) : null,
                    'notes' => !empty($row[8]) ? trim($row[8]) : null,
                ];

                // Validate required fields
                if (empty($data['name'])) {
                    $errors[] = "第 {$rowNumber} 列：所有權人名稱為必填項目";
                    $errorCount++;
                    continue;
                }

                // Validate exclusion_type if provided
                if (!empty($data['exclusion_type'])) {
                    $validExclusionTypes = ['法院囑託查封', '假扣押', '假處分', '破產登記', '未經繼承'];
                    if (!in_array($data['exclusion_type'], $validExclusionTypes)) {
                        $errors[] = "第 {$rowNumber} 列：排除類型 '{$data['exclusion_type']}' 無效";
                        $errorCount++;
                        continue;
                    }
                }

                // Check for duplicates
                $shouldInsert = true;

                if (!empty($data['owner_code'])) {
                    if (in_array($data['owner_code'], $seenOwnerCodes)) {
                        $errors[] = "第 {$rowNumber} 列：所有權人編號 '{$data['owner_code']}' 在匯入檔案中重複";
                        $errorCount++;
                        continue;
                    }
                    $seenOwnerCodes[] = $data['owner_code'];

                    $existing = $this->propertyOwnerModel
                        ->where('owner_code', $data['owner_code'])
                        ->where('urban_renewal_id', $urbanRenewalId)
                        ->first();

                    if ($existing) {
                        $errors[] = "第 {$rowNumber} 列：所有權人編號 '{$data['owner_code']}' 已存在於資料庫";
                        $errorCount++;
                        $shouldInsert = false;
                        continue;
                    }
                }

                if ($shouldInsert && empty($data['owner_code']) && !empty($data['id_number'])) {
                    if (in_array($data['id_number'], $seenIdNumbers)) {
                        $errors[] = "第 {$rowNumber} 列：身分證字號 '{$data['id_number']}' 在匯入檔案中重複";
                        $errorCount++;
                        continue;
                    }
                    $seenIdNumbers[] = $data['id_number'];

                    $existing = $this->propertyOwnerModel
                        ->where('id_number', $data['id_number'])
                        ->where('urban_renewal_id', $urbanRenewalId)
                        ->first();

                    if ($existing) {
                        $errors[] = "第 {$rowNumber} 列：身分證字號 '{$data['id_number']}' 已存在於資料庫";
                        $errorCount++;
                        $shouldInsert = false;
                        continue;
                    }
                }

                if (!$shouldInsert) {
                    continue;
                }

                try {
                    $insertId = $this->propertyOwnerModel->insert($data);

                    if ($insertId === false) {
                        $validationErrors = $this->propertyOwnerModel->errors();
                        $errorMsg = !empty($validationErrors) ? implode(', ', $validationErrors) : '插入失敗';
                        $errors[] = "第 {$rowNumber} 列：{$errorMsg}";
                        $errorCount++;
                    } else {
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "第 {$rowNumber} 列：{$e->getMessage()}";
                    $errorCount++;
                }
            }

            return $this->respond([
                'status' => 'success',
                'message' => "匯入完成：成功 {$successCount} 筆，失敗 {$errorCount} 筆",
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ]);

        } catch (UnauthorizedException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 401);
        } catch (ForbiddenException $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            log_message('error', 'Error importing property owners: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => '匯入失敗：' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle OPTIONS request for CORS
     */
    public function options(): ResponseInterface
    {
        $response = $this->response;
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setStatusCode(200);

        return $response;
    }
}
