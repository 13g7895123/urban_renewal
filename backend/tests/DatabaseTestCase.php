<?php

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Base test case for database tests
 *
 * This class provides a foundation for TDD testing with proper database setup/teardown
 */
abstract class DatabaseTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    /**
     * Should the database be refreshed before each test?
     */
    protected $refresh = true;

    /**
     * The name of a seed file used for all tests within this test case.
     */
    protected $seed = '';

    /**
     * The path to where we can find the test Seeds directory.
     */
    protected $basePath = SUPPORTPATH . 'Database/';

    /**
     * The namespace to help us find the migration classes.
     */
    protected $namespace = 'App';

    /**
     * Sample data for tests
     */
    protected $urbanRenewalData = [
        'name' => 'Test Urban Renewal Group',
        'area' => 1500.50,
        'member_count' => 25,
        'chairman_name' => 'John Doe',
        'chairman_phone' => '02-12345678',
        'address' => 'Test Address 123',
        'representative' => 'Jane Smith'
    ];

    protected $propertyOwnerData = [
        'urban_renewal_id' => 1,
        'name' => 'Test Property Owner',
        'id_number' => 'A123456789',
        'owner_code' => 'OW2409120001',
        'phone1' => '02-11111111',
        'phone2' => '0912345678',
        'contact_address' => 'Contact Address 123',
        'household_address' => 'Household Address 456',
        'exclusion_type' => null,
        'notes' => 'Test notes'
    ];

    protected $landPlotData = [
        'urban_renewal_id' => 1,
        'county' => '臺北市',
        'district' => '中正區',
        'section' => '城中段',
        'land_number_main' => '123',
        'land_number_sub' => '4',
        'total_area' => 500.75,
        'public_land_area' => 100.25,
        'private_land_area' => 400.50,
        'notes' => 'Test land plot'
    ];

    /**
     * Set up the test environment before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set up database tables for testing
        $this->setUpDatabaseTables();

        // Load necessary helper functions
        helper(['url', 'form']);
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Set up database tables for testing
     */
    protected function setUpDatabaseTables(): void
    {
        $db = \Config\Database::connect();

        // Create urban_renewals table
        $db->query("
            CREATE TABLE IF NOT EXISTS urban_renewals (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                area DECIMAL(10,2) NOT NULL,
                member_count INT NOT NULL,
                chairman_name VARCHAR(100) NOT NULL,
                chairman_phone VARCHAR(20) NOT NULL,
                address TEXT,
                representative VARCHAR(100),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL
            )
        ");

        // Create property_owners table
        $db->query("
            CREATE TABLE IF NOT EXISTS property_owners (
                id INT AUTO_INCREMENT PRIMARY KEY,
                urban_renewal_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                id_number VARCHAR(20),
                owner_code VARCHAR(20) UNIQUE,
                phone1 VARCHAR(20),
                phone2 VARCHAR(20),
                contact_address TEXT,
                household_address TEXT,
                exclusion_type ENUM('法院囑託查封','假扣押','假處分','破產登記','未經繼承'),
                notes TEXT,
                total_land_area DECIMAL(10,2) DEFAULT 0.00,
                total_building_area DECIMAL(10,2) DEFAULT 0.00,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL,
                FOREIGN KEY (urban_renewal_id) REFERENCES urban_renewals(id) ON DELETE CASCADE
            )
        ");

        // Create land_plots table
        $db->query("
            CREATE TABLE IF NOT EXISTS land_plots (
                id INT AUTO_INCREMENT PRIMARY KEY,
                urban_renewal_id INT NOT NULL,
                county VARCHAR(20) NOT NULL,
                district VARCHAR(20) NOT NULL,
                section VARCHAR(50) NOT NULL,
                land_number_main VARCHAR(20) NOT NULL,
                land_number_sub VARCHAR(20) DEFAULT '0',
                total_area DECIMAL(10,2),
                public_land_area DECIMAL(10,2) DEFAULT 0.00,
                private_land_area DECIMAL(10,2) DEFAULT 0.00,
                notes TEXT,
                representative_id INT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL,
                FOREIGN KEY (urban_renewal_id) REFERENCES urban_renewals(id) ON DELETE CASCADE,
                FOREIGN KEY (representative_id) REFERENCES property_owners(id) ON DELETE SET NULL
            )
        ");

        // Create buildings table
        $db->query("
            CREATE TABLE IF NOT EXISTS buildings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                urban_renewal_id INT NOT NULL,
                county VARCHAR(20) NOT NULL,
                district VARCHAR(20) NOT NULL,
                section VARCHAR(50) NOT NULL,
                building_number_main VARCHAR(20) NOT NULL,
                building_number_sub VARCHAR(20) DEFAULT '0',
                building_area DECIMAL(10,2),
                building_address TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at DATETIME NULL,
                FOREIGN KEY (urban_renewal_id) REFERENCES urban_renewals(id) ON DELETE CASCADE
            )
        ");

        // Create ownership tables
        $db->query("
            CREATE TABLE IF NOT EXISTS owner_land_ownerships (
                id INT AUTO_INCREMENT PRIMARY KEY,
                property_owner_id INT NOT NULL,
                land_plot_id INT NOT NULL,
                ownership_numerator INT NOT NULL DEFAULT 1,
                ownership_denominator INT NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (property_owner_id) REFERENCES property_owners(id) ON DELETE CASCADE,
                FOREIGN KEY (land_plot_id) REFERENCES land_plots(id) ON DELETE CASCADE
            )
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS owner_building_ownerships (
                id INT AUTO_INCREMENT PRIMARY KEY,
                property_owner_id INT NOT NULL,
                building_id INT NOT NULL,
                ownership_numerator INT NOT NULL DEFAULT 1,
                ownership_denominator INT NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (property_owner_id) REFERENCES property_owners(id) ON DELETE CASCADE,
                FOREIGN KEY (building_id) REFERENCES buildings(id) ON DELETE CASCADE
            )
        ");

        // Create location tables for testing
        $db->query("
            CREATE TABLE IF NOT EXISTS counties (
                id INT AUTO_INCREMENT PRIMARY KEY,
                county_code VARCHAR(10) NOT NULL UNIQUE,
                county_name VARCHAR(50) NOT NULL
            )
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS districts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                county_code VARCHAR(10) NOT NULL,
                district_code VARCHAR(10) NOT NULL,
                district_name VARCHAR(50) NOT NULL,
                FOREIGN KEY (county_code) REFERENCES counties(county_code)
            )
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS sections (
                id INT AUTO_INCREMENT PRIMARY KEY,
                county_code VARCHAR(10) NOT NULL,
                district_code VARCHAR(10) NOT NULL,
                section_code VARCHAR(10) NOT NULL,
                section_name VARCHAR(100) NOT NULL
            )
        ");
    }

    /**
     * Insert sample data for testing
     */
    protected function insertSampleData(): void
    {
        $db = \Config\Database::connect();

        // Insert sample urban renewal
        $db->table('urban_renewals')->insert($this->urbanRenewalData);

        // Insert sample location data
        $db->table('counties')->insert([
            'county_code' => '63',
            'county_name' => '臺北市'
        ]);

        $db->table('districts')->insert([
            'county_code' => '63',
            'district_code' => '100',
            'district_name' => '中正區'
        ]);

        $db->table('sections')->insert([
            'county_code' => '63',
            'district_code' => '100',
            'section_code' => '0001',
            'section_name' => '城中段'
        ]);
    }

    /**
     * Helper method to assert API response structure
     */
    protected function assertApiResponseStructure(array $response, string $status = 'success'): void
    {
        $this->assertIsArray($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals($status, $response['status']);
        $this->assertArrayHasKey('message', $response);

        if ($status === 'success') {
            $this->assertArrayHasKey('data', $response);
        }
    }

    /**
     * Helper method to assert pagination structure
     */
    protected function assertPaginationStructure(array $response): void
    {
        $this->assertArrayHasKey('pagination', $response);
        $pagination = $response['pagination'];
        $this->assertArrayHasKey('current_page', $pagination);
        $this->assertArrayHasKey('per_page', $pagination);
        $this->assertArrayHasKey('total', $pagination);
        $this->assertArrayHasKey('total_pages', $pagination);
    }

    /**
     * Helper method to create JSON request
     */
    protected function postJson(string $uri, array $data = []): \CodeIgniter\Test\TestResponse
    {
        return $this->withHeaders(['Content-Type' => 'application/json'])
                    ->post($uri, json_encode($data));
    }

    /**
     * Helper method to create JSON PUT request
     */
    protected function putJson(string $uri, array $data = []): \CodeIgniter\Test\TestResponse
    {
        return $this->withHeaders(['Content-Type' => 'application/json'])
                    ->put($uri, json_encode($data));
    }
}