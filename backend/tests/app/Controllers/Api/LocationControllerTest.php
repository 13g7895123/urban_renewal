<?php

namespace Tests\app\Controllers\Api;

use Tests\DatabaseTestCase;

/**
 * Test suite for Location API endpoints
 *
 * Following TDD principles with comprehensive test coverage for:
 * - GET /api/locations/counties (counties)
 * - GET /api/locations/districts/{countyCode} (districts)
 * - GET /api/locations/sections/{countyCode}/{districtCode} (sections)
 * - GET /api/locations/hierarchy (hierarchy)
 */
class LocationControllerTest extends DatabaseTestCase
{
    /**
     * Setup sample location data for testing
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Insert sample location data
        $this->insertLocationData();
    }

    /**
     * Insert comprehensive location test data
     */
    protected function insertLocationData(): void
    {
        $db = \Config\Database::connect();

        // Insert counties
        $counties = [
            ['county_code' => '63', 'county_name' => '臺北市'],
            ['county_code' => '65', 'county_name' => '新北市'],
            ['county_code' => '64', 'county_name' => '桃園市'],
            ['county_code' => '66', 'county_name' => '臺中市']
        ];

        foreach ($counties as $county) {
            $db->table('counties')->insert($county);
        }

        // Insert districts for 臺北市 (63)
        $taipeiDistricts = [
            ['county_code' => '63', 'district_code' => '100', 'district_name' => '中正區'],
            ['county_code' => '63', 'district_code' => '103', 'district_name' => '大同區'],
            ['county_code' => '63', 'district_code' => '104', 'district_name' => '中山區'],
            ['county_code' => '63', 'district_code' => '105', 'district_name' => '松山區']
        ];

        foreach ($taipeiDistricts as $district) {
            $db->table('districts')->insert($district);
        }

        // Insert districts for 新北市 (65)
        $newTaipeiDistricts = [
            ['county_code' => '65', 'district_code' => '220', 'district_name' => '板橋區'],
            ['county_code' => '65', 'district_code' => '221', 'district_name' => '汐止區'],
            ['county_code' => '65', 'district_code' => '222', 'district_name' => '深坑區']
        ];

        foreach ($newTaipeiDistricts as $district) {
            $db->table('districts')->insert($district);
        }

        // Insert sections for 中正區 (63-100)
        $zhongzhengSections = [
            ['county_code' => '63', 'district_code' => '100', 'section_code' => '0001', 'section_name' => '城中段'],
            ['county_code' => '63', 'district_code' => '100', 'section_code' => '0002', 'section_name' => '南門段'],
            ['county_code' => '63', 'district_code' => '100', 'section_code' => '0003', 'section_name' => '古亭段']
        ];

        foreach ($zhongzhengSections as $section) {
            $db->table('sections')->insert($section);
        }

        // Insert sections for 大同區 (63-103)
        $datongSections = [
            ['county_code' => '63', 'district_code' => '103', 'section_code' => '0001', 'section_name' => '大同段'],
            ['county_code' => '63', 'district_code' => '103', 'section_code' => '0002', 'section_name' => '建成段']
        ];

        foreach ($datongSections as $section) {
            $db->table('sections')->insert($section);
        }

        // Insert sections for 板橋區 (65-220)
        $banqiaoSections = [
            ['county_code' => '65', 'district_code' => '220', 'section_code' => '0001', 'section_name' => '板橋段'],
            ['county_code' => '65', 'district_code' => '220', 'section_code' => '0002', 'section_name' => '江子翠段']
        ];

        foreach ($banqiaoSections as $section) {
            $db->table('sections')->insert($section);
        }
    }

    // ===================== GET /api/locations/counties Tests =====================

    /**
     * @test
     * Test getting all counties returns proper structure
     */
    public function test_counties_returns_proper_response_structure(): void
    {
        $result = $this->get('/api/locations/counties');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertIsArray($response['data']);
        $this->assertGreaterThan(0, count($response['data']));

        // Check first county structure
        $firstCounty = $response['data'][0];
        $this->assertArrayHasKey('county_code', $firstCounty);
        $this->assertArrayHasKey('county_name', $firstCounty);
    }

