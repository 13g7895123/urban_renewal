<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyManagerRenewalModel extends Model
{
    protected $table = 'company_managers_renewals';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'company_id',
        'manager_id',
        'urban_renewal_id',
        'permission_level',
        'is_primary',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'company_id' => 'required|integer',
        'manager_id' => 'required|integer',
        'urban_renewal_id' => 'required|integer',
        'permission_level' => 'in_list[full,readonly,finance]',
        'is_primary' => 'integer',
    ];

    protected $validationMessages = [
        'company_id' => [
            'required' => '企業ID為必填',
            'integer' => '企業ID必須為整數',
        ],
        'manager_id' => [
            'required' => '管理者ID為必填',
            'integer' => '管理者ID必須為整數',
        ],
        'urban_renewal_id' => [
            'required' => '更新會ID為必填',
            'integer' => '更新會ID必須為整數',
        ],
        'permission_level' => [
            'in_list' => '權限等級必須為：full, readonly, finance',
        ],
    ];

    /**
     * 獲取某管理者在某企業下可管理的所有更新會
     * 
     * @param int $companyId 企業ID
     * @param int $managerId 管理者ID
     * @return array 更新會列表
     */
    public function getManagerRenewals($companyId, $managerId)
    {
        return $this->select('cmr.*, ur.name as renewal_name, ur.chairman_name')
                    ->from($this->table . ' cmr')
                    ->join('urban_renewals ur', 'ur.id = cmr.urban_renewal_id', 'left')
                    ->where('cmr.company_id', $companyId)
                    ->where('cmr.manager_id', $managerId)
                    ->orderBy('cmr.is_primary', 'DESC')
                    ->orderBy('cmr.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * 獲取某企業某更新會的所有管理者
     * 
     * @param int $companyId 企業ID
     * @param int $urbanRenewalId 更新會ID
     * @return array 管理者列表
     */
    public function getRenewalManagers($companyId, $urbanRenewalId)
    {
        return $this->select('cmr.*, u.username, u.full_name, u.email')
                    ->from($this->table . ' cmr')
                    ->join('users u', 'u.id = cmr.manager_id', 'left')
                    ->where('cmr.company_id', $companyId)
                    ->where('cmr.urban_renewal_id', $urbanRenewalId)
                    ->orderBy('cmr.is_primary', 'DESC')
                    ->orderBy('u.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * 檢查管理者是否有權限訪問某更新會
     * 
     * @param int $managerId 管理者ID
     * @param int $companyId 企業ID
     * @param int $urbanRenewalId 更新會ID
     * @return bool
     */
    public function hasAccess($managerId, $companyId, $urbanRenewalId)
    {
        return $this->where('manager_id', $managerId)
                    ->where('company_id', $companyId)
                    ->where('urban_renewal_id', $urbanRenewalId)
                    ->countAllResults() > 0;
    }

    /**
     * 獲取管理者有權限訪問的更新會ID列表
     * 
     * @param int $managerId 管理者ID
     * @param int $companyId 企業ID
     * @return array 更新會ID列表
     */
    public function getAccessibleRenewalIds($managerId, $companyId)
    {
        $results = $this->select('urban_renewal_id')
                        ->where('manager_id', $managerId)
                        ->where('company_id', $companyId)
                        ->findAll();
        
        return array_column($results, 'urban_renewal_id');
    }

    /**
     * 為管理者授予某個更新會的權限
     * 
     * @param int $companyId 企業ID
     * @param int $managerId 管理者ID
     * @param int $urbanRenewalId 更新會ID
     * @param string $permissionLevel 權限等級
     * @param bool $isPrimary 是否為主管理者
     * @return bool|int
     */
    public function grantAccess($companyId, $managerId, $urbanRenewalId, $permissionLevel = 'full', $isPrimary = false)
    {
        // 檢查是否已存在
        $existing = $this->where('company_id', $companyId)
                         ->where('manager_id', $managerId)
                         ->where('urban_renewal_id', $urbanRenewalId)
                         ->first();

        if ($existing) {
            // 更新現有記錄
            return $this->update($existing['id'], [
                'permission_level' => $permissionLevel,
                'is_primary' => $isPrimary ? 1 : 0,
            ]);
        } else {
            // 建立新記錄
            return $this->insert([
                'company_id' => $companyId,
                'manager_id' => $managerId,
                'urban_renewal_id' => $urbanRenewalId,
                'permission_level' => $permissionLevel,
                'is_primary' => $isPrimary ? 1 : 0,
            ]);
        }
    }

    /**
     * 撤銷管理者對某個更新會的權限
     * 
     * @param int $companyId 企業ID
     * @param int $managerId 管理者ID
     * @param int $urbanRenewalId 更新會ID
     * @return bool
     */
    public function revokeAccess($companyId, $managerId, $urbanRenewalId)
    {
        return $this->where('company_id', $companyId)
                    ->where('manager_id', $managerId)
                    ->where('urban_renewal_id', $urbanRenewalId)
                    ->delete();
    }

    /**
     * 獲取某企業的所有管理者及其管理的更新會
     * 
     * @param int $companyId 企業ID
     * @return array
     */
    public function getCompanyManagersWithRenewals($companyId)
    {
        return $this->select('cmr.*, u.username, u.full_name, u.email, ur.name as renewal_name')
                    ->from($this->table . ' cmr')
                    ->join('users u', 'u.id = cmr.manager_id', 'left')
                    ->join('urban_renewals ur', 'ur.id = cmr.urban_renewal_id', 'left')
                    ->where('cmr.company_id', $companyId)
                    ->orderBy('cmr.manager_id', 'ASC')
                    ->orderBy('cmr.is_primary', 'DESC')
                    ->orderBy('ur.name', 'ASC')
                    ->findAll();
    }

    /**
     * 撤銷某管理者在企業下的所有權限
     * 
     * @param int $companyId 企業ID
     * @param int $managerId 管理者ID
     * @return bool
     */
    public function revokeAllAccess($companyId, $managerId)
    {
        return $this->where('company_id', $companyId)
                    ->where('manager_id', $managerId)
                    ->delete();
    }

    /**
     * 檢查管理者是否為某企業某更新會的主管理者
     * 
     * @param int $managerId 管理者ID
     * @param int $companyId 企業ID
     * @param int $urbanRenewalId 更新會ID
     * @return bool
     */
    public function isPrimaryManager($managerId, $companyId, $urbanRenewalId)
    {
        return $this->where('manager_id', $managerId)
                    ->where('company_id', $companyId)
                    ->where('urban_renewal_id', $urbanRenewalId)
                    ->where('is_primary', 1)
                    ->countAllResults() > 0;
    }
}
