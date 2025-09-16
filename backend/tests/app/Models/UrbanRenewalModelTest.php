<?php

namespace Tests\app\Models;

use Tests\DatabaseTestCase;
use App\Models\UrbanRenewalModel;

/**
 * Test suite for Urban Renewal Model
 *
 * Testing model-specific business logic and database operations
 */
class UrbanRenewalModelTest extends DatabaseTestCase
{
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new UrbanRenewalModel();
        $this->insertSampleData();
    }

    /**
     * @test
     * Test model validation rules
     */
    public function test_model_validation_rules(): void
    {
        $validData = $this->urbanRenewalData;
        $result = $this->model->insert($validData);
        $this->assertNotFalse($result);

        // Test required field validation
        $invalidData = $validData;
        unset($invalidData['name']);
        $result = $this->model->insert($invalidData);
        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $this->model->errors());
    }

    /**
     * @test
     * Test search by name functionality
     */
    public function test_search_by_name(): void
    {
        $results = $this->model->searchByName('Test', 1, 10);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    /**
     * @test
     * Test soft delete functionality
     */
    public function test_soft_delete(): void
    {
        $id = $this->model->insert($this->urbanRenewalData);
        $this->model->delete($id);

        // Should not be found in normal queries
        $this->assertNull($this->model->find($id));

        // Should exist in database with deleted_at timestamp
        $this->seeInDatabase('urban_renewals', ['id' => $id]);
    }
}