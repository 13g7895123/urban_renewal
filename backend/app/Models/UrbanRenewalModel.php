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
        'name',
        'chairman_name',
        'chairman_phone',
        'address',
        'representative',
        'assigned_admin_id'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[255]',
        'chairman_name' => 'required|min_length[2]|max_length[100]',
        'chairman_phone' => 'required|min_length[8]|max_length[20]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => '更新會名稱為必填項目',
            'min_length' => '更新會名稱至少需要2個字元',
            'max_length' => '更新會名稱不能超過255個字元'
        ],
        'chairman_name' => [
            'required' => '理事長姓名為必填項目',
            'min_length' => '理事長姓名至少需要2個字元',
            'max_length' => '理事長姓名不能超過100個字元'
        ],
        'chairman_phone' => [
            'required' => '理事長電話為必填項目',
            'min_length' => '理事長電話至少需要8個字元',
            'max_length' => '理事長電話不能超過20個字元'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Get all urban renewals with pagination
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param int|null $urbanRenewalId Filter by urban_renewal_id (for company managers)
     */
    public function getUrbanRenewals($page = 1, $perPage = 10, $urbanRenewalId = null)
    {
        $builder = $this->orderBy('created_at', 'DESC');
        
        // 如果指定了 urban_renewal_id，只查詢該企業的資料
        if ($urbanRenewalId !== null) {
            $builder->where('id', $urbanRenewalId);
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
     * @param int|null $urbanRenewalId Filter by urban_renewal_id (for company managers)
     */
    public function searchByName($name, $page = 1, $perPage = 10, $urbanRenewalId = null)
    {
        $builder = $this->like('name', $name)
                        ->orderBy('created_at', 'DESC');
        
        // 如果指定了 urban_renewal_id，只查詢該企業的資料
        if ($urbanRenewalId !== null) {
            $builder->where('id', $urbanRenewalId);
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
        $companyModel = new \App\Models\CompanyModel();
        return $companyModel->where('urban_renewal_id', $urbanRenewalId)->first();
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
     * @param int|null $urbanRenewalId Filter by urban_renewal_id
     * @return array
     */
    public function getUrbanRenewalsWithAdmin($page = 1, $perPage = 10, $urbanRenewalId = null)
    {
        $builder = $this->select('urban_renewals.*, users.full_name as assigned_admin_name, users.email as assigned_admin_email')
                        ->join('users', 'users.id = urban_renewals.assigned_admin_id', 'left')
                        ->orderBy('urban_renewals.created_at', 'DESC');

        if ($urbanRenewalId !== null) {
            $builder->where('urban_renewals.id', $urbanRenewalId);
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
        return $urbanRenewal;
    }

    /**
     * Get all urban renewals with calculated member counts
     * @param int $page
     * @param int $perPage
     * @param int|null $urbanRenewalId
     * @return array
     */
    public function getUrbanRenewalsWithMemberCount($page = 1, $perPage = 10, $urbanRenewalId = null)
    {
        $urbanRenewals = $this->getUrbanRenewals($page, $perPage, $urbanRenewalId);
        
        // Add calculated member count to each record
        foreach ($urbanRenewals as &$renewal) {
            $renewal['member_count'] = $this->calculateMemberCount($renewal['id']);
        }
        
        return $urbanRenewals;
    }
}