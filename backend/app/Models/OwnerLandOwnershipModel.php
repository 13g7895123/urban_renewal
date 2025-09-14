<?php

namespace App\Models;

use CodeIgniter\Model;

class OwnerLandOwnershipModel extends Model
{
    protected $table = 'owner_land_ownership';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'property_owner_id',
        'land_plot_id',
        'ownership_numerator',
        'ownership_denominator'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'property_owner_id' => 'required|integer',
        'land_plot_id' => 'required|integer',
        'ownership_numerator' => 'required|integer|greater_than[0]',
        'ownership_denominator' => 'required|integer|greater_than[0]'
    ];

    protected $validationMessages = [
        'property_owner_id' => [
            'required' => '所有權人ID為必填項目',
            'integer' => '所有權人ID格式不正確'
        ],
        'land_plot_id' => [
            'required' => '地號ID為必填項目',
            'integer' => '地號ID格式不正確'
        ],
        'ownership_numerator' => [
            'required' => '持有比例分子為必填項目',
            'integer' => '持有比例分子必須為整數',
            'greater_than' => '持有比例分子必須大於0'
        ],
        'ownership_denominator' => [
            'required' => '持有比例分母為必填項目',
            'integer' => '持有比例分母必須為整數',
            'greater_than' => '持有比例分母必須大於0'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $beforeUpdate = [];
    protected $afterInsert = ['updateOwnerTotals'];
    protected $afterUpdate = ['updateOwnerTotals'];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = ['updateOwnerTotalsOnDelete'];

    /**
     * Update property owner totals after insert/update
     */
    protected function updateOwnerTotals(array $data)
    {
        if (isset($data['data']['property_owner_id'])) {
            $propertyOwnerModel = model('PropertyOwnerModel');
            $propertyOwnerModel->updateTotalAreas($data['data']['property_owner_id']);
        }
        return $data;
    }

    /**
     * Update property owner totals after delete
     */
    protected function updateOwnerTotalsOnDelete(array $data)
    {
        if (isset($data['purge']) && $data['purge'] && isset($data['data']['property_owner_id'])) {
            $propertyOwnerModel = model('PropertyOwnerModel');
            $propertyOwnerModel->updateTotalAreas($data['data']['property_owner_id']);
        }
        return $data;
    }

    /**
     * Get land ownership by property owner ID
     */
    public function getByPropertyOwnerId(int $propertyOwnerId): array
    {
        return $this->where('property_owner_id', $propertyOwnerId)
                    ->where('deleted_at', null)
                    ->findAll();
    }

    /**
     * Get land ownership by land plot ID
     */
    public function getByLandPlotId(int $landPlotId): array
    {
        return $this->where('land_plot_id', $landPlotId)
                    ->where('deleted_at', null)
                    ->findAll();
    }

    /**
     * Check if ownership already exists
     */
    public function ownershipExists(int $propertyOwnerId, int $landPlotId): bool
    {
        return $this->where('property_owner_id', $propertyOwnerId)
                    ->where('land_plot_id', $landPlotId)
                    ->where('deleted_at', null)
                    ->countAllResults() > 0;
    }

    /**
     * Create or update ownership
     */
    public function createOrUpdate(array $data): bool
    {
        $existing = $this->where('property_owner_id', $data['property_owner_id'])
                         ->where('land_plot_id', $data['land_plot_id'])
                         ->where('deleted_at', null)
                         ->first();

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert($data) !== false;
        }
    }
}