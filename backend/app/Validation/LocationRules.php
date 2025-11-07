<?php

namespace App\Validation;

use App\Models\CountyModel;
use App\Models\DistrictModel;
use App\Models\SectionModel;

class LocationRules
{
    /**
     * Validate county code exists in database
     */
    public function validate_county_code(?string $value, ?string &$error = null): bool
    {
        if (empty($value)) {
            return true; // Let 'required' rule handle empty values
        }

        $countyModel = new CountyModel();
        $county = $countyModel->where('code', $value)->first();

        if (!$county) {
            $error = "縣市代碼 '{$value}' 不存在於資料庫中";
            return false;
        }

        return true;
    }

    /**
     * Validate district code exists for given county
     */
    public function validate_district_code(?string $value, string $params, array $data, ?string &$error = null): bool
    {
        if (empty($value)) {
            return true; // Let 'required' rule handle empty values
        }

        // Extract county from params
        $countyField = $params; // Should be 'county'
        $countyCode = $data[$countyField] ?? null;

        if (empty($countyCode)) {
            $error = "無法驗證行政區代碼：缺少縣市代碼";
            return false;
        }

        // Get county ID first
        $countyModel = new CountyModel();
        $county = $countyModel->where('code', $countyCode)->first();

        if (!$county) {
            $error = "縣市代碼 '{$countyCode}' 不存在";
            return false;
        }

        // Use the existing method from DistrictModel
        $districtModel = new DistrictModel();
        $district = $districtModel->getByCodeAndCounty($value, $county['id']);

        if (!$district) {
            $error = "行政區代碼 '{$value}' 在縣市 '{$countyCode}' 中不存在";
            return false;
        }

        return true;
    }

    /**
     * Validate section code exists for given county and district
     */
    public function validate_section_code(?string $value, string $params, array $data, ?string &$error = null): bool
    {
        if (empty($value)) {
            return true; // Let 'required' rule handle empty values
        }

        // Extract county and district from params
        $fields = explode(',', $params); // Should be 'county,district'
        $countyField = $fields[0] ?? 'county';
        $districtField = $fields[1] ?? 'district';

        $countyCode = $data[$countyField] ?? null;
        $districtCode = $data[$districtField] ?? null;

        if (empty($countyCode) || empty($districtCode)) {
            $error = "無法驗證段小段代碼：缺少縣市或行政區代碼";
            return false;
        }

        // Get county and district IDs first
        $countyModel = new CountyModel();
        $county = $countyModel->where('code', $countyCode)->first();

        if (!$county) {
            $error = "縣市代碼 '{$countyCode}' 不存在";
            return false;
        }

        $districtModel = new DistrictModel();
        $district = $districtModel->getByCodeAndCounty($districtCode, $county['id']);

        if (!$district) {
            $error = "行政區代碼 '{$districtCode}' 在縣市 '{$countyCode}' 中不存在";
            return false;
        }

        // Use the existing method from SectionModel
        $sectionModel = new SectionModel();
        $section = $sectionModel->getByCodeAndDistrict($value, $district['id']);

        if (!$section) {
            $error = "段小段代碼 '{$value}' 在縣市 '{$countyCode}' 行政區 '{$districtCode}' 中不存在";
            return false;
        }

        return true;
    }
}
