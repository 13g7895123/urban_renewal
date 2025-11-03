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
        'area',
        'member_count',
        'chairman_name',
        'chairman_phone',
        'address',
        'representative'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[255]',
        'area' => 'required|decimal|greater_than[0]',
        'member_count' => 'required|integer|greater_than[0]',
        'chairman_name' => 'required|min_length[2]|max_length[100]',
        'chairman_phone' => 'required|min_length[8]|max_length[20]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => '更新會名稱為必填項目',
            'min_length' => '更新會名稱至少需要2個字元',
            'max_length' => '更新會名稱不能超過255個字元'
        ],
        'area' => [
            'required' => '土地面積為必填項目',
            'decimal' => '土地面積必須為數字',
            'greater_than' => '土地面積必須大於0'
        ],
        'member_count' => [
            'required' => '所有權人數為必填項目',
            'integer' => '所有權人數必須為整數',
            'greater_than' => '所有權人數必須大於0'
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
}