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
        'chairman_phone'
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
     */
    public function getUrbanRenewals($page = 1, $perPage = 10)
    {
        return $this->orderBy('created_at', 'DESC')
                   ->paginate($perPage, 'default', $page);
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
     */
    public function searchByName($name, $page = 1, $perPage = 10)
    {
        return $this->like('name', $name)
                   ->orderBy('created_at', 'DESC')
                   ->paginate($perPage, 'default', $page);
    }
}