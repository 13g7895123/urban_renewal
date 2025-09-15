<?php

namespace Tests\app\Controllers\Api;

use Tests\DatabaseTestCase;

/**
 * Test suite for Land Plot API endpoints
 *
 * Following TDD principles with comprehensive test coverage for:
 * - GET /api/urban-renewals/{id}/land-plots (index)
 * - GET /api/land-plots/{id} (show)
 * - POST /api/urban-renewals/{id}/land-plots (create)
 * - PUT /api/land-plots/{id} (update)
 * - DELETE /api/land-plots/{id} (delete)
 * - PUT /api/land-plots/{id}/representative (setRepresentative)
 */
class LandPlotControllerTest extends DatabaseTestCase
{
    /**
     * Test data provider for valid land plot data
     */
    public function validLandPlotDataProvider(): array
    {
        return [
            'complete_data' => [
                [
                    'county' => '臺北市',
                    'district' => '中正區',
                    'section' => '城中段',
                    'land_number_main' => '123',
                    'land_number_sub' => '4',
                    'total_area' => 500.75,
                    'public_land_area' => 100.25,
                    'private_land_area' => 400.50,
                    'notes' => 'Test land plot notes'
                ]
            ],
            'minimal_data' => [
                [
                    'county' => '臺北市',
                    'district' => '中正區',
                    'section' => '城中段',
                    'land_number_main' => '456',
                    'land_number_sub' => '0'
                ]
            ],
            'zero_sub_number' => [
                [
                    'county' => '新北市',
                    'district' => '板橋區',
                    'section' => '板橋段',
                    'land_number_main' => '789',
                    'land_number_sub' => '0',
                    'total_area' => 1000.0
                ]
            ]
        ];
    }

    /**
     * Test data provider for invalid land plot data
     */
    public function invalidLandPlotDataProvider(): array
    {
        return [
            'missing_county' => [
                [
                    'district' => '中正區',
                    'section' => '城中段',
                    'land_number_main' => '123'
                ],
                'county'
            ],
            'empty_county' => [
                [
                    'county' => '',
                    'district' => '中正區',
                    'section' => '城中段',
                    'land_number_main' => '123'
                ],
                'county'
            ],
            'missing_district' => [
                [
                    'county' => '臺北市',
                    'section' => '城中段',
                    'land_number_main' => '123'
                ],
                'district'
            ],
            'missing_section' => [
                [
                    'county' => '臺北市',
                    'district' => '中正區',
                    'land_number_main' => '123'
                ],
                'section'
            ],
            'missing_land_number_main' => [
                [
                    'county' => '臺北市',
                    'district' => '中正區',
                    'section' => '城中段'
                ],
                'land_number_main'
            ],
            'empty_land_number_main' => [
                [
                    'county' => '臺北市',
                    'district' => '中正區',
                    'section' => '城中段',
                    'land_number_main' => ''
                ],
                'land_number_main'
            ],
            'negative_total_area' => [
                [
                    'county' => '臺北市',
                    'district' => '中正區',
                    'section' => '城中段',
                    'land_number_main' => '123',
                    'total_area' => -100.0
                ],
                'total_area'
            ],
            'negative_public_area' => [
                [
                    'county' => '臺北市',
                    'district' => '中正區',
                    'section' => '城中段',
                    'land_number_main' => '123',
                    'public_land_area' => -50.0
                ],
                'public_land_area'
            ]
        ];
    }

    /**
     * Setup for land plot tests
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Insert required urban renewal for foreign key constraint
        $this->insertSampleData();
    }

    // ===================== GET /api/urban-renewals/{id}/land-plots (Index) Tests =====================

    /**
     * @test
     * Test getting land plots by urban renewal ID returns proper structure
     */
    public function test_index_returns_proper_response_structure(): void
    {
        $db = \Config\Database::connect();
        $db->table('land_plots')->insert($this->landPlotData);

        $result = $this->get('/api/urban-renewals/1/land-plots');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertIsArray($response['data']);
        $this->assertGreaterThan(0, count($response['data']));
    }

