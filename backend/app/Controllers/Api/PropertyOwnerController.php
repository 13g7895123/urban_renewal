<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use JsonException;

class PropertyOwnerController extends ResourceController
{
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
                foreach ($data['buildings'] as $buildingData) {
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
                    foreach ($data['buildings'] as $buildingData) {
                        // Create building if not exists
                        $buildingId = $this->buildingModel->createIfNotExists([
                            'urban_renewal_id' => $existingOwner['urban_renewal_id'],
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
                                'property_owner_id' => $id,
                                'building_id' => $buildingId,
                                'ownership_numerator' => $buildingData['ownership_numerator'],
                                'ownership_denominator' => $buildingData['ownership_denominator']
                            ]);
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
                        // Find land plot by plot number
                        $landPlot = $this->landPlotModel->where('land_number_main', $landData['plot_number'])
                                                       ->where('urban_renewal_id', $existingOwner['urban_renewal_id'])
                                                       ->first();

                        if ($landPlot) {
                            // Create ownership relationship
                            $this->ownerLandModel->createOrUpdate([
                                'property_owner_id' => $id,
                                'land_plot_id' => $landPlot['id'],
                                'ownership_numerator' => $landData['ownership_numerator'],
                                'ownership_denominator' => $landData['ownership_denominator']
                            ]);
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

            // Start transaction
            $db = \Config\Database::connect();
            $db->transStart();

            // Delete ownership relationships
            $buildingOwnerships = $this->ownerBuildingModel->getByPropertyOwnerId($id);
            foreach ($buildingOwnerships as $ownership) {
                $this->ownerBuildingModel->delete($ownership['id']);
            }

            $landOwnerships = $this->ownerLandModel->getByPropertyOwnerId($id);
            foreach ($landOwnerships as $ownership) {
                $this->ownerLandModel->delete($ownership['id']);
            }

            // Delete property owner
            $deleted = $this->propertyOwnerModel->delete($id);

            $db->transComplete();

            if ($db->transStatus() === false || !$deleted) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'Failed to delete property owner'
                ], 500);
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Property owner deleted successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error deleting property owner: ' . $e->getMessage());
            return $this->respond([
                'status' => 'error',
                'message' => 'Failed to delete property owner'
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

            // Create new spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = [
                'A1' => '所有權人編號',
                'B1' => '所有權人名稱',
                'C1' => '身分證字號',
                'D1' => '電話1',
                'E1' => '電話2',
                'F1' => '聯絡地址',
                'G1' => '戶籍地址',
                'H1' => '排除類型',
                'I1' => '備註'
            ];

            foreach ($headers as $cell => $header) {
                $sheet->setCellValue($cell, $header);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
            }

            // Auto-size columns
            foreach (range('A', 'I') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Fill data
            $row = 2;
            foreach ($propertyOwners as $owner) {
                $sheet->setCellValue('A' . $row, $owner['owner_code'] ?? '');
                $sheet->setCellValue('B' . $row, $owner['name'] ?? '');
                $sheet->setCellValue('C' . $row, $owner['id_number'] ?? '');
                $sheet->setCellValue('D' . $row, $owner['phone1'] ?? '');
                $sheet->setCellValue('E' . $row, $owner['phone2'] ?? '');
                $sheet->setCellValue('F' . $row, $owner['contact_address'] ?? '');
                $sheet->setCellValue('G' . $row, $owner['household_address'] ?? '');
                $sheet->setCellValue('H' . $row, $owner['exclusion_type'] ?? '');
                $sheet->setCellValue('I' . $row, $owner['notes'] ?? '');
                $row++;
            }

            // Generate filename with date
            $filename = '所有權人_' . $urbanRenewalName . '_' . date('Ymd') . '.xlsx';

            // Create writer
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Expose-Headers: Content-Disposition');

            // Output file
            $writer->save('php://output');
            exit;

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
            // Create new spreadsheet
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = [
                'A1' => '所有權人編號',
                'B1' => '所有權人名稱',
                'C1' => '身分證字號',
                'D1' => '電話1',
                'E1' => '電話2',
                'F1' => '聯絡地址',
                'G1' => '戶籍地址',
                'H1' => '排除類型',
                'I1' => '備註'
            ];

            foreach ($headers as $cell => $header) {
                $sheet->setCellValue($cell, $header);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
            }

            // Add example row (will be skipped during import)
            $sheet->setCellValue('A2', 'OW001');
            $sheet->setCellValue('B2', '王小明');
            $sheet->setCellValue('C2', 'A123456789');
            $sheet->setCellValue('D2', '0912345678');
            $sheet->setCellValue('E2', '02-12345678');
            $sheet->setCellValue('F2', '台北市信義區信義路一段123號');
            $sheet->setCellValue('G2', '台北市大安區和平東路二段456號');
            $sheet->setCellValue('H2', '');
            $sheet->setCellValue('I2', '範例資料（此列不會被匯入）');

            // Style example row as lighter text with gray background
            $sheet->getStyle('A2:I2')->getFont()->getColor()->setARGB('FFAAAAAA');
            $sheet->getStyle('A2:I2')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF5F5F5');

            // Auto-size columns
            foreach (range('A', 'I') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Generate filename
            $filename = '所有權人匯入範本_' . date('Ymd') . '.xlsx';

            // Create writer
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

            // Set headers for download
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Expose-Headers: Content-Disposition');

            // Output file
            $writer->save('php://output');
            exit;

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

                // Check for duplicates within the import file
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
                        $errors[] = "第 {$rowNumber} 列：所有權人編號 '{$data['owner_code']}' 已存在於資料庫";
                        $errorCount++;
                        continue;
                    }
                }

                // Check for duplicate ID numbers within the import file
                if (!empty($data['id_number'])) {
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
                        $errors[] = "第 {$rowNumber} 列：身分證字號 '{$data['id_number']}' 已存在於資料庫";
                        $errorCount++;
                        continue;
                    }
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