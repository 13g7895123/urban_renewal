<?php

namespace Tests\app\Controllers\Api;

use Tests\DatabaseTestCase;

/**
 * Test suite for Urban Renewal API endpoints
 *
 * This test follows TDD principles:
 * 1. RED: Write failing tests first
 * 2. GREEN: Make tests pass with minimal code
 * 3. REFACTOR: Improve code while keeping tests green
 */
class UrbanRenewalControllerTest extends DatabaseTestCase
{
    /**
     * Test data provider for valid urban renewal data
     */
    public function validUrbanRenewalDataProvider(): array
    {
        return [
            'standard_data' => [
                [
                    'name' => 'Test Urban Renewal Group',
                    'area' => 1500.50,
                    'memberCount' => 25,
                    'chairmanName' => 'John Doe',
                    'chairmanPhone' => '02-12345678',
                    'address' => 'Test Address 123',
                    'representative' => 'Jane Smith'
                ]
            ],
            'minimal_data' => [
                [
                    'name' => 'Minimal Group',
                    'area' => 100.0,
                    'memberCount' => 1,
                    'chairmanName' => 'Jane Doe',
                    'chairmanPhone' => '02-87654321'
                ]
            ],
            'large_group' => [
                [
                    'name' => 'Large Urban Renewal Project',
                    'area' => 5000.75,
                    'memberCount' => 150,
                    'chairmanName' => 'Chairman Name',
                    'chairmanPhone' => '02-12345678',
                    'address' => 'Large Project Address',
                    'representative' => 'Representative Name'
                ]
            ]
        ];
    }

    /**
     * Test data provider for invalid urban renewal data
     */
    public function invalidUrbanRenewalDataProvider(): array
    {
        return [
            'missing_name' => [
                [
                    'area' => 1500.50,
                    'memberCount' => 25,
                    'chairmanName' => 'John Doe',
                    'chairmanPhone' => '02-12345678'
                ],
                'name'
            ],
            'empty_name' => [
                [
                    'name' => '',
                    'area' => 1500.50,
                    'memberCount' => 25,
                    'chairmanName' => 'John Doe',
                    'chairmanPhone' => '02-12345678'
                ],
                'name'
            ],
            'name_too_short' => [
                [
                    'name' => 'A',
                    'area' => 1500.50,
                    'memberCount' => 25,
                    'chairmanName' => 'John Doe',
                    'chairmanPhone' => '02-12345678'
                ],
                'name'
            ],
            'missing_area' => [
                [
                    'name' => 'Test Group',
                    'memberCount' => 25,
                    'chairmanName' => 'John Doe',
                    'chairmanPhone' => '02-12345678'
                ],
                'area'
            ],
            'invalid_area_zero' => [
                [
                    'name' => 'Test Group',
                    'area' => 0,
                    'memberCount' => 25,
                    'chairmanName' => 'John Doe',
                    'chairmanPhone' => '02-12345678'
                ],
                'area'
            ],
            'invalid_area_negative' => [
                [
                    'name' => 'Test Group',
                    'area' => -100.5,
                    'memberCount' => 25,
                    'chairmanName' => 'John Doe',
                    'chairmanPhone' => '02-12345678'
                ],
                'area'
            ],
            'missing_member_count' => [
                [
                    'name' => 'Test Group',
                    'area' => 1500.50,
                    'chairmanName' => 'John Doe',
                    'chairmanPhone' => '02-12345678'
                ],
                'member_count'
            ],
            'invalid_member_count_zero' => [
                [
                    'name' => 'Test Group',
                    'area' => 1500.50,
                    'memberCount' => 0,
                    'chairmanName' => 'John Doe',
                    'chairmanPhone' => '02-12345678'
                ],
                'member_count'
            ],
            'missing_chairman_name' => [
                [
                    'name' => 'Test Group',
                    'area' => 1500.50,
                    'memberCount' => 25,
                    'chairmanPhone' => '02-12345678'
                ],
                'chairman_name'
            ],
            'chairman_name_too_short' => [
                [
                    'name' => 'Test Group',
                    'area' => 1500.50,
                    'memberCount' => 25,
                    'chairmanName' => 'A',
                    'chairmanPhone' => '02-12345678'
                ],
                'chairman_name'
            ],
            'missing_chairman_phone' => [
                [
                    'name' => 'Test Group',
                    'area' => 1500.50,
                    'memberCount' => 25,
                    'chairmanName' => 'John Doe'
                ],
                'chairman_phone'
            ],
            'chairman_phone_too_short' => [
                [
                    'name' => 'Test Group',
                    'area' => 1500.50,
                    'memberCount' => 25,
                    'chairmanName' => 'John Doe',
                    'chairmanPhone' => '123'
                ],
                'chairman_phone'
            ]
        ];
    }

