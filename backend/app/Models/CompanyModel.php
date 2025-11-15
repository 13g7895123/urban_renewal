<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'name',
        'tax_id',
        'company_phone',
        'max_renewal_count',
        'max_issue_count'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation rules
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[255]',
        'tax_id' => 'permit_empty|min_length[8]|max_length[20]',
        'company_phone' => 'permit_empty|min_length[8]|max_length[20]',
        'max_renewal_count' => 'permit_empty|integer|greater_than[0]',
        'max_issue_count' => 'permit_empty|integer|greater_than[0]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => '企業名稱為必填項目',
            'min_length' => '企業名稱至少需要2個字元',
            'max_length' => '企業名稱不能超過255個字元'
        ],
        'tax_id' => [
            'min_length' => '統一編號至少需要8個字元',
            'max_length' => '統一編號不能超過20個字元'
        ],
        'company_phone' => [
            'min_length' => '企業電話至少需要8個字元',
            'max_length' => '企業電話不能超過20個字元'
        ],
        'max_renewal_count' => [
            'integer' => '最大更新會數量必須為整數',
            'greater_than' => '最大更新會數量必須大於0'
        ],
        'max_issue_count' => [
            'integer' => '最大議題數量必須為整數',
            'greater_than' => '最大議題數量必須大於0'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Get company by urban_renewal_id (for backward compatibility during transition)
     * @param int $urbanRenewalId
     * @return array|null
     * @deprecated Use getRenewalsByCompanyId() with company_id instead
     */
    public function getByUrbanRenewalId($urbanRenewalId)
    {
        // During transition: find the company that manages this urban_renewal
        $urbanRenewalModel = new \App\Models\UrbanRenewalModel();
        $urbanRenewal = $urbanRenewalModel->find($urbanRenewalId);
        
        if (!$urbanRenewal || !$urbanRenewal['company_id']) {
            return null;
        }
        
        return $this->find($urbanRenewal['company_id']);
    }

    /**
     * Get company with its associated urban renewal data
     * @param int $id Company ID
     * @return array|null
     * @deprecated Use getRenewals() to get multiple renewals instead
     */
    public function getWithUrbanRenewal($id)
    {
        $company = $this->find($id);
        if (!$company) {
            return null;
        }
        
        // Get first urban renewal managed by this company
        $urbanRenewalModel = new \App\Models\UrbanRenewalModel();
        $urbanRenewal = $urbanRenewalModel->where('company_id', $id)->first();
        
        if ($urbanRenewal) {
            $company['urban_renewal_name'] = $urbanRenewal['name'];
            $company['chairman_name'] = $urbanRenewal['chairman_name'];
            $company['chairman_phone'] = $urbanRenewal['chairman_phone'];
        }
        
        return $company;
    }

    /**
     * Get the associated urban renewal for this company
     * @param int $companyId
     * @return array|null
     * @deprecated Companies can have multiple renewals now. Use getRenewals() instead
     */
    public function getUrbanRenewal($companyId)
    {
        $company = $this->find($companyId);
        if (!$company) {
            return null;
        }

        // Return the first urban renewal managed by this company
        $urbanRenewalModel = new \App\Models\UrbanRenewalModel();
        return $urbanRenewalModel->where('company_id', $companyId)->first();
    }

    /**
     * Get all urban renewals managed by this company
     * @param int $companyId Company ID
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array
     */
    public function getRenewals($companyId, $page = 1, $perPage = 10)
    {
        $urbanRenewalModel = new \App\Models\UrbanRenewalModel();
        return $urbanRenewalModel->where('company_id', $companyId)
                                  ->orderBy('created_at', 'DESC')
                                  ->paginate($perPage, 'default', $page);
    }

    /**
     * Get count of urban renewals managed by this company
     * @param int $companyId Company ID
     * @return int
     */
    public function getRenewalsCount($companyId): int
    {
        $urbanRenewalModel = new \App\Models\UrbanRenewalModel();
        return $urbanRenewalModel->where('company_id', $companyId)->countAllResults();
    }

    /**
     * Check if company has reached its renewal quota
     * @param int $companyId Company ID
     * @return bool True if quota is reached or exceeded
     */
    public function checkRenewalQuota($companyId): bool
    {
        $company = $this->find($companyId);
        if (!$company) {
            return false;
        }

        $currentCount = $this->getRenewalsCount($companyId);
        $maxCount = $company['max_renewal_count'] ?? 1;

        return $currentCount >= $maxCount;
    }

    /**
     * Update company data
     * @param int $id Company ID
     * @param array $data
     * @return bool
     */
    public function updateCompany($id, $data)
    {
        return $this->update($id, $data);
    }
}
