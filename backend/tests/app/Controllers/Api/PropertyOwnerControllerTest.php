<?php

namespace Tests\app\Controllers\Api;

use Tests\DatabaseTestCase;

/**
 * Test suite for Property Owner API endpoints
 *
 * Following TDD principles with comprehensive test coverage for:
 * - GET /api/property-owners (index)
 * - GET /api/property-owners/{id} (show)
 * - POST /api/property-owners (create)
 * - PUT /api/property-owners/{id} (update)
 * - DELETE /api/property-owners/{id} (delete)
 * - GET /api/urban-renewals/{id}/property-owners (getByUrbanRenewal)
 */
class PropertyOwnerControllerTest extends DatabaseTestCase
{
    /**
     * Test data provider for valid property owner data
     */
    public function validPropertyOwnerDataProvider(): array
    {
        return [
            'complete_data' => [
                [
                    'urban_renewal_id' => 1,
                    'owner_name' => 'Test Property Owner',
                    'identity_number' => 'A123456789',
                    'phone1' => '02-11111111',
                    'phone2' => '0912345678',
                    'contact_address' => 'Contact Address 123',
                    'registered_address' => 'Registered Address 456',
                    'exclusion_type' => '法院囑託查封',
                    'notes' => 'Test notes',
                    'buildings' => [
                        [
                            'county' => '臺北市',
                            'district' => '中正區',
                            'section' => '城中段',
                            'building_number_main' => '123',
                            'building_number_sub' => '4',
                            'building_area' => 50.5,
                            'building_address' => 'Building Address',
                            'ownership_numerator' => 1,
                            'ownership_denominator' => 2
                        ]
                    ],
                    'lands' => [
                        [
                            'plot_number' => '123',
                            'ownership_numerator' => 1,
                            'ownership_denominator' => 3
                        ]
                    ]
                ]
            ],
            'minimal_data' => [
                [
                    'urban_renewal_id' => 1,
                    'owner_name' => 'Minimal Owner'
                ]
            ],
            'with_exclusion_type' => [
                [
                    'urban_renewal_id' => 1,
                    'owner_name' => 'Excluded Owner',
                    'identity_number' => 'B987654321',
                    'exclusion_type' => '假扣押'
                ]
            ]
        ];
    }

    /**
     * Test data provider for invalid property owner data
     */
    public function invalidPropertyOwnerDataProvider(): array
    {
        return [
            'missing_urban_renewal_id' => [
                [
                    'owner_name' => 'Test Owner'
                ],
                'urban_renewal_id'
            ],
            'invalid_urban_renewal_id' => [
                [
                    'urban_renewal_id' => 'invalid',
                    'owner_name' => 'Test Owner'
                ],
                'urban_renewal_id'
            ],
            'missing_owner_name' => [
                [
                    'urban_renewal_id' => 1
                ],
                'owner_name'
            ],
            'empty_owner_name' => [
                [
                    'urban_renewal_id' => 1,
                    'owner_name' => ''
                ],
                'owner_name'
            ],
            'owner_name_too_long' => [
                [
                    'urban_renewal_id' => 1,
                    'owner_name' => str_repeat('A', 101)
                ],
                'owner_name'
            ],
            'invalid_exclusion_type' => [
                [
                    'urban_renewal_id' => 1,
                    'owner_name' => 'Test Owner',
                    'exclusion_type' => 'invalid_type'
                ],
                'exclusion_type'
            ],
            'nonexistent_urban_renewal' => [
                [
                    'urban_renewal_id' => 999,
                    'owner_name' => 'Test Owner'
                ],
                'urban_renewal_id'
            ]
        ];
    }

    /**
     * Setup for property owner tests
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Insert required urban renewal for foreign key constraint
        $this->insertSampleData();

        // Insert sample land plot for land ownership tests
        $db = \Config\Database::connect();
        $db->table('land_plots')->insert($this->landPlotData);
    }

    // ===================== GET /api/property-owners (Index) Tests =====================

    /**
     * @test
     * Test getting all property owners returns proper structure
     */
    public function test_index_returns_proper_response_structure(): void
    {
        $db = \Config\Database::connect();
        $db->table('property_owners')->insert($this->propertyOwnerData);

        $result = $this->get('/api/property-owners');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertIsArray($response['data']);
        $this->assertGreaterThan(0, count($response['data']));
    }

