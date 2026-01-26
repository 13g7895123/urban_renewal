<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
$loader = require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/spark'; // Basic CI4 environment

use Config\Services;
use App\Models\CountyModel;
use App\Models\DistrictModel;
use App\Models\SectionModel;

$countyModel = new CountyModel();
$districtModel = new DistrictModel();
$sectionModel = new SectionModel();

$testData = [
    ['county' => 'CYI', 'district' => 'CYIA0', 'section' => '0090'],
    ['county' => 'HSQ', 'district' => 'HSQA0', 'section' => '0119'],
];

foreach ($testData as $data) {
    echo "Testing: County={$data['county']}, District={$data['district']}, Section={$data['section']}\n";
    
    $county = $countyModel->where('code', $data['county'])->first();
    if (!$county) {
        echo "FAIL: County {$data['county']} not found\n";
        continue;
    }
    echo "SUCCESS: County {$data['county']} found (ID: {$county['id']})\n";
    
    $district = $districtModel->where('code', $data['district'])->where('county_id', $county['id'])->first();
    if (!$district) {
        echo "FAIL: District {$data['district']} not found in County {$data['county']}\n";
        continue;
    }
    echo "SUCCESS: District {$data['district']} found (ID: {$district['id']})\n";
    
    $section = $sectionModel->where('code', $data['section'])->where('district_id', $district['id'])->first();
    if (!$section) {
        echo "FAIL: Section {$data['section']} not found in District {$data['district']}\n";
        continue;
    }
    echo "SUCCESS: Section {$data['section']} found (ID: {$section['id']})\n";
    echo "-------------------\n";
}
