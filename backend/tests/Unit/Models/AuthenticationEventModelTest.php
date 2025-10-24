<?php

namespace Tests\Unit\Models;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\AuthenticationEventModel;
use App\Models\UserModel;

class AuthenticationEventModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = false;
    protected $migrateOnce = false;
    protected $refresh     = false;
    protected $namespace   = null;

    protected $eventModel;
    protected $userModel;
    protected $testUserId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventModel = new AuthenticationEventModel();
        $this->userModel = new UserModel();

        // Create a test user
        $this->testUserId = $this->userModel->insert([
            'username' => 'test_user_' . time(),
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'test' . time() . '@example.com',
            'role' => 'admin',
            'is_active' => 1
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up test data
        if ($this->testUserId) {
            $this->userModel->delete($this->testUserId);
        }

        // Clean up test events
        $this->eventModel->where('username_attempted LIKE', 'test_%')->delete();
    }

    public function testGetFailedLoginsByIPReturnsCorrectCount()
    {
        $testIp = '192.168.1.100';

        // Create multiple failed login attempts from the same IP
        for ($i = 0; $i < 3; $i++) {
            $this->eventModel->insert([
                'event_type' => 'login_failure',
                'user_id' => null,
                'username_attempted' => 'test_user_' . $i,
                'ip_address' => $testIp,
                'user_agent' => 'Test Browser',
                'failure_reason' => 'invalid_credentials',
                'created_at' => date('Y-m-d H:i:s', strtotime("-{$i} minutes"))
            ]);
        }

        // Test within 30 minutes
        $failedAttempts = $this->eventModel->getFailedLoginsByIP($testIp, 30);
        $this->assertEquals(3, $failedAttempts);

        // Test within 1 minute (should only return the most recent)
        $failedAttempts = $this->eventModel->getFailedLoginsByIP($testIp, 1);
        $this->assertEquals(1, $failedAttempts);
    }

    public function testGetFailedLoginsByIPIgnoresSuccessfulLogins()
    {
        $testIp = '192.168.1.101';

        // Create failed login
        $this->eventModel->insert([
            'event_type' => 'login_failure',
            'user_id' => null,
            'username_attempted' => 'test_user',
            'ip_address' => $testIp,
            'user_agent' => 'Test Browser',
            'failure_reason' => 'invalid_credentials',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Create successful login
        $this->eventModel->insert([
            'event_type' => 'login_success',
            'user_id' => $this->testUserId,
            'username_attempted' => 'test_user',
            'ip_address' => $testIp,
            'user_agent' => 'Test Browser',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Should only count failed attempts
        $failedAttempts = $this->eventModel->getFailedLoginsByIP($testIp, 30);
        $this->assertEquals(1, $failedAttempts);
    }

    public function testGetUserAuthHistoryReturnsRecentEvents()
    {
        // Create multiple auth events for the test user
        $eventTypes = ['login_success', 'logout', 'token_refresh'];

        foreach ($eventTypes as $index => $eventType) {
            $this->eventModel->insert([
                'event_type' => $eventType,
                'user_id' => $this->testUserId,
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Test Browser',
                'created_at' => date('Y-m-d H:i:s', strtotime("-{$index} hours"))
            ]);
        }

        $history = $this->eventModel->getUserAuthHistory($this->testUserId, 10);

        $this->assertIsArray($history);
        $this->assertCount(3, $history);

        // Verify events are ordered by created_at DESC (most recent first)
        $this->assertEquals('login_success', $history[0]['event_type']);
        $this->assertEquals('logout', $history[1]['event_type']);
        $this->assertEquals('token_refresh', $history[2]['event_type']);
    }

    public function testGetUserAuthHistoryRespectsLimit()
    {
        // Create 10 events
        for ($i = 0; $i < 10; $i++) {
            $this->eventModel->insert([
                'event_type' => 'login_success',
                'user_id' => $this->testUserId,
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Test Browser',
                'created_at' => date('Y-m-d H:i:s', strtotime("-{$i} minutes"))
            ]);
        }

        $history = $this->eventModel->getUserAuthHistory($this->testUserId, 5);
        $this->assertCount(5, $history);
    }

    public function testDeleteOldEventsRemovesExpiredRecords()
    {
        // Create old event (100 days ago)
        $oldEventId = $this->eventModel->insert([
            'event_type' => 'login_success',
            'user_id' => $this->testUserId,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test Browser',
            'created_at' => date('Y-m-d H:i:s', strtotime('-100 days'))
        ]);

        // Create recent event (10 days ago)
        $recentEventId = $this->eventModel->insert([
            'event_type' => 'login_success',
            'user_id' => $this->testUserId,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test Browser',
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
        ]);

        // Delete events older than 90 days
        $deleted = $this->eventModel->deleteOldEvents(90);

        $this->assertGreaterThanOrEqual(1, $deleted);

        // Verify old event is deleted
        $oldEvent = $this->eventModel->find($oldEventId);
        $this->assertNull($oldEvent);

        // Verify recent event still exists
        $recentEvent = $this->eventModel->find($recentEventId);
        $this->assertNotNull($recentEvent);
    }

    public function testGetEventStatsReturnsCorrectCounts()
    {
        // Create events in the last 24 hours
        $eventTypes = [
            'login_success' => 5,
            'login_failure' => 3,
            'logout' => 2
        ];

        foreach ($eventTypes as $eventType => $count) {
            for ($i = 0; $i < $count; $i++) {
                $this->eventModel->insert([
                    'event_type' => $eventType,
                    'user_id' => $this->testUserId,
                    'ip_address' => '192.168.1.1',
                    'user_agent' => 'Test Browser',
                    'created_at' => date('Y-m-d H:i:s', strtotime("-{$i} hours"))
                ]);
            }
        }

        // Get all event stats for last 24 hours
        $stats = $this->eventModel->getEventStats(null, 24);

        $this->assertIsArray($stats);
        $this->assertGreaterThanOrEqual(3, count($stats));

        // Verify counts
        foreach ($stats as $stat) {
            if (isset($eventTypes[$stat['event_type']])) {
                $this->assertEquals($eventTypes[$stat['event_type']], $stat['count']);
            }
        }
    }

    public function testGetEventStatsFiltersByEventType()
    {
        // Create mixed events
        $this->eventModel->insert([
            'event_type' => 'login_success',
            'user_id' => $this->testUserId,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test Browser',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->eventModel->insert([
            'event_type' => 'login_failure',
            'user_id' => null,
            'username_attempted' => 'test_user',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Test Browser',
            'failure_reason' => 'invalid_credentials',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Get stats only for login_success
        $stats = $this->eventModel->getEventStats('login_success', 24);

        $this->assertIsArray($stats);
        $this->assertEquals(1, count($stats));
        $this->assertEquals('login_success', $stats[0]['event_type']);
    }
}
