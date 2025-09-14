<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OfficialTaiwanLocationSeeder extends Seeder
{
    public function run()
    {
        // Load processed Taiwan location data
        $jsonFile = ROOTPATH . 'processed_taiwan_locations.json';

        if (!file_exists($jsonFile)) {
            throw new \Exception("Processed Taiwan location data file not found: $jsonFile");
        }

        $jsonData = file_get_contents($jsonFile);
        $locationData = json_decode($jsonData, true);

        if (!$locationData) {
            throw new \Exception("Failed to decode Taiwan location data JSON");
        }

        echo "Loading Taiwan location data with:\n";
        echo "- Counties: " . count($locationData['counties']) . "\n";
        echo "- Districts: " . count($locationData['districts']) . "\n";
        echo "- Sections: " . count($locationData['sections']) . "\n\n";

        // Clear existing location data (use emptyTable for FK constraints)
        $this->db->table('sections')->emptyTable();
        $this->db->table('districts')->emptyTable();
        $this->db->table('counties')->emptyTable();

        // Insert counties in batches
        echo "Inserting counties...\n";
        $this->db->table('counties')->insertBatch($locationData['counties']);

        // Get county IDs for district insertion
        $countyIds = [];
        $counties = $this->db->table('counties')->get()->getResultArray();
        foreach ($counties as $county) {
            $countyIds[$county['code']] = $county['id'];
        }

        // Prepare districts with county_id
        echo "Preparing districts...\n";
        $districtsWithIds = [];
        foreach ($locationData['districts'] as $district) {
            $districtsWithIds[] = [
                'code' => $district['code'],
                'name' => $district['name'],
                'county_id' => $countyIds[$district['county_code']]
            ];
        }

        // Insert districts in batches of 100
        echo "Inserting districts in batches...\n";
        $districtBatches = array_chunk($districtsWithIds, 100);
        foreach ($districtBatches as $batch) {
            $this->db->table('districts')->insertBatch($batch);
        }

        // Get district IDs for section insertion
        $districtIds = [];
        $districts = $this->db->table('districts')->get()->getResultArray();
        foreach ($districts as $district) {
            $districtIds[$district['code']] = $district['id'];
        }

        // Prepare sections with district_id
        echo "Preparing sections...\n";
        $sectionsWithIds = [];
        foreach ($locationData['sections'] as $section) {
            $sectionsWithIds[] = [
                'code' => $section['code'],
                'name' => $section['name'],
                'district_id' => $districtIds[$section['district_code']]
            ];
        }

        // Insert sections in batches of 500 (large dataset)
        echo "Inserting sections in batches...\n";
        $sectionBatches = array_chunk($sectionsWithIds, 500);
        $batchCount = 1;
        $totalBatches = count($sectionBatches);

        foreach ($sectionBatches as $batch) {
            echo "Processing batch $batchCount/$totalBatches (" . count($batch) . " sections)\n";
            $this->db->table('sections')->insertBatch($batch);
            $batchCount++;
        }

        echo "\n=== Seeding Complete ===\n";
        echo "Successfully loaded official Taiwan location data:\n";
        echo "- " . count($locationData['counties']) . " counties\n";
        echo "- " . count($locationData['districts']) . " districts\n";
        echo "- " . count($locationData['sections']) . " sections\n";

        // Display sample data for verification
        echo "\n=== Sample Data Verification ===\n";

        // Sample counties
        echo "Sample Counties:\n";
        $sampleCounties = $this->db->table('counties')->limit(5)->get()->getResultArray();
        foreach ($sampleCounties as $county) {
            echo "- {$county['code']}: {$county['name']}\n";
        }

        // Sample districts
        echo "\nSample Districts:\n";
        $sampleDistricts = $this->db->table('districts')
            ->select('districts.code, districts.name, counties.name as county_name')
            ->join('counties', 'counties.id = districts.county_id')
            ->limit(5)
            ->get()
            ->getResultArray();
        foreach ($sampleDistricts as $district) {
            echo "- {$district['code']}: {$district['name']} ({$district['county_name']})\n";
        }

        // Sample sections
        echo "\nSample Sections:\n";
        $sampleSections = $this->db->table('sections')
            ->select('sections.code, sections.name, districts.name as district_name')
            ->join('districts', 'districts.id = sections.district_id')
            ->limit(5)
            ->get()
            ->getResultArray();
        foreach ($sampleSections as $section) {
            echo "- {$section['code']}: {$section['name']} ({$section['district_name']})\n";
        }

        echo "\nTaiwan location data seeding completed successfully!\n";
    }
}