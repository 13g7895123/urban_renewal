<?php

namespace App\Models;

use CodeIgniter\Model;

class UrbanRenewalModel extends Model
{
    protected $table = 'urban_renewals';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'company_id',
        'name',
        'chairman_name',
        'chairman_phone',
        'address',
        'representative',
        'assigned_admin_id',
        'phone',
        'fax',
        'email',
        'contact_person',
        'notes'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation rules - 只有 name 是必填的
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[255]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => '更新會名稱為必填項目',
            'min_length' => '更新會名稱至少需要2個字元',
            'max_length' => '更新會名稱不能超過255個字元'
        ],
    ];

    protected $skipValidation = false;

    /**
     * Get all urban renewals with pagination
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param int|null $companyId Filter by company_id (for company managers)
     * @deprecated Use getUrbanRenewals() with companyId parameter
     */
    public function getUrbanRenewals($page = 1, $perPage = 10, $urbanRenewalId = null)
    {
        $builder = $this->orderBy('created_at', 'DESC');

        // Support both old parameter name (for backward compatibility) and new usage
        if ($urbanRenewalId !== null) {
            // If this looks like it's being used as company_id, use it that way
            $builder->where('company_id', $urbanRenewalId);
        }

        return $builder->paginate($perPage, 'default', $page);
    }

    /**
     * Get urban renewal by ID
     */
    public function getUrbanRenewal($id)
    {
        return $this->find($id);
    }

    /**
     * Create new urban renewal
     */
    public function createUrbanRenewal($data)
    {
        return $this->insert($data);
    }

    /**
     * Update urban renewal
     */
    public function updateUrbanRenewal($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * Delete urban renewal (soft delete)
     */
    public function deleteUrbanRenewal($id)
    {
        return $this->delete($id);
    }

    /**
     * Search urban renewals by name
     * @param string $name Search keyword
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param int|null $companyId Filter by company_id (for company managers)
     */
    public function searchByName($name, $page = 1, $perPage = 10, $urbanRenewalId = null)
    {
        $builder = $this->like('name', $name)
            ->orderBy('created_at', 'DESC');

        // Support both old parameter name (for backward compatibility)
        if ($urbanRenewalId !== null) {
            $builder->where('company_id', $urbanRenewalId);
        }

        return $builder->paginate($perPage, 'default', $page);
    }

    /**
     * Get the associated company for this urban renewal
     * @param int $urbanRenewalId
     * @return array|null
     */
    public function getCompany($urbanRenewalId)
    {
        $urbanRenewal = $this->find($urbanRenewalId);
        if (!$urbanRenewal || !$urbanRenewal['company_id']) {
            return null;
        }

        $companyModel = new \App\Models\CompanyModel();
        return $companyModel->find($urbanRenewal['company_id']);
    }

    /**
     * Batch assign admin to multiple urban renewals
     * @param array $assignments Array of ['urban_renewal_id' => admin_id]
     * @return bool
     */
    public function batchAssignAdmin($assignments)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($assignments as $urbanRenewalId => $adminId) {
                $this->update($urbanRenewalId, [
                    'assigned_admin_id' => $adminId === '' || $adminId === null ? null : $adminId
                ]);
            }

            $db->transComplete();
            return $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Batch assign admin failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get urban renewals with assigned admin info
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param int|null $companyId Filter by company_id
     * @return array
     */
    public function getUrbanRenewalsWithAdmin($page = 1, $perPage = 10, $urbanRenewalId = null)
    {
        $builder = $this->select('urban_renewals.*, users.full_name as assigned_admin_name, users.email as assigned_admin_email')
            ->join('users', 'users.id = urban_renewals.assigned_admin_id', 'left')
            ->orderBy('urban_renewals.created_at', 'DESC');

        // Support both old parameter name (for backward compatibility)
        if ($urbanRenewalId !== null) {
            $builder->where('urban_renewals.company_id', $urbanRenewalId);
        }

        return $builder->paginate($perPage, 'default', $page);
    }

    /**
     * Calculate member count for an urban renewal
     * @param int $urbanRenewalId
     * @return int
     */
    public function calculateMemberCount(int $urbanRenewalId): int
    {
        $propertyOwnerModel = new \App\Models\PropertyOwnerModel();
        return $propertyOwnerModel->where('urban_renewal_id', $urbanRenewalId)->countAllResults();
    }

    /**
     * Calculate assigned member count (工作人員指派數量)
     * @param int $urbanRenewalId
     * @return int
     */
    public function calculateAssignedMemberCount(int $urbanRenewalId): int
    {
        $assignmentModel = new \App\Models\UserRenewalAssignmentModel();
        return $assignmentModel->where('urban_renewal_id', $urbanRenewalId)->countAllResults();
    }

    /**
     * Get urban renewal with calculated member count
     * @param int $id
     * @return array|null
     */
    public function getWithMemberCount(int $id): ?array
    {
        $urbanRenewal = $this->find($id);
        if (!$urbanRenewal) {
            return null;
        }

        $urbanRenewal['member_count'] = $this->calculateMemberCount($id);
        $urbanRenewal['area'] = $this->calculateTotalLandArea($id);
        return $urbanRenewal;
    }

    /**
     * Get all urban renewals with calculated member counts
     * @param int $page
     * @param int $perPage
     * @param int|null $companyId
     * @return array
     */
    public function getUrbanRenewalsWithMemberCount($page = 1, $perPage = 10, $urbanRenewalId = null)
    {
        $urbanRenewals = $this->getUrbanRenewals($page, $perPage, $urbanRenewalId);

        // Add calculated member count and land area to each record
        foreach ($urbanRenewals as &$renewal) {
            $renewal['member_count'] = $this->calculateMemberCount($renewal['id']);
            $renewal['area'] = $this->calculateTotalLandArea($renewal['id']);
        }
        unset($renewal);

        return $urbanRenewals;
    }

    /**
     * Calculate total land area for an urban renewal
     * First tries to sum up all property owners' land area
     * If empty, falls back to summing land_plots table
     * 
     * @param int $urbanRenewalId
     * @return float
     */
    public function calculateTotalLandArea(int $urbanRenewalId): float
    {
        $propertyOwnerModel = new \App\Models\PropertyOwnerModel();

        // Get all property owners for this urban renewal
        $propertyOwners = $propertyOwnerModel
            ->where('urban_renewal_id', $urbanRenewalId)
            ->findAll();

        $totalLandArea = 0;

        // For each property owner, sum their land areas
        foreach ($propertyOwners as $owner) {
            $ownerTotals = $propertyOwnerModel->calculateTotalAreas($owner['id']);
            $totalLandArea += $ownerTotals['total_land_area'] ?? 0;
        }

        // If total is 0, fallback to land_plots table
        if ($totalLandArea == 0) {
            $totalLandArea = $this->calculateTotalLandAreaFromLandPlots($urbanRenewalId);
        }

        return round($totalLandArea, 2);
    }

    /**
     * Calculate total land area from land_plots table
     * Used as fallback when property owners data is empty
     * 
     * @param int $urbanRenewalId
     * @return float
     */
    public function calculateTotalLandAreaFromLandPlots(int $urbanRenewalId): float
    {
        $landPlotModel = new \App\Models\LandPlotModel();

        $result = $landPlotModel
            ->selectSum('land_area', 'total')
            ->where('urban_renewal_id', $urbanRenewalId)
            ->first();

        return round((float)($result['total'] ?? 0), 2);
    }

    /**
     * Get urban renewal with calculated total land area
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithCalculatedArea(int $id): ?array
    {
        $urbanRenewal = $this->find($id);
        if (!$urbanRenewal) {
            return null;
        }

        $urbanRenewal['area'] = $this->calculateTotalLandArea($id);
        return $urbanRenewal;
    }

    /**
     * Get all urban renewals with calculated total land area
     * 
     * @param int $page
     * @param int $perPage
     * @param int|null $companyId
     * @return array
     */
    public function getUrbanRenewalsWithCalculatedArea($page = 1, $perPage = 10, $urbanRenewalId = null)
    {
        $urbanRenewals = $this->getUrbanRenewals($page, $perPage, $urbanRenewalId);

        // Add calculated land area to each record
        foreach ($urbanRenewals as &$renewal) {
            $renewal['area'] = $this->calculateTotalLandArea($renewal['id']);
        }
        unset($renewal);

        return $urbanRenewals;
    }
}