    // ===================== GET /api/urban-renewals (Index) Tests =====================

    /**
     * @test
     * Test getting all urban renewals returns proper structure
     */
    public function test_index_returns_proper_response_structure(): void
    {
        $this->insertSampleData();

        $result = $this->get('/api/urban-renewals');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertPaginationStructure($response);
        $this->assertIsArray($response['data']);
    }

    /**
     * @test
     * Test getting urban renewals with empty database
     */
    public function test_index_returns_empty_array_when_no_data(): void
    {
        $result = $this->get('/api/urban-renewals');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEmpty($response['data']);
        $this->assertEquals(0, $response['pagination']['total']);
    }

    /**
     * @test
     * Test pagination works correctly
     */
    public function test_index_pagination_works_correctly(): void
    {
        // Create multiple urban renewals for pagination testing
        $db = \Config\Database::connect();
        for ($i = 1; $i <= 15; $i++) {
            $data = $this->urbanRenewalData;
            $data['name'] = "Urban Renewal $i";
            $db->table('urban_renewals')->insert($data);
        }

        // Test first page
        $result = $this->get('/api/urban-renewals?page=1&per_page=5');
        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertCount(5, $response['data']);
        $this->assertEquals(1, $response['pagination']['current_page']);
        $this->assertEquals(5, $response['pagination']['per_page']);
        $this->assertEquals(15, $response['pagination']['total']);

        // Test second page
        $result = $this->get('/api/urban-renewals?page=2&per_page=5');
        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertCount(5, $response['data']);
        $this->assertEquals(2, $response['pagination']['current_page']);
    }

    /**
     * @test
     * Test search functionality
     */
    public function test_index_search_functionality(): void
    {
        $db = \Config\Database::connect();
        $db->table('urban_renewals')->insert([
            'name' => 'Searchable Urban Renewal',
            'area' => 1000.0,
            'member_count' => 10,
            'chairman_name' => 'Search Test',
            'chairman_phone' => '02-12345678'
        ]);
        $db->table('urban_renewals')->insert([
            'name' => 'Another Group',
            'area' => 2000.0,
            'member_count' => 20,
            'chairman_name' => 'Another Test',
            'chairman_phone' => '02-87654321'
        ]);

        $result = $this->get('/api/urban-renewals?search=Searchable');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertCount(1, $response['data']);
        $this->assertStringContainsString('Searchable', $response['data'][0]['name']);
    }

    // ===================== GET /api/urban-renewals/{id} (Show) Tests =====================

    /**
     * @test
     * Test getting specific urban renewal by valid ID
     */
    public function test_show_returns_urban_renewal_when_valid_id(): void
    {
        $this->insertSampleData();

        $result = $this->get('/api/urban-renewals/1');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertIsArray($response['data']);
        $this->assertEquals(1, $response['data']['id']);
        $this->assertEquals($this->urbanRenewalData['name'], $response['data']['name']);
    }

    /**
     * @test
     * Test getting urban renewal with invalid ID returns 404
     */
    public function test_show_returns_404_when_urban_renewal_not_found(): void
    {
        $result = $this->get('/api/urban-renewals/999');

        $result->assertStatus(404);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('找不到', $response['message']);
    }

    /**
     * @test
     * Test getting urban renewal with non-numeric ID returns 400
     */
    public function test_show_returns_400_when_invalid_id_format(): void
    {
        $result = $this->get('/api/urban-renewals/invalid');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('ID', $response['message']);
    }