    /**
     * @test
     * Test getting land plots with empty database
     */
    public function test_index_returns_empty_array_when_no_data(): void
    {
        $result = $this->get('/api/urban-renewals/1/land-plots');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEmpty($response['data']);
    }

    /**
     * @test
     * Test getting land plots for non-existent urban renewal
     */
    public function test_index_returns_404_for_nonexistent_urban_renewal(): void
    {
        $result = $this->get('/api/urban-renewals/999/land-plots');

        $result->assertStatus(404);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('找不到', $response['message']);
    }

    /**
     * @test
     * Test getting land plots without urban renewal ID returns 400
     */
    public function test_index_returns_400_when_no_urban_renewal_id(): void
    {
        $result = $this->get('/api/urban-renewals//land-plots');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('缺少', $response['message']);
    }

    /**
     * @test
     * Test getting land plots with invalid urban renewal ID format
     */
    public function test_index_returns_400_with_invalid_urban_renewal_id(): void
    {
        $result = $this->get('/api/urban-renewals/invalid/land-plots');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    // ===================== GET /api/land-plots/{id} (Show) Tests =====================

    /**
     * @test
     * Test getting specific land plot by valid ID
     */
    public function test_show_returns_land_plot_when_valid_id(): void
    {
        $db = \Config\Database::connect();
        $db->table('land_plots')->insert($this->landPlotData);

        $result = $this->get('/api/land-plots/1');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertIsArray($response['data']);
        $this->assertEquals(1, $response['data']['id']);
        $this->assertEquals($this->landPlotData['county'], $response['data']['county']);
        $this->assertEquals($this->landPlotData['land_number_main'], $response['data']['land_number_main']);
    }

    /**
     * @test
     * Test getting land plot with invalid ID returns 404
     */
    public function test_show_returns_404_when_land_plot_not_found(): void
    {
        $result = $this->get('/api/land-plots/999');

        $result->assertStatus(404);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('找不到', $response['message']);
    }

    /**
     * @test
     * Test getting land plot with non-numeric ID returns 400
     */
    public function test_show_returns_400_when_invalid_id_format(): void
    {
        $result = $this->get('/api/land-plots/invalid');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    /**
     * @test
     * Test getting land plot without ID returns 400
     */
    public function test_show_returns_400_when_no_id_provided(): void
    {
        $result = $this->get('/api/land-plots/');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    // ===================== POST /api/urban-renewals/{id}/land-plots (Create) Tests =====================

    /**
     * @test
     * @dataProvider validLandPlotDataProvider
     * Test creating land plot with valid data
     */
    public function test_create_land_plot_with_valid_data(array $landPlotData): void
    {
        $result = $this->postJson('/api/urban-renewals/1/land-plots', $landPlotData);

        $result->assertStatus(201);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertIsArray($response['data']);
        $this->assertEquals($landPlotData['county'], $response['data']['county']);
        $this->assertEquals($landPlotData['land_number_main'], $response['data']['land_number_main']);
        $this->assertEquals(1, $response['data']['urban_renewal_id']);

        // Verify data was actually stored in database
        $this->seeInDatabase('land_plots', [
            'county' => $landPlotData['county'],
            'land_number_main' => $landPlotData['land_number_main'],
            'urban_renewal_id' => 1
        ]);
    }

    /**
     * @test
     * @dataProvider invalidLandPlotDataProvider
     * Test creating land plot with invalid data returns validation errors
     */
    public function test_create_land_plot_with_invalid_data(array $invalidData, string $expectedErrorField): void
    {
        $result = $this->postJson('/api/urban-renewals/1/land-plots', $invalidData);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey($expectedErrorField, $response['errors']);

        // Verify no data was stored in database
        $this->dontSeeInDatabase('land_plots', ['county' => $invalidData['county'] ?? 'nonexistent']);
    }

    /**
     * @test
     * Test creating land plot for non-existent urban renewal
     */
    public function test_create_land_plot_for_nonexistent_urban_renewal(): void
    {
        $landPlotData = [
            'county' => '臺北市',
            'district' => '中正區',
            'section' => '城中段',
            'land_number_main' => '123'
        ];

        $result = $this->postJson('/api/urban-renewals/999/land-plots', $landPlotData);

        $result->assertStatus(404);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('找不到', $response['message']);
    }

    /**
     * @test
     * Test creating land plot with empty request body
     */
    public function test_create_land_plot_with_empty_body(): void
    {
        $result = $this->postJson('/api/urban-renewals/1/land-plots', []);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * @test
     * Test creating duplicate land plot (same main and sub numbers) should fail
     */
    public function test_create_duplicate_land_plot_fails(): void
    {
        $db = \Config\Database::connect();
        $db->table('land_plots')->insert($this->landPlotData);

        $duplicateData = [
            'county' => $this->landPlotData['county'],
            'district' => $this->landPlotData['district'],
            'section' => $this->landPlotData['section'],
            'land_number_main' => $this->landPlotData['land_number_main'],
            'land_number_sub' => $this->landPlotData['land_number_sub']
        ];

        $result = $this->postJson('/api/urban-renewals/1/land-plots', $duplicateData);

        // Should fail due to unique constraint
        $this->assertNotEquals(201, $result->getStatusCode());
    }

    // ===================== PUT /api/land-plots/{id} (Update) Tests =====================

    /**
     * @test
     * Test updating land plot with valid data
     */
    public function test_update_land_plot_with_valid_data(): void
    {
        $db = \Config\Database::connect();
        $db->table('land_plots')->insert($this->landPlotData);

        $updateData = [
            'county' => '新北市',
            'district' => '板橋區',
            'section' => '板橋段',
            'land_number_main' => '999',
            'land_number_sub' => '1',
            'total_area' => 800.5,
            'notes' => 'Updated notes'
        ];

        $result = $this->putJson('/api/land-plots/1', $updateData);

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEquals($updateData['county'], $response['data']['county']);
        $this->assertEquals($updateData['land_number_main'], $response['data']['land_number_main']);

        // Verify data was actually updated in database
        $this->seeInDatabase('land_plots', [
            'id' => 1,
            'county' => $updateData['county'],
            'land_number_main' => $updateData['land_number_main']
        ]);
    }

    /**
     * @test
     * Test updating land plot with partial data
     */
    public function test_update_land_plot_with_partial_data(): void
    {
        $db = \Config\Database::connect();
        $db->table('land_plots')->insert($this->landPlotData);

        $updateData = [
            'total_area' => 999.99,
            'notes' => 'Partially updated notes'
        ];

        $result = $this->putJson('/api/land-plots/1', $updateData);

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertEquals($updateData['total_area'], (float)$response['data']['total_area']);
        $this->assertEquals($updateData['notes'], $response['data']['notes']);

        // Other fields should remain unchanged
        $this->assertEquals($this->landPlotData['county'], $response['data']['county']);
    }

    /**
     * @test
     * Test updating non-existent land plot returns 404
     */
    public function test_update_nonexistent_land_plot_returns_404(): void
    {
        $updateData = ['total_area' => 500.0];

        $result = $this->putJson('/api/land-plots/999', $updateData);

        $result->assertStatus(404);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('找不到', $response['message']);
    }

    /**
     * @test
     * Test updating land plot with invalid data returns validation errors
     */
    public function test_update_land_plot_with_invalid_data(): void
    {
        $db = \Config\Database::connect();
        $db->table('land_plots')->insert($this->landPlotData);

        $invalidData = [
            'county' => '', // Empty county should fail validation
            'total_area' => -100 // Negative area should fail validation
        ];

        $result = $this->putJson('/api/land-plots/1', $invalidData);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertArrayHasKey('errors', $response);
    }

    /**
     * @test
     * Test updating land plot without ID returns 400
     */
    public function test_update_land_plot_without_id_returns_400(): void
    {
        $result = $this->putJson('/api/land-plots/', ['total_area' => 500.0]);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    // ===================== DELETE /api/land-plots/{id} (Delete) Tests =====================

    /**
     * @test
     * Test deleting existing land plot
     */
    public function test_delete_existing_land_plot(): void
    {
        $db = \Config\Database::connect();
        $db->table('land_plots')->insert($this->landPlotData);

        $result = $this->delete('/api/land-plots/1');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertStringContainsString('刪除成功', $response['message']);

        // Verify soft delete - record should still exist but with deleted_at set
        $this->seeInDatabase('land_plots', ['id' => 1]);
        $this->notSeeInDatabase('land_plots', ['id' => 1, 'deleted_at' => null]);
    }

    /**
     * @test
     * Test deleting non-existent land plot returns 404
     */
    public function test_delete_nonexistent_land_plot_returns_404(): void
    {
        $result = $this->delete('/api/land-plots/999');

        $result->assertStatus(404);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('找不到', $response['message']);
    }

    /**
     * @test
     * Test deleting land plot without ID returns 400
     */
    public function test_delete_land_plot_without_id_returns_400(): void
    {
        $result = $this->delete('/api/land-plots/');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    /**
     * @test
     * Test deleting land plot with invalid ID format returns 400
     */
    public function test_delete_land_plot_with_invalid_id_returns_400(): void
    {
        $result = $this->delete('/api/land-plots/invalid');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    // ===================== PUT /api/land-plots/{id}/representative (Set Representative) Tests =====================

    /**
     * @test
     * Test setting representative for land plot
     */
    public function test_set_representative_for_land_plot(): void
    {
        $db = \Config\Database::connect();
        $db->table('land_plots')->insert($this->landPlotData);
        $db->table('property_owners')->insert($this->propertyOwnerData);

        $representativeData = [
            'representative_id' => 1
        ];

        $result = $this->putJson('/api/land-plots/1/representative', $representativeData);

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEquals(1, $response['data']['representative_id']);

        // Verify data was updated in database
        $this->seeInDatabase('land_plots', [
            'id' => 1,
            'representative_id' => 1
        ]);
    }

    /**
     * @test
     * Test setting representative with non-existent property owner
     */
    public function test_set_representative_with_nonexistent_property_owner(): void
    {
        $db = \Config\Database::connect();
        $db->table('land_plots')->insert($this->landPlotData);

        $representativeData = [
            'representative_id' => 999
        ];

        $result = $this->putJson('/api/land-plots/1/representative', $representativeData);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    /**
     * @test
     * Test removing representative from land plot
     */
    public function test_remove_representative_from_land_plot(): void
    {
        $db = \Config\Database::connect();
        $landPlotWithRep = $this->landPlotData;
        $landPlotWithRep['representative_id'] = 1;
        $db->table('land_plots')->insert($landPlotWithRep);
        $db->table('property_owners')->insert($this->propertyOwnerData);

        $representativeData = [
            'representative_id' => null
        ];

        $result = $this->putJson('/api/land-plots/1/representative', $representativeData);

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertNull($response['data']['representative_id']);

        // Verify data was updated in database
        $this->seeInDatabase('land_plots', [
            'id' => 1,
            'representative_id' => null
        ]);
    }

    /**
     * @test
     * Test setting representative for non-existent land plot
     */
    public function test_set_representative_for_nonexistent_land_plot(): void
    {
        $representativeData = [
            'representative_id' => 1
        ];

        $result = $this->putJson('/api/land-plots/999/representative', $representativeData);

        $result->assertStatus(404);
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
        $result = $this->options('/api/urban-renewals/1/land-plots');

        $result->assertStatus(200);
        $result->assertHeader('Access-Control-Allow-Origin', '*');
        $result->assertHeader('Access-Control-Allow-Methods');
        $result->assertHeader('Access-Control-Allow-Headers');
    }

    // ===================== Business Logic Tests =====================

    /**
     * @test
     * Test land plot numbering system
     */
    public function test_land_plot_numbering_system(): void
    {
        $landPlotData1 = [
            'county' => '臺北市',
            'district' => '中正區',
            'section' => '城中段',
            'land_number_main' => '123',
            'land_number_sub' => '0'
        ];

        $landPlotData2 = [
            'county' => '臺北市',
            'district' => '中正區',
            'section' => '城中段',
            'land_number_main' => '123',
            'land_number_sub' => '1'
        ];

        $result1 = $this->postJson('/api/urban-renewals/1/land-plots', $landPlotData1);
        $result2 = $this->postJson('/api/urban-renewals/1/land-plots', $landPlotData2);

        $result1->assertStatus(201);
        $result2->assertStatus(201);

        // Both should be created successfully as they have different sub numbers
        $response1 = $result1->getJSON(true);
        $response2 = $result2->getJSON(true);

        $this->assertEquals('123', $response1['data']['land_number_main']);
        $this->assertEquals('0', $response1['data']['land_number_sub']);
        $this->assertEquals('123', $response2['data']['land_number_main']);
        $this->assertEquals('1', $response2['data']['land_number_sub']);
    }

    /**
     * @test
     * Test area calculations consistency
     */
    public function test_area_calculations_consistency(): void
    {
        $landPlotData = [
            'county' => '臺北市',
            'district' => '中正區',
            'section' => '城中段',
            'land_number_main' => '123',
            'total_area' => 1000.0,
            'public_land_area' => 300.0,
            'private_land_area' => 700.0
        ];

        $result = $this->postJson('/api/urban-renewals/1/land-plots', $landPlotData);

        $result->assertStatus(201);
        $response = $result->getJSON(true);

        // Verify area calculations are preserved
        $this->assertEquals(1000.0, (float)$response['data']['total_area']);
        $this->assertEquals(300.0, (float)$response['data']['public_land_area']);
        $this->assertEquals(700.0, (float)$response['data']['private_land_area']);

        // In a real system, you might want to validate that public + private = total
        $calculatedTotal = (float)$response['data']['public_land_area'] + (float)$response['data']['private_land_area'];
        $this->assertEquals((float)$response['data']['total_area'], $calculatedTotal);
    }

    // ===================== Integration Tests =====================

    /**
     * @test
     * Test complete land plot lifecycle
     */
    public function test_complete_land_plot_lifecycle(): void
    {
        // Create land plot
        $createData = [
            'county' => '臺北市',
            'district' => '中正區',
            'section' => '城中段',
            'land_number_main' => '999',
            'land_number_sub' => '0',
            'total_area' => 500.0
        ];

        $createResult = $this->postJson('/api/urban-renewals/1/land-plots', $createData);
        $createResult->assertStatus(201);
        $createdLandPlot = $createResult->getJSON(true)['data'];

        // Read land plot
        $readResult = $this->get("/api/land-plots/{$createdLandPlot['id']}");
        $readResult->assertStatus(200);
        $readLandPlot = $readResult->getJSON(true)['data'];
        $this->assertEquals($createData['land_number_main'], $readLandPlot['land_number_main']);

        // Update land plot
        $updateData = ['total_area' => 750.0];
        $updateResult = $this->putJson("/api/land-plots/{$createdLandPlot['id']}", $updateData);
        $updateResult->assertStatus(200);
        $updatedLandPlot = $updateResult->getJSON(true)['data'];
        $this->assertEquals($updateData['total_area'], (float)$updatedLandPlot['total_area']);

        // Delete land plot
        $deleteResult = $this->delete("/api/land-plots/{$createdLandPlot['id']}");
        $deleteResult->assertStatus(200);

        // Verify deletion
        $verifyResult = $this->get("/api/land-plots/{$createdLandPlot['id']}");
        $verifyResult->assertStatus(404);
    }

    /**
     * @test
     * Test cascade behavior when urban renewal is deleted
     */
    public function test_cascade_delete_with_urban_renewal(): void
    {
        $db = \Config\Database::connect();
        $db->table('land_plots')->insert($this->landPlotData);

        // Delete the urban renewal (should cascade to land plots)
        $deleteResult = $this->delete('/api/urban-renewals/1');
        $deleteResult->assertStatus(200);

        // Verify land plot is also soft deleted
        $this->notSeeInDatabase('land_plots', ['urban_renewal_id' => 1, 'deleted_at' => null]);
    }
}