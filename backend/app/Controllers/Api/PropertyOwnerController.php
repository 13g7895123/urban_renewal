<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use JsonException;
use App\Traits\HasRbacPermissions;
use App\Services\ExcelExportService;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PropertyOwnerController extends ResourceController
{
    use HasRbacPermissions;

    protected $propertyOwnerModel;
    protected $ownerBuildingModel;
    protected $ownerLandModel;
    protected $buildingModel;
    protected $landPlotModel;

    public function __construct()
    {
        $this->propertyOwnerModel = model('PropertyOwnerModel');
        $this->ownerBuildingModel = model('OwnerBuildingOwnershipModel');
        $this->ownerLandModel = model('OwnerLandOwnershipModel');
        $this->buildingModel = model('BuildingModel');
        $this->landPlotModel = model('LandPlotModel');
    }

    /**
     * Get property owners by urban renewal ID
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

            // 權限驗證：檢查用戶身份
            $user = $_SERVER['AUTH_USER'] ?? null;
            
            // Debug logging
            if (ENVIRONMENT !== 'production') {
                log_message('debug', 'PropertyOwnerController::getByUrbanRenewal - Request ID: ' . $urbanRenewalId);
                log_message('debug', 'PropertyOwnerController::getByUrbanRenewal - User: ' . json_encode($user));
            }
            
            if (!$user) {
                log_message('error', 'PropertyOwnerController::getByUrbanRenewal - No user found in $_SERVER[AUTH_USER]');
                return $this->respond([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ], 401);
            }

            // 系統管理員可以查看所有資料
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            // 非管理員需要驗證 urban_renewal_id
            if (!$isAdmin) {
                // 檢查是否為企業管理者
                $isCompanyManager = isset($user['is_company_manager']) && 
                                   ($user['is_company_manager'] === 1 || 
                                    $user['is_company_manager'] === '1' || 
                                    $user['is_company_manager'] === true);
                
                if (!$isCompanyManager) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您沒有管理所有權人的權限'
                    ], 403);
                }

                $userUrbanRenewalId = $user['urban_renewal_id'] ?? null;
                if (!$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您的帳號未關聯任何更新會'
                    ], 403);
                }

                // 檢查是否訪問自己企業的資料
                if ((int)$userUrbanRenewalId !== (int)$urbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您無權存取其他企業的資料'
                    ], 403);
                }
            }

            $propertyOwners = $this->propertyOwnerModel->getByUrbanRenewalId((int)$urbanRenewalId);

            return $this->respond([
                'status' => 'success',
                'data' => $propertyOwners,
                'message' => 'Property owners retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching property owners: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve property owners'
            ], 500);
        }
    }

    /**
     * Get all property owners
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
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve property owners'
            ], 500);
        }
    }

    /**
     * Get single property owner with details
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

            $propertyOwner = $this->propertyOwnerModel->getWithDetails((int)$id);

            if (!$propertyOwner) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Property owner not found'
                ], 404);
            }

            // 權限驗證：檢查用戶身份
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->respond([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ], 401);
            }

            // 系統管理員可以查看所有資料
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            // 非管理員需要驗證 urban_renewal_id
            if (!$isAdmin) {
                $isCompanyManager = isset($user['is_company_manager']) && 
                                   ($user['is_company_manager'] === 1 || 
                                    $user['is_company_manager'] === '1' || 
                                    $user['is_company_manager'] === true);
                
                if (!$isCompanyManager) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您沒有查看所有權人的權限'
                    ], 403);
                }

                $userUrbanRenewalId = $user['urban_renewal_id'] ?? null;
                if (!$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您的帳號未關聯任何更新會'
                    ], 403);
                }

                // 檢查所有權人是否屬於用戶的企業
                if ((int)$propertyOwner['urban_renewal_id'] !== (int)$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您無權存取其他企業的資料'
                    ], 403);
                }
            }

            return $this->respond([
                'status' => 'success',
                'data' => $propertyOwner,
                'message' => 'Property owner retrieved successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching property owner: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to retrieve property owner'
            ], 500);
        }
    }

    /**
     * Create new property owner
     */
    public function create(): ResponseInterface
    {
        try {
            // Ensure proper UTF-8 handling
            $this->response->setHeader('Content-Type', 'application/json; charset=utf-8');

            // Get raw JSON input and decode with proper UTF-8 handling
            $rawInput = $this->request->getBody();

            if (empty($rawInput)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'No data provided'
                ], 400);
            }

            try {
                $data = json_decode($rawInput, true, 512, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                log_message('error', 'JSON decode error: ' . $e->getMessage() . ' - Raw input: ' . $rawInput);
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Invalid JSON format: ' . $e->getMessage()
                ], 400);
            }

            if (!$data || !is_array($data)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Invalid data format'
                ], 400);
            }

            // Validate required fields
            if (empty($data['urban_renewal_id']) || empty($data['owner_name'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Missing required fields: urban_renewal_id, owner_name'
                ], 400);
            }

            // 權限驗證：檢查用戶身份
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->respond([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ], 401);
            }

            // 系統管理員可以操作所有資料
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            // 非管理員需要驗證 urban_renewal_id
            if (!$isAdmin) {
                $isCompanyManager = isset($user['is_company_manager']) && 
                                   ($user['is_company_manager'] === 1 || 
                                    $user['is_company_manager'] === '1' || 
                                    $user['is_company_manager'] === true);
                
                if (!$isCompanyManager) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您沒有新增所有權人的權限'
                    ], 403);
                }

                $userUrbanRenewalId = $user['urban_renewal_id'] ?? null;
                if (!$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您的帳號未關聯任何更新會'
                    ], 403);
                }

                // 檢查是否為自己企業新增所有權人
                if ((int)$data['urban_renewal_id'] !== (int)$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您只能為自己的企業新增所有權人'
                    ], 403);
                }
            }

            // Start transaction
            $db = \Config\Database::connect();
            $db->transStart();

            // Prepare property owner data
            $ownerData = [
                'urban_renewal_id' => $data['urban_renewal_id'],
                'name' => $data['owner_name'],
                'id_number' => $data['identity_number'] ?? null,
                'owner_code' => $data['owner_code'] ?? null,
                'phone1' => $data['phone1'] ?? null,
                'phone2' => $data['phone2'] ?? null,
                'contact_address' => $data['contact_address'] ?? null,
                'household_address' => $data['registered_address'] ?? null,
                'exclusion_type' => $data['exclusion_type'] ?? null,
                'notes' => $data['notes'] ?? null
            ];

            // Create property owner
            $ownerId = $this->propertyOwnerModel->insert($ownerData);

            if (!$ownerId) {
                $db->transRollback();
                $errors = $this->propertyOwnerModel->errors();
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to create property owner',
                    'errors' => $errors
                ], 400);
            }

            // Handle buildings
            if (!empty($data['buildings']) && is_array($data['buildings'])) {
                foreach ($data['buildings'] as $buildingIndex => $buildingData) {
                    // Create building if not exists
                    $buildingId = $this->buildingModel->createIfNotExists([
                        'urban_renewal_id' => $data['urban_renewal_id'],
                        'county' => $buildingData['county'],
                        'district' => $buildingData['district'],
                        'section' => $buildingData['section'],
                        'building_number_main' => $buildingData['building_number_main'],
                        'building_number_sub' => $buildingData['building_number_sub'],
                        'building_area' => $buildingData['building_area'] ?? null,
                        'building_address' => $buildingData['building_address'] ?? null
                    ]);

                    if ($buildingId) {
                        // Create ownership relationship
                        $this->ownerBuildingModel->createOrUpdate([
                            'property_owner_id' => $ownerId,
                            'building_id' => $buildingId,
                            'ownership_numerator' => $buildingData['ownership_numerator'],
                            'ownership_denominator' => $buildingData['ownership_denominator']
                        ]);
                    } else {
                        // Get validation errors from the model
                        $errors = $this->buildingModel->errors();
                        log_message('error', 'Failed to create building for data: ' . json_encode($buildingData, JSON_UNESCAPED_UNICODE));
                        log_message('error', 'Validation errors: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));

                        // Return error response with detailed validation errors
                        $errorMessage = '建物資料驗證失敗 (建物 #' . ($buildingIndex + 1) . ')';
                        if (!empty($errors)) {
                            $errorDetails = [];
                            foreach ($errors as $field => $error) {
                                $errorDetails[] = $error;
                            }
                            $errorMessage .= ': ' . implode('; ', $errorDetails);
                        }

                        return $this->fail($errorMessage, 400);
                    }
                }
            }

            // Handle lands
            if (!empty($data['lands']) && is_array($data['lands'])) {
                foreach ($data['lands'] as $landData) {
                    // Log land data for debugging
                    log_message('info', 'Processing land data: ' . json_encode($landData, JSON_UNESCAPED_UNICODE));

                    // Find land plot by plot number - try multiple search methods
                    $landPlot = null;

                    // Method 1: Search by land_number_main directly
                    if (!empty($landData['plot_number'])) {
                        $landPlot = $this->landPlotModel->where('land_number_main', $landData['plot_number'])
                                                       ->where('urban_renewal_id', $data['urban_renewal_id'])
                                                       ->first();
                    }

                    // Method 2: If not found, try searching by combined land number
                    if (!$landPlot && !empty($landData['plot_number'])) {
                        $landPlot = $this->landPlotModel->where("CONCAT(land_number_main, '-', land_number_sub)", $landData['plot_number'])
                                                       ->where('urban_renewal_id', $data['urban_renewal_id'])
                                                       ->first();
                    }

                    // Method 3: Search by landNumber field if it exists
                    if (!$landPlot && !empty($landData['plot_number'])) {
                        $landPlot = $this->landPlotModel->where('landNumber', $landData['plot_number'])
                                                       ->where('urban_renewal_id', $data['urban_renewal_id'])
                                                       ->first();
                    }

                    if ($landPlot) {
                        log_message('info', 'Found land plot: ' . json_encode($landPlot, JSON_UNESCAPED_UNICODE));

                        // Create ownership relationship
                        try {
                            $ownershipData = [
                                'property_owner_id' => (int)$ownerId,
                                'land_plot_id' => (int)$landPlot['id'],
                                'ownership_numerator' => (int)$landData['ownership_numerator'],
                                'ownership_denominator' => (int)$landData['ownership_denominator']
                            ];

                            log_message('info', 'Ownership data with type conversion: ' . json_encode($ownershipData, JSON_UNESCAPED_UNICODE));

                            $ownershipResult = $this->ownerLandModel->createOrUpdate($ownershipData);

                            if (!$ownershipResult) {
                                log_message('error', 'Failed to create land ownership relationship');
                                $errors = $this->ownerLandModel->errors();
                                if (!empty($errors)) {
                                    log_message('error', 'Land ownership validation errors: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
                                }
                            } else {
                                log_message('info', 'Successfully created land ownership relationship');
                            }
                        } catch (\Exception $e) {
                            log_message('error', 'Exception in land ownership creation: ' . $e->getMessage());
                            throw $e; // Re-throw to be caught by outer try-catch
                        }
                    } else {
                        log_message('warning', 'Land plot not found for plot_number: ' . $landData['plot_number']);
                    }
                }
            }

            // Update total areas
            // $this->propertyOwnerModel->updateTotalAreas($ownerId);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to create property owner'
                ], 500);
            }

            // Get the created property owner with details
            $createdOwner = $this->propertyOwnerModel->getWithDetails($ownerId);

            return $this->respond([
                'status' => 'success',
                'data' => $createdOwner,
                'message' => 'Property owner created successfully'
            ], 201);

        } catch (\Exception $e) {
            log_message('error', 'Error creating property owner: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to create property owner'
            ], 500);
        }
    }

    /**
     * Update property owner
     */
    public function update($id = null): ResponseInterface
    {
        try {
            if (!is_numeric($id)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Invalid property owner ID'
                ], 400);
            }

            $data = $this->request->getJSON(true);

            if (!$data) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'No data provided'
                ], 400);
            }

            // Check if property owner exists
            $existingOwner = $this->propertyOwnerModel->find($id);
            if (!$existingOwner) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Property owner not found'
                ], 404);
            }

            // 權限驗證：檢查用戶身份
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->respond([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ], 401);
            }

            // 系統管理員可以操作所有資料
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            // 非管理員需要驗證 urban_renewal_id
            if (!$isAdmin) {
                $isCompanyManager = isset($user['is_company_manager']) && 
                                   ($user['is_company_manager'] === 1 || 
                                    $user['is_company_manager'] === '1' || 
                                    $user['is_company_manager'] === true);
                
                if (!$isCompanyManager) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您沒有編輯所有權人的權限'
                    ], 403);
                }

                $userUrbanRenewalId = $user['urban_renewal_id'] ?? null;
                if (!$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您的帳號未關聯任何更新會'
                    ], 403);
                }

                // 檢查所有權人是否屬於用戶的企業
                if ((int)$existingOwner['urban_renewal_id'] !== (int)$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您只能編輯自己企業的所有權人'
                    ], 403);
                }
            }

            // Start transaction
            $db = \Config\Database::connect();
            $db->transStart();

            // Prepare property owner data
            $ownerData = [
                'name' => $data['owner_name'] ?? $existingOwner['name'],
                'id_number' => $data['identity_number'] ?? $existingOwner['id_number'],
                'phone1' => $data['phone1'] ?? $existingOwner['phone1'],
                'phone2' => $data['phone2'] ?? $existingOwner['phone2'],
                'contact_address' => $data['contact_address'] ?? $existingOwner['contact_address'],
                'household_address' => $data['registered_address'] ?? $existingOwner['household_address'],
                'exclusion_type' => $data['exclusion_type'] ?? $existingOwner['exclusion_type'],
                'notes' => $data['notes'] ?? $existingOwner['notes']
            ];

            // Update property owner
            $updated = $this->propertyOwnerModel->update($id, $ownerData);

            if (!$updated) {
                $db->transRollback();
                $errors = $this->propertyOwnerModel->errors();
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to update property owner',
                    'errors' => $errors
                ], 400);
            }

            // Handle buildings update - remove existing and add new
            if (isset($data['buildings'])) {
                // Remove existing building ownerships
                $existingBuildingOwnerships = $this->ownerBuildingModel->getByPropertyOwnerId($id);
                foreach ($existingBuildingOwnerships as $ownership) {
                    $this->ownerBuildingModel->delete($ownership['id']);
                }

                // Add new buildings
                if (!empty($data['buildings']) && is_array($data['buildings'])) {
                    foreach ($data['buildings'] as $buildingIndex => $buildingData) {
                        log_message('info', 'Processing building data: ' . json_encode($buildingData, JSON_UNESCAPED_UNICODE));

                        // Create building if not exists
                        $buildingId = $this->buildingModel->createIfNotExists([
                            'urban_renewal_id' => $existingOwner['urban_renewal_id'],
                            'county' => $buildingData['county'],
                            'district' => $buildingData['district'],
                            'section' => $buildingData['section'],
                            'building_number_main' => $buildingData['building_number_main'],
                            'building_number_sub' => $buildingData['building_number_sub'] ?? '',
                            'building_area' => $buildingData['building_area'] ?? null,
                            'building_address' => $buildingData['building_address'] ?? null
                        ]);

                        if ($buildingId) {
                            log_message('info', 'Building created/found with ID: ' . $buildingId);

                            // Create ownership relationship
                            $ownershipResult = $this->ownerBuildingModel->createOrUpdate([
                                'property_owner_id' => $id,
                                'building_id' => $buildingId,
                                'ownership_numerator' => $buildingData['ownership_numerator'],
                                'ownership_denominator' => $buildingData['ownership_denominator']
                            ]);

                            if (!$ownershipResult) {
                                log_message('error', 'Failed to create building ownership relationship');
                            }
                        } else {
                            // Get validation errors from the model
                            $errors = $this->buildingModel->errors();
                            log_message('error', 'Failed to create building for data: ' . json_encode($buildingData, JSON_UNESCAPED_UNICODE));
                            log_message('error', 'Validation errors: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));

                            // Return error response with detailed validation errors
                            $errorMessage = '建物資料驗證失敗 (建物 #' . ($buildingIndex + 1) . ')';
                            if (!empty($errors)) {
                                $errorDetails = [];
                                foreach ($errors as $field => $error) {
                                    $errorDetails[] = $error;
                                }
                                $errorMessage .= ': ' . implode('; ', $errorDetails);
                            }

                            return $this->fail($errorMessage, 400);
                        }
                    }
                }
            }

            // Handle lands update - remove existing and add new
            if (isset($data['lands'])) {
                // Remove existing land ownerships
                $existingLandOwnerships = $this->ownerLandModel->getByPropertyOwnerId($id);
                foreach ($existingLandOwnerships as $ownership) {
                    $this->ownerLandModel->delete($ownership['id']);
                }

                // Add new lands
                if (!empty($data['lands']) && is_array($data['lands'])) {
                    foreach ($data['lands'] as $landData) {
                        log_message('info', 'Processing land data: ' . json_encode($landData, JSON_UNESCAPED_UNICODE));

                        // Find land plot by plot number - try multiple search methods
                        $landPlot = null;

                        // Method 1: Search by land_number_main directly
                        if (!empty($landData['plot_number'])) {
                            $landPlot = $this->landPlotModel->where('land_number_main', $landData['plot_number'])
                                                           ->where('urban_renewal_id', $existingOwner['urban_renewal_id'])
                                                           ->first();
                        }

                        // Method 2: If not found, try searching by combined land number (e.g., "123-4")
                        if (!$landPlot && !empty($landData['plot_number']) && strpos($landData['plot_number'], '-') !== false) {
                            $parts = explode('-', $landData['plot_number']);
                            $landNumberMain = $parts[0];
                            $landNumberSub = $parts[1] ?? '';

                            $query = $this->landPlotModel->where('land_number_main', $landNumberMain)
                                                        ->where('urban_renewal_id', $existingOwner['urban_renewal_id']);

                            if (!empty($landNumberSub)) {
                                $query->where('land_number_sub', $landNumberSub);
                            }

                            $landPlot = $query->first();
                        }

                        // Method 3: Try CONCAT search as fallback
                        if (!$landPlot && !empty($landData['plot_number'])) {
                            $landPlot = $this->landPlotModel->where("CONCAT(land_number_main, '-', land_number_sub)", $landData['plot_number'])
                                                           ->where('urban_renewal_id', $existingOwner['urban_renewal_id'])
                                                           ->first();
                        }

                        if ($landPlot) {
                            log_message('info', 'Land plot found with ID: ' . $landPlot['id']);

                            // Create ownership relationship
                            $ownershipResult = $this->ownerLandModel->createOrUpdate([
                                'property_owner_id' => $id,
                                'land_plot_id' => $landPlot['id'],
                                'ownership_numerator' => $landData['ownership_numerator'],
                                'ownership_denominator' => $landData['ownership_denominator']
                            ]);

                            if (!$ownershipResult) {
                                log_message('error', 'Failed to create land ownership relationship');
                            }
                        } else {
                            log_message('warning', 'Land plot not found for plot_number: ' . $landData['plot_number']);
                        }
                    }
                }
            }

            // Update total areas
            // $this->propertyOwnerModel->updateTotalAreas($id);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to update property owner'
                ], 500);
            }

            // Get the updated property owner with details
            $updatedOwner = $this->propertyOwnerModel->getWithDetails($id);

            return $this->respond([
                'status' => 'success',
                'data' => $updatedOwner,
                'message' => 'Property owner updated successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error updating property owner: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to update property owner'
            ], 500);
        }
    }

    /**
     * Delete property owner
     */
    public function delete($id = null): ResponseInterface
    {
        try {
            if (!is_numeric($id)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Invalid property owner ID'
                ], 400);
            }

            // Check if property owner exists
            $existingOwner = $this->propertyOwnerModel->find($id);
            if (!$existingOwner) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Property owner not found'
                ], 404);
            }

            // 權限驗證：檢查用戶身份
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->respond([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ], 401);
            }

            // 系統管理員可以操作所有資料
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            // 非管理員需要驗證 urban_renewal_id
            if (!$isAdmin) {
                $isCompanyManager = isset($user['is_company_manager']) && 
                                   ($user['is_company_manager'] === 1 || 
                                    $user['is_company_manager'] === '1' || 
                                    $user['is_company_manager'] === true);
                
                if (!$isCompanyManager) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您沒有刪除所有權人的權限'
                    ], 403);
                }

                $userUrbanRenewalId = $user['urban_renewal_id'] ?? null;
                if (!$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您的帳號未關聯任何更新會'
                    ], 403);
                }

                // 檢查所有權人是否屬於用戶的企業
                if ((int)$existingOwner['urban_renewal_id'] !== (int)$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您只能刪除自己企業的所有權人'
                    ], 403);
                }
            }

            // Start transaction
            $db = \Config\Database::connect();
            $db->transStart();

            log_message('info', "Deleting property owner ID: $id");

            // Delete ownership relationships
            $buildingOwnerships = $this->ownerBuildingModel->getByPropertyOwnerId($id);
            log_message('info', "Found " . count($buildingOwnerships) . " building ownerships to delete");
            foreach ($buildingOwnerships as $ownership) {
                $result = $this->ownerBuildingModel->delete($ownership['id']);
                log_message('info', "Deleted building ownership {$ownership['id']}: " . ($result ? 'success' : 'failed'));
            }

            $landOwnerships = $this->ownerLandModel->getByPropertyOwnerId($id);
            log_message('info', "Found " . count($landOwnerships) . " land ownerships to delete");
            foreach ($landOwnerships as $ownership) {
                $result = $this->ownerLandModel->delete($ownership['id']);
                log_message('info', "Deleted land ownership {$ownership['id']}: " . ($result ? 'success' : 'failed'));
            }

            // Delete property owner
            $deleted = $this->propertyOwnerModel->delete($id);
            log_message('info', "Delete property owner result: " . var_export($deleted, true));

            $db->transComplete();

            $transStatus = $db->transStatus();
            log_message('info', "Transaction status: " . ($transStatus === false ? 'FAILED' : 'SUCCESS'));

            if ($transStatus === false) {
                log_message('error', "Transaction failed when deleting property owner ID: $id");
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to delete property owner: transaction failed'
                ], 500);
            }

            if ($deleted === false) {
                log_message('error', "Model delete returned false for property owner ID: $id");
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to delete property owner: delete returned false'
                ], 500);
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Property owner deleted successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error deleting property owner: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to delete property owner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export property owners to Excel
     */
    public function export($urbanRenewalId = null): ResponseInterface
    {
        try {
            if (!is_numeric($urbanRenewalId)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Invalid urban renewal ID'
                ], 400);
            }

            // 權限驗證：檢查用戶身份
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->respond([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ], 401);
            }

            // 系統管理員可以匯出所有資料
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            // 非管理員需要驗證 urban_renewal_id
            if (!$isAdmin) {
                $isCompanyManager = isset($user['is_company_manager']) && 
                                   ($user['is_company_manager'] === 1 || 
                                    $user['is_company_manager'] === '1' || 
                                    $user['is_company_manager'] === true);
                
                if (!$isCompanyManager) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您沒有匯出所有權人的權限'
                    ], 403);
                }

                $userUrbanRenewalId = $user['urban_renewal_id'] ?? null;
                if (!$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您的帳號未關聯任何更新會'
                    ], 403);
                }

                // 檢查是否匯出自己企業的資料
                if ((int)$urbanRenewalId !== (int)$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您只能匯出自己企業的所有權人資料'
                    ], 403);
                }
            }

            // Get property owners
            $propertyOwners = $this->propertyOwnerModel->getByUrbanRenewalId((int)$urbanRenewalId);

            if (empty($propertyOwners)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => '無所有權人資料可匯出'
                ], 404);
            }

            // Get urban renewal name for filename
            $urbanRenewalModel = model('UrbanRenewalModel');
            $urbanRenewal = $urbanRenewalModel->find($urbanRenewalId);
            $urbanRenewalName = $urbanRenewal['name'] ?? '所有權人';

            // Create Excel export
            $excel = new ExcelExportService();

            // Set document properties
            $excel->setDocumentProperties(
                '都市更新會管理系統',
                '所有權人清單',
                '所有權人資料匯出',
                '所有權人資料匯出'
            );

            // Add table header
            $excel->addTableHeader([
                '所有權人編號',
                '所有權人名稱',
                '身分證字號',
                '電話1',
                '電話2',
                '聯絡地址',
                '戶籍地址',
                '排除類型',
                '備註'
            ]);

            // Prepare data
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

            // Add data
            $excel->addTableData($data, true, 9);

            // Auto-size columns
            $excel->autoSizeColumns('A', 'I');

            // Generate filename with date
            $filename = '所有權人_' . $urbanRenewalName . '_' . date('Ymd') . '.xlsx';

            // Download the file
            $excel->download($filename);

        } catch (\Exception $e) {
            log_message('error', 'Error exporting property owners: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => '匯出失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Excel template for property owners import
     */
    public function downloadTemplate(): ResponseInterface
    {
        try {
            // Create Excel export
            $excel = new ExcelExportService();

            // Set document properties
            $excel->setDocumentProperties(
                '都市更新會管理系統',
                '所有權人匯入範本',
                '所有權人匯入範本',
                '所有權人資料匯入範本'
            );

            // Add table header
            $excel->addTableHeader([
                '所有權人編號',
                '所有權人名稱',
                '身分證字號',
                '電話1',
                '電話2',
                '聯絡地址',
                '戶籍地址',
                '排除類型',
                '備註'
            ]);

            // Add example row (will be skipped during import)
            $exampleData = [
                [
                    'OW001',
                    '王小明',
                    'A123456789',
                    '0912345678',
                    '02-12345678',
                    '台北市信義區信義路一段123號',
                    '台北市大安區和平東路二段456號',
                    '',
                    '範例資料（此列不會被匯入）'
                ]
            ];
            $excel->addTableData($exampleData, true, 9);

            // Style the example row with lighter text
            $sheet = $excel->getSheet();
            $sheet->getStyle('A2:I2')->getFont()->getColor()->setARGB('FFAAAAAA');
            $sheet->getStyle('A2:I2')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF5F5F5');

            // Auto-size columns
            $excel->autoSizeColumns('A', 'I');

            // Generate filename
            $filename = '所有權人匯入範本_' . date('Ymd') . '.xlsx';

            // Download the file
            $excel->download($filename);

        } catch (\Exception $e) {
            log_message('error', 'Error generating template: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => '範本產生失敗：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import property owners from Excel
     */
    public function import($urbanRenewalId = null): ResponseInterface
    {
        try {
            if (!is_numeric($urbanRenewalId)) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Invalid urban renewal ID'
                ], 400);
            }

            // 權限驗證：檢查用戶身份
            $user = $_SERVER['AUTH_USER'] ?? null;
            if (!$user) {
                return $this->respond([
                    'status' => 'error',
                    'message' => '未授權：無法識別用戶身份'
                ], 401);
            }

            // 系統管理員可以匯入所有資料
            $isAdmin = isset($user['role']) && $user['role'] === 'admin';
            
            // 非管理員需要驗證 urban_renewal_id
            if (!$isAdmin) {
                $isCompanyManager = isset($user['is_company_manager']) && 
                                   ($user['is_company_manager'] === 1 || 
                                    $user['is_company_manager'] === '1' || 
                                    $user['is_company_manager'] === true);
                
                if (!$isCompanyManager) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您沒有匯入所有權人的權限'
                    ], 403);
                }

                $userUrbanRenewalId = $user['urban_renewal_id'] ?? null;
                if (!$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您的帳號未關聯任何更新會'
                    ], 403);
                }

                // 檢查是否匯入自己企業的資料
                if ((int)$urbanRenewalId !== (int)$userUrbanRenewalId) {
                    return $this->respond([
                        'status' => 'error',
                        'message' => '權限不足：您只能為自己的企業匯入所有權人資料'
                    ], 403);
                }
            }

            // Check if file was uploaded
            $file = $this->request->getFile('file');
            if (!$file || !$file->isValid()) {
                return $this->respond([
                    'status' => 'error',
                    'message' => '請選擇要匯入的Excel檔案'
                ], 400);
            }

            // Validate file extension
            $extension = $file->getClientExtension();
            if (!in_array($extension, ['xlsx', 'xls'])) {
                return $this->respond([
                    'status' => 'error',
                    'message' => '僅支援 .xlsx 或 .xls 格式的檔案'
                ], 400);
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
            $seenOwnerCodes = []; // Track owner codes in this import
            $seenIdNumbers = [];  // Track ID numbers in this import

            // Process each row
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because index starts at 0 and we removed header

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Skip example row (owner_code = OW001 AND notes contains "範例" or "此列不會被匯入")
                $ownerCode = !empty($row[0]) ? trim($row[0]) : '';
                $notes = !empty($row[8]) ? trim($row[8]) : '';
                if ($ownerCode === 'OW001' && (strpos($notes, '範例') !== false || strpos($notes, '此列不會被匯入') !== false)) {
                    continue;
                }

                // Prepare data
                $data = [
                    'urban_renewal_id' => (int)$urbanRenewalId,
                    'owner_code' => !empty($row[0]) ? trim($row[0]) : null,
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
                        $errors[] = "第 {$rowNumber} 列：排除類型 '{$data['exclusion_type']}' 無效，必須是以下其中之一：" . implode('、', $validExclusionTypes);
                        $errorCount++;
                        continue;
                    }
                }

                // Check for duplicates within the import file
                $shouldInsert = true; // Flag to determine if we should insert new record

                if (!empty($data['owner_code'])) {
                    if (in_array($data['owner_code'], $seenOwnerCodes)) {
                        $errors[] = "第 {$rowNumber} 列：所有權人編號 '{$data['owner_code']}' 在匯入檔案中重複";
                        $errorCount++;
                        continue;
                    }
                    $seenOwnerCodes[] = $data['owner_code'];

                    // Check if owner_code already exists in database
                    $existing = $this->propertyOwnerModel
                        ->where('owner_code', $data['owner_code'])
                        ->where('urban_renewal_id', $urbanRenewalId)
                        ->first();

                    if ($existing) {
                        // Record exists - skip and report error
                        $errors[] = "第 {$rowNumber} 列：所有權人編號 '{$data['owner_code']}' 已存在於資料庫";
                        $errorCount++;
                        $shouldInsert = false;
                        continue;
                    }
                }

                // Only check ID number if owner_code is empty (id_number is secondary check)
                if ($shouldInsert && empty($data['owner_code']) && !empty($data['id_number'])) {
                    if (in_array($data['id_number'], $seenIdNumbers)) {
                        $errors[] = "第 {$rowNumber} 列：身分證字號 '{$data['id_number']}' 在匯入檔案中重複";
                        $errorCount++;
                        continue;
                    }
                    $seenIdNumbers[] = $data['id_number'];

                    // Check if ID number already exists in database for this urban renewal
                    $existing = $this->propertyOwnerModel
                        ->where('id_number', $data['id_number'])
                        ->where('urban_renewal_id', $urbanRenewalId)
                        ->first();

                    if ($existing) {
                        // Record exists - skip and report error
                        $errors[] = "第 {$rowNumber} 列：身分證字號 '{$data['id_number']}' 已存在於資料庫";
                        $errorCount++;
                        $shouldInsert = false;
                        continue;
                    }
                }

                // Only insert if shouldInsert flag is true
                if (!$shouldInsert) {
                    continue;
                }

                try {
                    // Insert data
                    $insertId = $this->propertyOwnerModel->insert($data);

                    if ($insertId === false) {
                        // Insert failed, get validation errors
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

        } catch (\Exception $e) {
            log_message('error', 'Error importing property owners: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => '匯入失敗：' . $e->getMessage()
            ], 500);
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