    /**
     * @test
     * Test getting urban renewal without ID returns 400
     */
    public function test_show_returns_400_when_no_id_provided(): void
    {
        $result = $this->get('/api/urban-renewals/');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    // ===================== POST /api/urban-renewals (Create) Tests =====================

    /**
     * @test
     * @dataProvider validUrbanRenewalDataProvider
     * Test creating urban renewal with valid data
     */
    public function test_create_urban_renewal_with_valid_data(array $urbanRenewalData): void
    {
        $result = $this->postJson('/api/urban-renewals', $urbanRenewalData);

        $result->assertStatus(201);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertIsArray($response['data']);
        $this->assertEquals($urbanRenewalData['name'], $response['data']['name']);
        $this->assertEquals($urbanRenewalData['area'], (float)$response['data']['area']);

        // Verify data was actually stored in database
        $this->seeInDatabase('urban_renewals', [
            'name' => $urbanRenewalData['name'],
            'area' => $urbanRenewalData['area']
        ]);
    }

    /**
     * @test
     * @dataProvider invalidUrbanRenewalDataProvider
     * Test creating urban renewal with invalid data returns validation errors
     */
    public function test_create_urban_renewal_with_invalid_data(array $invalidData, string $expectedErrorField): void
    {
        $result = $this->postJson('/api/urban-renewals', $invalidData);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey($expectedErrorField, $response['errors']);

        // Verify no data was stored in database
        $this->dontSeeInDatabase('urban_renewals', ['name' => $invalidData['name'] ?? 'nonexistent']);
    }

    /**
     * @test
     * Test creating urban renewal with empty request body
     */
    public function test_create_urban_renewal_with_empty_body(): void
    {
        $result = $this->postJson('/api/urban-renewals', []);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * @test
     * Test creating urban renewal with malformed JSON
     */
    public function test_create_urban_renewal_with_malformed_json(): void
    {
        $result = $this->withHeaders(['Content-Type' => 'application/json'])
                       ->post('/api/urban-renewals', 'invalid json');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    // ===================== PUT /api/urban-renewals/{id} (Update) Tests =====================

    /**
     * @test
     * Test updating urban renewal with valid data
     */
    public function test_update_urban_renewal_with_valid_data(): void
    {
        $this->insertSampleData();

        $updateData = [
            'name' => 'Updated Urban Renewal Name',
            'area' => 2000.75,
            'memberCount' => 30,
            'chairmanName' => 'Updated Chairman',
            'chairmanPhone' => '02-99999999',
            'address' => 'Updated Address',
            'representative' => 'Updated Representative'
        ];

        $result = $this->putJson('/api/urban-renewals/1', $updateData);

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEquals($updateData['name'], $response['data']['name']);
        $this->assertEquals($updateData['area'], (float)$response['data']['area']);

        // Verify data was actually updated in database
        $this->seeInDatabase('urban_renewals', [
            'id' => 1,
            'name' => $updateData['name'],
            'area' => $updateData['area']
        ]);
    }

    /**
     * @test
     * Test updating urban renewal with partial data
     */
    public function test_update_urban_renewal_with_partial_data(): void
    {
        $this->insertSampleData();

        $updateData = [
            'name' => 'Partially Updated Name',
            'area' => 1800.0
        ];

        $result = $this->putJson('/api/urban-renewals/1', $updateData);

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertEquals($updateData['name'], $response['data']['name']);
        $this->assertEquals($updateData['area'], (float)$response['data']['area']);

        // Other fields should remain unchanged
        $this->assertEquals($this->urbanRenewalData['member_count'], $response['data']['member_count']);
    }

    /**
     * @test
     * Test updating non-existent urban renewal returns 404
     */
    public function test_update_nonexistent_urban_renewal_returns_404(): void
    {
        $updateData = ['name' => 'Updated Name'];

        $result = $this->putJson('/api/urban-renewals/999', $updateData);

        $result->assertStatus(404);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('找不到', $response['message']);
    }

    /**
     * @test
     * Test updating urban renewal with invalid data returns validation errors
     */
    public function test_update_urban_renewal_with_invalid_data(): void
    {
        $this->insertSampleData();

        $invalidData = [
            'name' => '', // Empty name should fail validation
            'area' => -100 // Negative area should fail validation
        ];

        $result = $this->putJson('/api/urban-renewals/1', $invalidData);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * @test
     * Test updating urban renewal without ID returns 400
     */
    public function test_update_urban_renewal_without_id_returns_400(): void
    {
        $result = $this->putJson('/api/urban-renewals/', ['name' => 'Test']);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    // ===================== DELETE /api/urban-renewals/{id} (Delete) Tests =====================

    /**
     * @test
     * Test deleting existing urban renewal
     */
    public function test_delete_existing_urban_renewal(): void
    {
        $this->insertSampleData();

        $result = $this->delete('/api/urban-renewals/1');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertStringContainsString('刪除成功', $response['message']);

        // Verify soft delete - record should still exist but with deleted_at set
        $this->seeInDatabase('urban_renewals', ['id' => 1]);
        $this->notSeeInDatabase('urban_renewals', ['id' => 1, 'deleted_at' => null]);
    }

    /**
     * @test
     * Test deleting non-existent urban renewal returns 404
     */
    public function test_delete_nonexistent_urban_renewal_returns_404(): void
    {
        $result = $this->delete('/api/urban-renewals/999');

        $result->assertStatus(404);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('找不到', $response['message']);
    }

    /**
     * @test
     * Test deleting urban renewal without ID returns 400
     */
    public function test_delete_urban_renewal_without_id_returns_400(): void
    {
        $result = $this->delete('/api/urban-renewals/');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    /**
     * @test
     * Test deleting urban renewal with invalid ID format returns 400
     */
    public function test_delete_urban_renewal_with_invalid_id_returns_400(): void
    {
        $result = $this->delete('/api/urban-renewals/invalid');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    // ===================== CORS and Options Tests =====================

    /**
     * @test
     * Test OPTIONS request for CORS preflight
     */
    public function test_options_request_returns_cors_headers(): void
    {
        $result = $this->options('/api/urban-renewals');

        $result->assertStatus(200);
        $result->assertHeader('Access-Control-Allow-Origin', '*');
        $result->assertHeader('Access-Control-Allow-Methods');
        $result->assertHeader('Access-Control-Allow-Headers');
    }

    // ===================== Error Handling Tests =====================

    /**
     * @test
     * Test server error handling
     */
    public function test_server_error_handling(): void
    {
        // This test would require mocking database failure
        // For now, we'll test the error response structure
        $this->markTestIncomplete('Server error handling test requires database mocking');
    }

    /**
     * @test
     * Test response format consistency across all endpoints
     */
    public function test_response_format_consistency(): void
    {
        $this->insertSampleData();

        // Test all endpoints return consistent error format
        $endpoints = [
            ['method' => 'get', 'uri' => '/api/urban-renewals/999'],
            ['method' => 'put', 'uri' => '/api/urban-renewals/999'],
            ['method' => 'delete', 'uri' => '/api/urban-renewals/999']
        ];

        foreach ($endpoints as $endpoint) {
            $result = $this->{$endpoint['method']}($endpoint['uri']);
            $response = $result->getJSON(true);
            $this->assertApiResponseStructure($response, 'error');
        }
    }

    // ===================== Database Constraint Tests =====================

    /**
     * @test
     * Test cascade delete behavior when urban renewal has related records
     */
    public function test_cascade_delete_with_related_records(): void
    {
        $this->insertSampleData();

        // Add related property owner
        $propertyOwnerData = $this->propertyOwnerData;
        $propertyOwnerData['urban_renewal_id'] = 1;
        $db = \Config\Database::connect();
        $db->table('property_owners')->insert($propertyOwnerData);

        $result = $this->delete('/api/urban-renewals/1');

        $result->assertStatus(200);

        // Verify related records are also soft deleted
        $this->notSeeInDatabase('property_owners', ['urban_renewal_id' => 1, 'deleted_at' => null]);
    }

    // ===================== Security Tests =====================

    /**
     * @test
     * Test SQL injection protection
     */
    public function test_sql_injection_protection(): void
    {
        $maliciousData = [
            'name' => "'; DROP TABLE urban_renewals; --",
            'area' => 1000.0,
            'memberCount' => 10,
            'chairmanName' => 'Test',
            'chairmanPhone' => '02-12345678'
        ];

        $result = $this->postJson('/api/urban-renewals', $maliciousData);

        // Even if creation succeeds, table should still exist
        $this->assertTrue($this->db->tableExists('urban_renewals'));
    }

    /**
     * @test
     * Test XSS protection in response data
     */
    public function test_xss_protection(): void
    {
        $xssData = [
            'name' => '<script>alert("xss")</script>',
            'area' => 1000.0,
            'memberCount' => 10,
            'chairmanName' => 'Test',
            'chairmanPhone' => '02-12345678'
        ];

        $result = $this->postJson('/api/urban-renewals', $xssData);

        if ($result->getStatusCode() === 201) {
            $response = $result->getJSON(true);
            // Response should escape or sanitize script tags
            $this->assertNotContains('<script>', $response['data']['name']);
        }
    }
}