    /**
     * @test
     * Test getting property owners with empty database
     */
    public function test_index_returns_empty_array_when_no_data(): void
    {
        $result = $this->get('/api/property-owners');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEmpty($response['data']);
    }

    // ===================== GET /api/property-owners/{id} (Show) Tests =====================

    /**
     * @test
     * Test getting specific property owner by valid ID
     */
    public function test_show_returns_property_owner_when_valid_id(): void
    {
        $db = \Config\Database::connect();
        $db->table('property_owners')->insert($this->propertyOwnerData);

        $result = $this->get('/api/property-owners/1');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertIsArray($response['data']);
        $this->assertEquals(1, $response['data']['id']);
        $this->assertEquals($this->propertyOwnerData['name'], $response['data']['name']);
        $this->assertArrayHasKey('buildings', $response['data']);
        $this->assertArrayHasKey('lands', $response['data']);
    }

    /**
     * @test
     * Test getting property owner with invalid ID returns 404
     */
    public function test_show_returns_404_when_property_owner_not_found(): void
    {
        $result = $this->get('/api/property-owners/999');

        $result->assertStatus(404);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('not found', $response['message']);
    }

    /**
     * @test
     * Test getting property owner with non-numeric ID returns 400
     */
    public function test_show_returns_400_when_invalid_id_format(): void
    {
        $result = $this->get('/api/property-owners/invalid');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('Invalid', $response['message']);
    }

    // ===================== POST /api/property-owners (Create) Tests =====================

    /**
     * @test
     * @dataProvider validPropertyOwnerDataProvider
     * Test creating property owner with valid data
     */
    public function test_create_property_owner_with_valid_data(array $propertyOwnerData): void
    {
        $result = $this->postJson('/api/property-owners', $propertyOwnerData);

        $result->assertStatus(201);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertIsArray($response['data']);
        $this->assertEquals($propertyOwnerData['owner_name'], $response['data']['name']);
        $this->assertEquals($propertyOwnerData['urban_renewal_id'], $response['data']['urban_renewal_id']);

        // Verify data was actually stored in database
        $this->seeInDatabase('property_owners', [
            'name' => $propertyOwnerData['owner_name'],
            'urban_renewal_id' => $propertyOwnerData['urban_renewal_id']
        ]);

        // If buildings were provided, verify they were created
        if (isset($propertyOwnerData['buildings'])) {
            $this->seeInDatabase('buildings', [
                'building_number_main' => $propertyOwnerData['buildings'][0]['building_number_main']
            ]);
            $this->seeInDatabase('owner_building_ownerships', [
                'property_owner_id' => $response['data']['id']
            ]);
        }

        // If lands were provided, verify ownership was created
        if (isset($propertyOwnerData['lands'])) {
            $this->seeInDatabase('owner_land_ownerships', [
                'property_owner_id' => $response['data']['id']
            ]);
        }
    }

    /**
     * @test
     * @dataProvider invalidPropertyOwnerDataProvider
     * Test creating property owner with invalid data returns validation errors
     */
    public function test_create_property_owner_with_invalid_data(array $invalidData, string $expectedErrorField): void
    {
        $result = $this->postJson('/api/property-owners', $invalidData);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');

        // For foreign key violations, the response might be different
        if ($expectedErrorField === 'urban_renewal_id' && isset($invalidData['urban_renewal_id']) && $invalidData['urban_renewal_id'] === 999) {
            // This might be a foreign key constraint error
            $this->assertStringContainsString('Failed', $response['message']);
        } else {
            $this->assertArrayHasKey('errors', $response);
            $this->assertArrayHasKey($expectedErrorField, $response['errors']);
        }

        // Verify no data was stored in database
        if (isset($invalidData['owner_name'])) {
            $this->dontSeeInDatabase('property_owners', ['name' => $invalidData['owner_name']]);
        }
    }

    /**
     * @test
     * Test creating property owner with empty request body
     */
    public function test_create_property_owner_with_empty_body(): void
    {
        $result = $this->postJson('/api/property-owners', []);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('No data provided', $response['message']);
    }

