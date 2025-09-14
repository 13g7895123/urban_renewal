<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CountyModel;
use App\Models\DistrictModel;
use App\Models\SectionModel;
use CodeIgniter\RESTful\ResourceController;

class LocationController extends ResourceController
{
    protected $format = 'json';

    /**
     * Get all counties
     */
    public function counties()
    {
        $countyModel = new CountyModel();
        $counties = $countyModel->getCountiesForDropdown();

        return $this->response
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->setJSON([
                'status' => 'success',
                'data' => $counties
            ]);
    }

    /**
     * Get districts by county code
     */
    public function districts($countyCode = null)
    {
        if (!$countyCode) {
            return $this->failValidationError('County code is required');
        }

        $districtModel = new DistrictModel();
        $districts = $districtModel->getByCountyCode($countyCode);

        return $this->response
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->setJSON([
                'status' => 'success',
                'data' => $districts
            ]);
    }

    /**
     * Get sections by district code and county code
     */
    public function sections($countyCode = null, $districtCode = null)
    {
        if (!$countyCode || !$districtCode) {
            return $this->failValidationError('County code and district code are required');
        }

        $sectionModel = new SectionModel();
        $sections = $sectionModel->getByDistrictAndCountyCode($districtCode, $countyCode);

        return $this->response
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->setJSON([
                'status' => 'success',
                'data' => $sections
            ]);
    }

    /**
     * Get complete location hierarchy
     */
    public function hierarchy()
    {
        $countyModel = new CountyModel();
        $districtModel = new DistrictModel();
        $sectionModel = new SectionModel();

        $counties = $countyModel->findAll();
        $hierarchy = [];

        foreach ($counties as $county) {
            $countyData = [
                'id' => $county['id'],
                'code' => $county['code'],
                'name' => $county['name'],
                'districts' => []
            ];

            $districts = $districtModel->getByCountyId($county['id']);

            foreach ($districts as $district) {
                $districtData = [
                    'id' => $district['id'],
                    'code' => $district['code'],
                    'name' => $district['name'],
                    'sections' => []
                ];

                $sections = $sectionModel->getByDistrictId($district['id']);

                foreach ($sections as $section) {
                    $districtData['sections'][] = [
                        'id' => $section['id'],
                        'code' => $section['code'],
                        'name' => $section['name']
                    ];
                }

                $countyData['districts'][] = $districtData;
            }

            $hierarchy[] = $countyData;
        }

        return $this->respond([
            'status' => 'success',
            'data' => $hierarchy
        ]);
    }

    /**
     * Handle OPTIONS requests for CORS
     */
    public function options()
    {
        return $this->response
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->setHeader('Access-Control-Max-Age', '86400')
            ->setStatusCode(200);
    }
}