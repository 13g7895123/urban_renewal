<?php

namespace Tests\app\Controllers\Api;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\UserModel;
use App\Models\AuthenticationEventModel;

class AuthControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate     = false;
    protected $migrateOnce = false;
    protected $refresh     = false;
    protected $namespace   = null;

    protected $userModel;
    protected $eventModel;
    protected $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userModel = new UserModel();
        $this->eventModel = new AuthenticationEventModel();

        // Create test user
        $userId = $this->userModel->insert([
            'username' => 'test_admin',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'admin@test.com',
            'role' => 'admin',
            'is_active' => 1,
            'login_attempts' => 0
        ]);

        $this->testUser = $this->userModel->find($userId);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up test data
        if ($this->testUser) {
            $this->userModel->delete($this->testUser['id']);
        }

        // Clean up test events
        $this->eventModel->where('username_attempted', 'test_admin')->delete();
        $this->eventModel->where('user_id', $this->testUser['id'] ?? null)->delete();
    }

    public function testSuccessfulLoginLogsAuthEvent()
    {
        $result = $this->withBodyFormat('json')
            ->post('/api/auth/login', [
                'username' => 'test_admin',
                'password' => 'password123'
            ]);

        $result->assertStatus(200);
        $result->assertJSONFragment(['success' => true]);

        // Verify login_success event was logged
        $events = $this->eventModel
            ->where('user_id', $this->testUser['id'])
            ->where('event_type', 'login_success')
            ->orderBy('created_at', 'DESC')
            ->findAll(1);

        $this->assertCount(1, $events);
        $this->assertEquals('login_success', $events[0]['event_type']);
        $this->assertEquals($this->testUser['id'], $events[0]['user_id']);

        // Verify event metadata contains role and urban_renewal_id
        $metadata = json_decode($events[0]['event_metadata'], true);
        $this->assertArrayHasKey('role', $metadata);
        $this->assertEquals('admin', $metadata['role']);
    }

    public function testFailedLoginWithInvalidCredentialsLogsEvent()
    {
        $result = $this->withBodyFormat('json')
            ->post('/api/auth/login', [
                'username' => 'nonexistent_user',
                'password' => 'wrongpassword'
            ]);

        $result->assertStatus(401);

        // Verify login_failure event was logged
        $events = $this->eventModel
            ->where('username_attempted', 'nonexistent_user')
            ->where('event_type', 'login_failure')
            ->where('failure_reason', 'invalid_credentials')
            ->findAll();

        $this->assertGreaterThanOrEqual(1, count($events));
        $this->assertEquals('login_failure', $events[0]['event_type']);
        $this->assertNull($events[0]['user_id']);
    }

    public function testFailedLoginWithWrongPasswordLogsEvent()
    {
        $result = $this->withBodyFormat('json')
            ->post('/api/auth/login', [
                'username' => 'test_admin',
                'password' => 'wrongpassword'
            ]);

        $result->assertStatus(401);

        // Verify login_failure event was logged with invalid_password reason
        $events = $this->eventModel
            ->where('user_id', $this->testUser['id'])
            ->where('event_type', 'login_failure')
            ->where('failure_reason', 'invalid_password')
            ->orderBy('created_at', 'DESC')
            ->findAll(1);

        $this->assertCount(1, $events);

        // Verify login_attempts in metadata
        $metadata = json_decode($events[0]['event_metadata'], true);
        $this->assertArrayHasKey('login_attempts', $metadata);
        $this->assertEquals(1, $metadata['login_attempts']);
    }

    public function testAccountLockoutLogsEvent()
    {
        // Attempt login 5 times with wrong password to trigger lockout
        for ($i = 0; $i < 5; $i++) {
            $this->withBodyFormat('json')
                ->post('/api/auth/login', [
                    'username' => 'test_admin',
                    'password' => 'wrongpassword'
                ]);
        }

        // Verify account_locked event was logged
        $events = $this->eventModel
            ->where('user_id', $this->testUser['id'])
            ->where('event_type', 'login_failure')
            ->where('failure_reason', 'account_locked')
            ->findAll();

        $this->assertGreaterThanOrEqual(1, count($events));

        // Verify user account is locked
        $user = $this->userModel->find($this->testUser['id']);
        $this->assertNotNull($user['locked_until']);
        $this->assertGreaterThan(time(), strtotime($user['locked_until']));
    }

    public function testLogoutLogsAuthEvent()
    {
        // First login to get token
        $loginResult = $this->withBodyFormat('json')
            ->post('/api/auth/login', [
                'username' => 'test_admin',
                'password' => 'password123'
            ]);

        $loginData = json_decode($loginResult->getJSON(), true);
        $token = $loginData['data']['token'];

        // Now logout
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])
            ->post('/api/auth/logout');

        $result->assertStatus(200);

        // Verify logout event was logged
        $events = $this->eventModel
            ->where('user_id', $this->testUser['id'])
            ->where('event_type', 'logout')
            ->orderBy('created_at', 'DESC')
            ->findAll(1);

        $this->assertCount(1, $events);
        $this->assertEquals('logout', $events[0]['event_type']);
        $this->assertEquals($this->testUser['id'], $events[0]['user_id']);
    }

    public function testTokenRefreshLogsAuthEvent()
    {
        // First login to get refresh token
        $loginResult = $this->withBodyFormat('json')
            ->post('/api/auth/login', [
                'username' => 'test_admin',
                'password' => 'password123'
            ]);

        $loginData = json_decode($loginResult->getJSON(), true);
        $refreshToken = $loginData['data']['refresh_token'];

        // Refresh token
        $result = $this->withBodyFormat('json')
            ->post('/api/auth/refresh', [
                'refresh_token' => $refreshToken
            ]);

        $result->assertStatus(200);

        // Verify token_refresh event was logged
        $events = $this->eventModel
            ->where('user_id', $this->testUser['id'])
            ->where('event_type', 'token_refresh')
            ->orderBy('created_at', 'DESC')
            ->findAll(1);

        $this->assertCount(1, $events);
        $this->assertEquals('token_refresh', $events[0]['event_type']);
        $this->assertEquals($this->testUser['id'], $events[0]['user_id']);
    }

    public function testAuthEventsContainIPAddressAndUserAgent()
    {
        // Login with custom headers
        $result = $this->withBodyFormat('json')
            ->withHeaders([
                'User-Agent' => 'Test Browser/1.0'
            ])
            ->post('/api/auth/login', [
                'username' => 'test_admin',
                'password' => 'password123'
            ]);

        $result->assertStatus(200);

        // Verify event contains IP and User-Agent
        $events = $this->eventModel
            ->where('user_id', $this->testUser['id'])
            ->where('event_type', 'login_success')
            ->orderBy('created_at', 'DESC')
            ->findAll(1);

        $this->assertNotEmpty($events[0]['ip_address']);
        $this->assertNotEmpty($events[0]['user_agent']);
        $this->assertStringContainsString('Test Browser', $events[0]['user_agent']);
    }

    public function testMultipleFailedLoginsFromSameIPAreTracked()
    {
        $testIp = '192.168.1.100';

        // Simulate multiple failed attempts from same IP
        for ($i = 0; $i < 3; $i++) {
            $this->eventModel->insert([
                'event_type' => 'login_failure',
                'user_id' => null,
                'username_attempted' => 'test_user_' . $i,
                'ip_address' => $testIp,
                'user_agent' => 'Test Browser',
                'failure_reason' => 'invalid_credentials',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Verify all attempts are tracked
        $failedAttempts = $this->eventModel->getFailedLoginsByIP($testIp, 30);
        $this->assertEquals(3, $failedAttempts);
    }
}