    /**
     * @test
     * Test creating property owner with malformed JSON
     */
    public function test_create_property_owner_with_malformed_json(): void
    {
        $result = $this->withHeaders(['Content-Type' => 'application/json'])
                       ->post('/api/property-owners', 'invalid json');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    /**
     * @test
     * Test transaction rollback on building creation failure
     */
    public function test_create_property_owner_transaction_rollback_on_error(): void
    {
        $invalidBuildingData = [
            'urban_renewal_id' => 1,
            'owner_name' => 'Test Owner',
            'buildings' => [
                [
                    'county' => '', // Invalid empty county
                    'district' => '',
                    'section' => '',
                    'building_number_main' => '',
                    'ownership_numerator' => 1,
                    'ownership_denominator' => 1
                ]
            ]
        ];

        $result = $this->postJson('/api/property-owners', $invalidBuildingData);

        // If transaction works properly, no property owner should be created
        $this->dontSeeInDatabase('property_owners', ['name' => 'Test Owner']);
    }

    // ===================== PUT /api/property-owners/{id} (Update) Tests =====================

    /**
     * @test
     * Test updating property owner with valid data
     */
    public function test_update_property_owner_with_valid_data(): void
    {
        $db = \Config\Database::connect();
        $db->table('property_owners')->insert($this->propertyOwnerData);

        $updateData = [
            'owner_name' => 'Updated Property Owner',
            'identity_number' => 'B987654321',
            'phone1' => '02-99999999',
            'contact_address' => 'Updated Contact Address'
        ];

        $result = $this->putJson('/api/property-owners/1', $updateData);

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEquals($updateData['owner_name'], $response['data']['name']);
        $this->assertEquals($updateData['identity_number'], $response['data']['id_number']);

        // Verify data was actually updated in database
        $this->seeInDatabase('property_owners', [
            'id' => 1,
            'name' => $updateData['owner_name'],
            'id_number' => $updateData['identity_number']
        ]);
    }

    /**
     * @test
     * Test updating property owner with buildings and lands
     */
    public function test_update_property_owner_with_buildings_and_lands(): void
    {
        $db = \Config\Database::connect();
        $db->table('property_owners')->insert($this->propertyOwnerData);

        $updateData = [
            'owner_name' => 'Updated Owner',
            'buildings' => [
                [
                    'county' => '臺北市',
                    'district' => '中正區',
                    'section' => '城中段',
                    'building_number_main' => '999',
                    'building_number_sub' => '1',
                    'building_area' => 75.5,
                    'ownership_numerator' => 1,
                    'ownership_denominator' => 1
                ]
            ],
            'lands' => [
                [
                    'plot_number' => '123',
                    'ownership_numerator' => 2,
                    'ownership_denominator' => 3
                ]
            ]
        ];

        $result = $this->putJson('/api/property-owners/1', $updateData);

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);

        // Verify building was created
        $this->seeInDatabase('buildings', [
            'building_number_main' => '999'
        ]);

        // Verify ownership relationships
        $this->seeInDatabase('owner_building_ownerships', [
            'property_owner_id' => 1,
            'ownership_numerator' => 1,
            'ownership_denominator' => 1
        ]);

        $this->seeInDatabase('owner_land_ownerships', [
            'property_owner_id' => 1,
            'ownership_numerator' => 2,
            'ownership_denominator' => 3
        ]);
    }

    /**
     * @test
     * Test updating non-existent property owner returns 404
     */
    public function test_update_nonexistent_property_owner_returns_404(): void
    {
        $updateData = ['owner_name' => 'Updated Name'];

        $result = $this->putJson('/api/property-owners/999', $updateData);

        $result->assertStatus(404);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('not found', $response['message']);
    }

