<?php

namespace App\Models;

use CodeIgniter\Model;

class SectionModel extends Model
{
    protected $table = 'sections';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDelete = false;
    protected $protectFields = true;
    protected $allowedFields = ['district_id', 'code', 'name'];

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
        'district_id' => 'required|integer',
        'code' => 'required|max_length[10]',
        'name' => 'required|max_length[100]',
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
     * Get sections by district ID
     */
    public function getByDistrictId(int $districtId)
    {
        return $this->where('district_id', $districtId)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    /**
     * Get sections by district code and county code
     */
    public function getByDistrictAndCountyCode(string $districtCode, string $countyCode)
    {
        $districtModel = new DistrictModel();
        $countyModel = new CountyModel();

        $county = $countyModel->getByCode($countyCode);
        if (!$county) {
            return [];
        }

        $district = $districtModel->getByCodeAndCounty($districtCode, $county['id']);
        if (!$district) {
            return [];
        }

        return $this->getByDistrictId($district['id']);
    }

    /**
     * Get section by code and district ID
     */
    public function getByCodeAndDistrict(string $code, int $districtId)
    {
        return $this->where('code', $code)
                   ->where('district_id', $districtId)
                   ->first();
    }
}