    /**
     * @test
     * Test counties are returned in correct order
     */
    public function test_counties_returned_in_correct_order(): void
    {
        $result = $this->get('/api/locations/counties');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $counties = $response['data'];

        // Should be ordered by county_code or county_name
        $this->assertGreaterThanOrEqual(4, count($counties));

        // Find specific counties to verify they exist
        $countyNames = array_column($counties, 'county_name');
        $this->assertContains('臺北市', $countyNames);
        $this->assertContains('新北市', $countyNames);
        $this->assertContains('桃園市', $countyNames);
        $this->assertContains('臺中市', $countyNames);
    }

    /**
     * @test
     * Test counties endpoint with empty database
     */
    public function test_counties_returns_empty_array_when_no_data(): void
    {
        // Clear counties table
        $db = \Config\Database::connect();
        $db->table('counties')->truncate();

        $result = $this->get('/api/locations/counties');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEmpty($response['data']);
    }

    /**
     * @test
     * Test counties endpoint returns CORS headers
     */
    public function test_counties_returns_cors_headers(): void
    {
        $result = $this->get('/api/locations/counties');

        $result->assertStatus(200);
        $result->assertHeader('Access-Control-Allow-Origin', '*');
        $result->assertHeader('Access-Control-Allow-Methods');
        $result->assertHeader('Access-Control-Allow-Headers');
    }

    // ===================== GET /api/locations/districts/{countyCode} Tests =====================

    /**
     * @test
     * Test getting districts by valid county code
     */
    public function test_districts_by_valid_county_code(): void
    {
        $result = $this->get('/api/locations/districts/63');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertIsArray($response['data']);
        $this->assertGreaterThan(0, count($response['data']));

        // Check first district structure
        $firstDistrict = $response['data'][0];
        $this->assertArrayHasKey('district_code', $firstDistrict);
        $this->assertArrayHasKey('district_name', $firstDistrict);
        $this->assertEquals('63', $firstDistrict['county_code']);
    }

    /**
     * @test
     * Test getting districts for county with multiple districts
     */
    public function test_districts_for_taipei_city(): void
    {
        $result = $this->get('/api/locations/districts/63');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $districts = $response['data'];

        $this->assertGreaterThanOrEqual(4, count($districts));

        $districtNames = array_column($districts, 'district_name');
        $this->assertContains('中正區', $districtNames);
        $this->assertContains('大同區', $districtNames);
        $this->assertContains('中山區', $districtNames);
        $this->assertContains('松山區', $districtNames);

        // All districts should belong to the same county
        foreach ($districts as $district) {
            $this->assertEquals('63', $district['county_code']);
        }
    }

    /**
     * @test
     * Test getting districts for different county
     */
    public function test_districts_for_new_taipei_city(): void
    {
        $result = $this->get('/api/locations/districts/65');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $districts = $response['data'];

        $this->assertGreaterThanOrEqual(3, count($districts));

        $districtNames = array_column($districts, 'district_name');
        $this->assertContains('板橋區', $districtNames);
        $this->assertContains('汐止區', $districtNames);
        $this->assertContains('深坑區', $districtNames);

        // All districts should belong to New Taipei City
        foreach ($districts as $district) {
            $this->assertEquals('65', $district['county_code']);
        }
    }

    /**
     * @test
     * Test getting districts for non-existent county code
     */
    public function test_districts_for_nonexistent_county_code(): void
    {
        $result = $this->get('/api/locations/districts/999');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEmpty($response['data']);
    }

    /**
     * @test
     * Test getting districts without county code returns validation error
     */
    public function test_districts_without_county_code_returns_error(): void
    {
        $result = $this->get('/api/locations/districts/');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('required', $response['message']);
    }