    /**
     * @test
     * Test updating property owner with invalid data returns validation errors
     */
    public function test_update_property_owner_with_invalid_data(): void
    {
        $db = \Config\Database::connect();
        $db->table('property_owners')->insert($this->propertyOwnerData);

        $invalidData = [
            'owner_name' => '', // Empty name should fail validation
            'exclusion_type' => 'invalid_type' // Invalid exclusion type
        ];

        $result = $this->putJson('/api/property-owners/1', $invalidData);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    /**
     * @test
     * Test updating property owner without ID returns 400
     */
    public function test_update_property_owner_without_id_returns_400(): void
    {
        $result = $this->putJson('/api/property-owners/', ['owner_name' => 'Test']);

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    // ===================== DELETE /api/property-owners/{id} (Delete) Tests =====================

    /**
     * @test
     * Test deleting existing property owner
     */
    public function test_delete_existing_property_owner(): void
    {
        $db = \Config\Database::connect();
        $db->table('property_owners')->insert($this->propertyOwnerData);

        // Add some ownership relationships
        $db->table('buildings')->insert([
            'urban_renewal_id' => 1,
            'county' => '臺北市',
            'district' => '中正區',
            'section' => '城中段',
            'building_number_main' => '123',
            'building_number_sub' => '4'
        ]);

        $db->table('owner_building_ownerships')->insert([
            'property_owner_id' => 1,
            'building_id' => 1,
            'ownership_numerator' => 1,
            'ownership_denominator' => 2
        ]);

        $result = $this->delete('/api/property-owners/1');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertStringContainsString('successfully', $response['message']);

        // Verify soft delete - record should still exist but with deleted_at set
        $this->seeInDatabase('property_owners', ['id' => 1]);
        $this->notSeeInDatabase('property_owners', ['id' => 1, 'deleted_at' => null]);

        // Verify ownership relationships are deleted
        $this->dontSeeInDatabase('owner_building_ownerships', ['property_owner_id' => 1]);
    }

    /**
     * @test
     * Test deleting non-existent property owner returns 404
     */
    public function test_delete_nonexistent_property_owner_returns_404(): void
    {
        $result = $this->delete('/api/property-owners/999');

        $result->assertStatus(404);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('not found', $response['message']);
    }

    /**
     * @test
     * Test deleting property owner without ID returns 400
     */
    public function test_delete_property_owner_without_id_returns_400(): void
    {
        $result = $this->delete('/api/property-owners/');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    /**
     * @test
     * Test deleting property owner with invalid ID format returns 400
     */
    public function test_delete_property_owner_with_invalid_id_returns_400(): void
    {
        $result = $this->delete('/api/property-owners/invalid');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
    }

    // ===================== GET /api/urban-renewals/{id}/property-owners Tests =====================

    /**
     * @test
     * Test getting property owners by urban renewal ID
     */
    public function test_get_property_owners_by_urban_renewal_id(): void
    {
        $db = \Config\Database::connect();

        // Insert multiple property owners for same urban renewal
        $owner1 = $this->propertyOwnerData;
        $owner1['name'] = 'Owner 1';
        $db->table('property_owners')->insert($owner1);

        $owner2 = $this->propertyOwnerData;
        $owner2['name'] = 'Owner 2';
        $db->table('property_owners')->insert($owner2);

        // Insert property owner for different urban renewal
        $db->table('urban_renewals')->insert([
            'name' => 'Another Urban Renewal',
            'area' => 2000.0,
            'member_count' => 20,
            'chairman_name' => 'Another Chairman',
            'chairman_phone' => '02-87654321'
        ]);

        $owner3 = $this->propertyOwnerData;
        $owner3['name'] = 'Owner 3';
        $owner3['urban_renewal_id'] = 2;
        $db->table('property_owners')->insert($owner3);

        $result = $this->get('/api/urban-renewals/1/property-owners');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertCount(2, $response['data']);

        // Verify only owners from urban renewal 1 are returned
        foreach ($response['data'] as $owner) {
            $this->assertEquals(1, $owner['urban_renewal_id']);
        }
    }

    /**
     * @test
     * Test getting property owners for non-existent urban renewal
     */
    public function test_get_property_owners_for_nonexistent_urban_renewal(): void
    {
        $result = $this->get('/api/urban-renewals/999/property-owners');

        $result->assertStatus(200);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response);
        $this->assertEmpty($response['data']);
    }

    /**
     * @test
     * Test getting property owners with invalid urban renewal ID
     */
    public function test_get_property_owners_with_invalid_urban_renewal_id(): void
    {
        $result = $this->get('/api/urban-renewals/invalid/property-owners');

        $result->assertStatus(400);
        $response = $result->getJSON(true);
        $this->assertApiResponseStructure($response, 'error');
        $this->assertStringContainsString('Invalid', $response['message']);
    }

    // ===================== CORS and Options Tests =====================

    /**
     * @test
     * Test OPTIONS request for CORS preflight
     */
    public function test_options_request_returns_cors_headers(): void
    {
        $result = $this->options('/api/property-owners');

        $result->assertStatus(200);
        $result->assertHeader('Access-Control-Allow-Origin', '*');
        $result->assertHeader('Access-Control-Allow-Methods');
        $result->assertHeader('Access-Control-Allow-Headers');
    }

    // ===================== Business Logic Tests =====================

    /**
     * @test
     * Test owner code generation is unique
     */
    public function test_owner_code_generation_is_unique(): void
    {
        $ownerData1 = [
            'urban_renewal_id' => 1,
            'owner_name' => 'Owner 1'
        ];

        $ownerData2 = [
            'urban_renewal_id' => 1,
            'owner_name' => 'Owner 2'
        ];

        $result1 = $this->postJson('/api/property-owners', $ownerData1);
        $result2 = $this->postJson('/api/property-owners', $ownerData2);

        if ($result1->getStatusCode() === 201 && $result2->getStatusCode() === 201) {
            $response1 = $result1->getJSON(true);
            $response2 = $result2->getJSON(true);

            $this->assertNotEquals($response1['data']['owner_code'], $response2['data']['owner_code']);
        }
    }

    /**
     * @test
     * Test property owner creation with duplicate owner code fails
     */
    public function test_duplicate_owner_code_fails(): void
    {
        $ownerData = [
            'urban_renewal_id' => 1,
            'owner_name' => 'Test Owner',
            'owner_code' => 'DUPLICATE_CODE'
        ];

        // Create first owner
        $this->postJson('/api/property-owners', $ownerData);

        // Try to create second owner with same code
        $result = $this->postJson('/api/property-owners', $ownerData);

        // Should fail due to unique constraint
        $this->assertNotEquals(201, $result->getStatusCode());
    }

    /**
     * @test
     * Test property owner with exclusion types
     */
    public function test_property_owner_exclusion_types(): void
    {
        $validExclusionTypes = ['法院囑託查封', '假扣押', '假處分', '破產登記', '未經繼承'];

        foreach ($validExclusionTypes as $exclusionType) {
            $ownerData = [
                'urban_renewal_id' => 1,
                'owner_name' => "Owner with {$exclusionType}",
                'exclusion_type' => $exclusionType
            ];

            $result = $this->postJson('/api/property-owners', $ownerData);

            $result->assertStatus(201);
            $response = $result->getJSON(true);
            $this->assertEquals($exclusionType, $response['data']['exclusion_type']);
        }
    }

    // ===================== Integration Tests =====================

    /**
     * @test
     * Test complete property owner lifecycle
     */
    public function test_complete_property_owner_lifecycle(): void
    {
        // Create property owner
        $createData = [
            'urban_renewal_id' => 1,
            'owner_name' => 'Lifecycle Test Owner',
            'identity_number' => 'A123456789',
            'phone1' => '02-11111111'
        ];

        $createResult = $this->postJson('/api/property-owners', $createData);
        $createResult->assertStatus(201);
        $createdOwner = $createResult->getJSON(true)['data'];

        // Read property owner
        $readResult = $this->get("/api/property-owners/{$createdOwner['id']}");
        $readResult->assertStatus(200);
        $readOwner = $readResult->getJSON(true)['data'];
        $this->assertEquals($createData['owner_name'], $readOwner['name']);

        // Update property owner
        $updateData = ['owner_name' => 'Updated Lifecycle Owner'];
        $updateResult = $this->putJson("/api/property-owners/{$createdOwner['id']}", $updateData);
        $updateResult->assertStatus(200);
        $updatedOwner = $updateResult->getJSON(true)['data'];
        $this->assertEquals($updateData['owner_name'], $updatedOwner['name']);

        // Delete property owner
        $deleteResult = $this->delete("/api/property-owners/{$createdOwner['id']}");
        $deleteResult->assertStatus(200);

        // Verify deletion
        $verifyResult = $this->get("/api/property-owners/{$createdOwner['id']}");
        $verifyResult->assertStatus(404);
    }
}