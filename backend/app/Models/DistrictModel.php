<?php

namespace App\Models;

use CodeIgniter\Model;

class DistrictModel extends Model
{
    protected $table = 'districts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDelete = false;
    protected $protectFields = true;
    protected $allowedFields = ['county_id', 'code', 'name'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'county_id' => 'required|integer',
        'code' => 'required|max_length[10]',
        'name' => 'required|max_length[50]',
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get districts by county ID
     */
    public function getByCountyId(int $countyId)
    {
        return $this->where('county_id', $countyId)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get districts by county code
     */
    public function getByCountyCode(string $countyCode)
    {
        $countyModel = new CountyModel();
        $county = $countyModel->getByCode($countyCode);

        if (!$county) {
            return [];
        }

        return $this->getByCountyId($county['id']);
    }

    /**
     * Get district by code and county ID
     */
    public function getByCodeAndCounty(string $code, int $countyId)
    {
        return $this->where('code', $code)
                   ->where('county_id', $countyId)
                   ->first();
    }
}