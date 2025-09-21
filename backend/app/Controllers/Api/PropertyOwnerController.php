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