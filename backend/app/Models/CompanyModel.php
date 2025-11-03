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
        'urban_renewal_id',
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
     * Get company by urban_renewal_id
     * @param int $urbanRenewalId
     * @return array|null
     */
    public function getByUrbanRenewalId($urbanRenewalId)
    {
        return $this->where('urban_renewal_id', $urbanRenewalId)->first();
    }

    /**
     * Get company with its associated urban renewal data
     * @param int $id Company ID
     * @return array|null
     */
    public function getWithUrbanRenewal($id)
    {
        return $this->select('companies.*, urban_renewals.name as urban_renewal_name, urban_renewals.area, urban_renewals.member_count, urban_renewals.chairman_name, urban_renewals.chairman_phone')
                    ->join('urban_renewals', 'companies.urban_renewal_id = urban_renewals.id', 'left')
                    ->find($id);
    }

    /**
     * Get the associated urban renewal for this company
     * @param int $companyId
     * @return array|null
     */
    public function getUrbanRenewal($companyId)
    {
        $company = $this->find($companyId);
        if (!$company) {
            return null;
        }

        $urbanRenewalModel = new \App\Models\UrbanRenewalModel();
        return $urbanRenewalModel->find($company['urban_renewal_id']);
    }

    /**
     * Update company data
     * @param int $id Company ID
     * @param array $data
     * @return bool
     */
    public function updateCompany($id, $data)
    {
        // Remove urban_renewal_id from update data as it shouldn't be changed
        unset($data['urban_renewal_id']);
        
        return $this->update($id, $data);
    }
}