    /**
     * @test
     * Test getting districts with empty county code
     */
    public function test_districts_with_empty_county_code(): void
    {
        $result = $this->get('/api/locations/districts/ ');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    /**
     * @test
     * Test districts endpoint returns CORS headers
     */
    public function test_districts_returns_cors_headers(): void
    {
        $result = $this->get('/api/locations/districts/63');

        $result->assertStatus(200);
        $result->assertHeader('Access-Control-Allow-Origin', '*');
        $result->assertHeader('Access-Control-Allow-Methods');
        $result->assertHeader('Access-Control-Allow-Headers');
    }

    // ===================== GET /api/locations/sections/{countyCode}/{districtCode} Tests =====================

    /**
     * @test
     * Test getting sections by valid county and district codes
     */
    public function test_sections_by_valid_county_and_district_codes(): void
    {
        $result = $this->get('/api/locations/sections/63/100');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertIsArray($response['data']);
        $this->assertGreaterThan(0, count($response['data']));

        // Check first section structure
        $firstSection = $response['data'][0];
        $this->assertArrayHasKey('section_code', $firstSection);
        $this->assertArrayHasKey('section_name', $firstSection);
        $this->assertEquals('63', $firstSection['county_code']);
        $this->assertEquals('100', $firstSection['district_code']);
    }

    /**
     * @test
     * Test getting sections for 中正區 (Zhongzheng District)
     */
    public function test_sections_for_zhongzheng_district(): void
    {
        $result = $this->get('/api/locations/sections/63/100');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $sections = $response['data'];

        $this->assertGreaterThanOrEqual(3, count($sections));

        $sectionNames = array_column($sections, 'section_name');
        $this->assertContains('城中段', $sectionNames);
        $this->assertContains('南門段', $sectionNames);
        $this->assertContains('古亭段', $sectionNames);

        // All sections should belong to the same district
        foreach ($sections as $section) {
            $this->assertEquals('63', $section['county_code']);
            $this->assertEquals('100', $section['district_code']);
        }
    }

    /**
     * @test
     * Test getting sections for different district
     */
    public function test_sections_for_datong_district(): void
    {
        $result = $this->get('/api/locations/sections/63/103');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $sections = $response['data'];

        $this->assertGreaterThanOrEqual(2, count($sections));

        $sectionNames = array_column($sections, 'section_name');
        $this->assertContains('大同段', $sectionNames);
        $this->assertContains('建成段', $sectionNames);

        // All sections should belong to Datong District
        foreach ($sections as $section) {
            $this->assertEquals('63', $section['county_code']);
            $this->assertEquals('103', $section['district_code']);
        }
    }

    /**
     * @test
     * Test getting sections for different county/district combination
     */
    public function test_sections_for_banqiao_district(): void
    {
        $result = $this->get('/api/locations/sections/65/220');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $sections = $response['data'];

        $this->assertGreaterThanOrEqual(2, count($sections));

        $sectionNames = array_column($sections, 'section_name');
        $this->assertContains('板橋段', $sectionNames);
        $this->assertContains('江子翠段', $sectionNames);

        // All sections should belong to Banqiao District
        foreach ($sections as $section) {
            $this->assertEquals('65', $section['county_code']);
            $this->assertEquals('220', $section['district_code']);
        }
    }

    /**
     * @test
     * Test getting sections for non-existent county/district combination
     */
    public function test_sections_for_nonexistent_county_district(): void
    {
        $result = $this->get('/api/locations/sections/999/999');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEmpty($response['data']);
    }

    /**
     * @test
     * Test getting sections with valid county but non-existent district
     */
    public function test_sections_for_valid_county_nonexistent_district(): void
    {
        $result = $this->get('/api/locations/sections/63/999');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEmpty($response['data']);
    }

    /**
     * @test
     * Test getting sections without county code returns error
     */
    public function test_sections_without_county_code_returns_error(): void
    {
        $result = $this->get('/api/locations/sections//100');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    /**
     * @test
     * Test getting sections without district code returns error
     */
    public function test_sections_without_district_code_returns_error(): void
    {
        $result = $this->get('/api/locations/sections/63/');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    /**
     * @test
     * Test sections endpoint returns CORS headers
     */
    public function test_sections_returns_cors_headers(): void
    {
        $result = $this->get('/api/locations/sections/63/100');

        $result->assertStatus(200);
        $result->assertHeader('Access-Control-Allow-Origin', '*');
        $result->assertHeader('Access-Control-Allow-Methods');
        $result->assertHeader('Access-Control-Allow-Headers');
    }

    // ===================== GET /api/locations/hierarchy Tests =====================

    /**
     * @test
     * Test getting location hierarchy returns proper structure
     */
    public function test_hierarchy_returns_proper_structure(): void
    {
        $result = $this->get('/api/locations/hierarchy');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertIsArray($response['data']);

        // Check if hierarchy has counties
        $this->assertGreaterThan(0, count($response['data']));

        // Check structure of first county in hierarchy
        $firstCounty = $response['data'][0];
        $this->assertArrayHasKey('county_code', $firstCounty);
        $this->assertArrayHasKey('county_name', $firstCounty);
        $this->assertArrayHasKey('districts', $firstCounty);
        $this->assertIsArray($firstCounty['districts']);

        // If there are districts, check their structure
        if (!empty($firstCounty['districts'])) {
            $firstDistrict = $firstCounty['districts'][0];
            $this->assertArrayHasKey('district_code', $firstDistrict);
            $this->assertArrayHasKey('district_name', $firstDistrict);
            $this->assertArrayHasKey('sections', $firstDistrict);
            $this->assertIsArray($firstDistrict['sections']);

            // If there are sections, check their structure
            if (!empty($firstDistrict['sections'])) {
                $firstSection = $firstDistrict['sections'][0];
                $this->assertArrayHasKey('section_code', $firstSection);
                $this->assertArrayHasKey('section_name', $firstSection);
            }
        }
    }

    /**
     * @test
     * Test hierarchy contains expected data relationships
     */
    public function test_hierarchy_contains_expected_data(): void
    {
        $result = $this->get('/api/locations/hierarchy');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $hierarchy = $response['data'];

        // Find Taipei City in hierarchy
        $taipeiCity = null;
        foreach ($hierarchy as $county) {
            if ($county['county_code'] === '63') {
                $taipeiCity = $county;
                break;
            }
        }

        $this->assertNotNull($taipeiCity, 'Taipei City should exist in hierarchy');
        $this->assertEquals('臺北市', $taipeiCity['county_name']);
        $this->assertGreaterThan(0, count($taipeiCity['districts']));

        // Find Zhongzheng District within Taipei City
        $zhongzhengDistrict = null;
        foreach ($taipeiCity['districts'] as $district) {
            if ($district['district_code'] === '100') {
                $zhongzhengDistrict = $district;
                break;
            }
        }

        $this->assertNotNull($zhongzhengDistrict, 'Zhongzheng District should exist');
        $this->assertEquals('中正區', $zhongzhengDistrict['district_name']);
        $this->assertGreaterThan(0, count($zhongzhengDistrict['sections']));

        // Check if specific sections exist
        $sectionNames = array_column($zhongzhengDistrict['sections'], 'section_name');
        $this->assertContains('城中段', $sectionNames);
    }

    /**
     * @test
     * Test hierarchy with empty database returns empty structure
     */
    public function test_hierarchy_with_empty_database(): void
    {
        // Clear all location tables
        $db = \Config\Database::connect();
        $db->table('sections')->truncate();
        $db->table('districts')->truncate();
        $db->table('counties')->truncate();

        $result = $this->get('/api/locations/hierarchy');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEmpty($response['data']);
    }

    /**
     * @test
     * Test hierarchy returns CORS headers
     */
    public function test_hierarchy_returns_cors_headers(): void
    {
        $result = $this->get('/api/locations/hierarchy');

        $result->assertStatus(200);
        $result->assertHeader('Access-Control-Allow-Origin', '*');
        $result->assertHeader('Access-Control-Allow-Methods');
        $result->assertHeader('Access-Control-Allow-Headers');
    }

    // ===================== CORS and Options Tests =====================

    /**
     * @test
     * Test OPTIONS request for CORS preflight on all location endpoints
     */
    public function test_options_request_returns_cors_headers(): void
    {
        $endpoints = [
            '/api/locations/counties',
            '/api/locations/districts/63',
            '/api/locations/sections/63/100',
            '/api/locations/hierarchy'
        ];

        foreach ($endpoints as $endpoint) {
            $result = $this->options($endpoint);

            $result->assertStatus(200);
            $result->assertHeader('Access-Control-Allow-Origin', '*');
            $result->assertHeader('Access-Control-Allow-Methods');
            $result->assertHeader('Access-Control-Allow-Headers');
        }
    }

    // ===================== Performance and Edge Case Tests =====================

    /**
     * @test
     * Test location endpoints with special characters in codes
     */
    public function test_location_endpoints_with_special_characters(): void
    {
        // Test with special characters that might cause issues
        $specialCodes = ['<script>', '\'; DROP TABLE counties; --', '../../../etc/passwd'];

        foreach ($specialCodes as $code) {
            $result = $this->get("/api/locations/districts/" . urlencode($code));

            // Should not cause server errors
            $this->assertNotEquals(500, $result->getStatusCode());

            // Should return appropriate error or empty result
            $this->assertContains($result->getStatusCode(), [200, 400, 404]);
        }
    }

    /**
     * @test
     * Test location data consistency across endpoints
     */
    public function test_location_data_consistency(): void
    {
        // Get all counties
        $countiesResult = $this->get('/api/locations/counties');
        $counties = $countiesResult->getJSON(true)['data'];

        // For each county, verify districts are consistent
        foreach ($counties as $county) {
            $districtsResult = $this->get("/api/locations/districts/{$county['county_code']}");
            $districts = $districtsResult->getJSON(true)['data'];

            // All districts should belong to the same county
            foreach ($districts as $district) {
                $this->assertEquals($county['county_code'], $district['county_code']);

                // Test sections for this district
                $sectionsResult = $this->get("/api/locations/sections/{$county['county_code']}/{$district['district_code']}");
                $sections = $sectionsResult->getJSON(true)['data'];

                // All sections should belong to the same county and district
                foreach ($sections as $section) {
                    $this->assertEquals($county['county_code'], $section['county_code']);
                    $this->assertEquals($district['district_code'], $section['district_code']);
                }
            }
        }
    }

    /**
     * @test
     * Test hierarchy data matches individual endpoint data
     */
    public function test_hierarchy_matches_individual_endpoints(): void
    {
        // Get hierarchy
        $hierarchyResult = $this->get('/api/locations/hierarchy');
        $hierarchy = $hierarchyResult->getJSON(true)['data'];

        // Get counties from individual endpoint
        $countiesResult = $this->get('/api/locations/counties');
        $counties = $countiesResult->getJSON(true)['data'];

        // Compare county counts
        $this->assertEquals(count($counties), count($hierarchy));

        // Check each county in hierarchy exists in counties endpoint
        foreach ($hierarchy as $hierarchyCounty) {
            $foundCounty = false;
            foreach ($counties as $county) {
                if ($county['county_code'] === $hierarchyCounty['county_code']) {
                    $foundCounty = true;
                    $this->assertEquals($county['county_name'], $hierarchyCounty['county_name']);
                    break;
                }
            }
            $this->assertTrue($foundCounty, "County {$hierarchyCounty['county_code']} should exist in counties endpoint");
        }
    }

    // ===================== Integration Tests =====================

    /**
     * @test
     * Test complete location navigation flow
     */
    public function test_complete_location_navigation_flow(): void
    {
        // 1. Get all counties
        $countiesResult = $this->get('/api/locations/counties');
        $countiesResult->assertStatus(200);
        $counties = $countiesResult->getJSON(true)['data'];
        $this->assertNotEmpty($counties);

        // 2. Select Taipei City and get its districts
        $taipeiCode = '63';
        $districtsResult = $this->get("/api/locations/districts/{$taipeiCode}");
        $districtsResult->assertStatus(200);
        $districts = $districtsResult->getJSON(true)['data'];
        $this->assertNotEmpty($districts);

        // 3. Select Zhongzheng District and get its sections
        $zhongzhengCode = '100';
        $sectionsResult = $this->get("/api/locations/sections/{$taipeiCode}/{$zhongzhengCode}");
        $sectionsResult->assertStatus(200);
        $sections = $sectionsResult->getJSON(true)['data'];
        $this->assertNotEmpty($sections);

        // 4. Verify the flow worked correctly
        $this->assertContains('城中段', array_column($sections, 'section_name'));
    }
}