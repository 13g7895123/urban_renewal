<?php

namespace Tests\Feature\Api;

use Tests\Support\ApiTestCase;
use App\Models\UserModel;

class AuthControllerTest extends ApiTestCase
{
    protected $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new UserModel();
    }

    public function testUserCanRegisterWithValidData()
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirm' => 'Password123!',
            'full_name' => 'New User',
            'phone' => '0912345678',
            'user_type' => 'general',
            'role' => 'member'
        ];

        $result = $this->withHeaders([
            'Content-Type' => 'application/json'
        ])->post('/api/auth/register', $userData);

        $result->assertStatus(200);
        $result->assertJSONFragment([
            'success' => true
        ]);

        // 驗證使用者已建立
        $user = $this->userModel->where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('newuser', $user['username']);
    }

    public function testRegisterFailsWithMissingRequiredFields()
    {
        $userData = [
            'username' => 'incomplete'
        ];

        $result = $this->post('/api/auth/register', $userData);

        $result->assertStatus(400);
    }

    public function testRegisterFailsWithInvalidEmail()
    {
        $userData = [
            'username' => 'testuser',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirm' => 'Password123!',
            'full_name' => 'Test User',
            'phone' => '0912345678',
            'user_type' => 'general',
            'role' => 'member'
        ];

        $result = $this->post('/api/auth/register', $userData);

        $result->assertStatus(400);
    }

    public function testRegisterFailsWithPasswordMismatch()
    {
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirm' => 'DifferentPassword123!',
            'full_name' => 'Test User',
            'phone' => '0912345678',
            'user_type' => 'general',
            'role' => 'member'
        ];

        $result = $this->post('/api/auth/register', $userData);

        $result->assertStatus(400);
    }

    public function testUserCanLoginWithCorrectCredentials()
    {
        // 建立測試使用者
        $user = $this->createTestUser([
            'username' => 'logintest',
            'password_hash' => password_hash('Password123!', PASSWORD_DEFAULT),
        ]);

        $loginData = [
            'username' => 'logintest',
            'password' => 'Password123!'
        ];

        $result = $this->post('/api/auth/login', $loginData);
        
        $result->assertStatus(200);
        $result->assertJSONFragment([
            'success' => true
        ]);
        
        // 驗證返回了使用者資料
        $data = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('user', $data['data']);
        $this->assertEquals('logintest', $data['data']['user']['username']);
    }

    public function testLoginFailsWithIncorrectPassword()
    {
        $user = $this->createTestUser([
            'username' => 'failtest',
            'password_hash' => password_hash('CorrectPassword', PASSWORD_DEFAULT),
        ]);

        $loginData = [
            'username' => 'failtest',
            'password' => 'WrongPassword'
        ];

        $result = $this->post('/api/auth/login', $loginData);
        
        $result->assertStatus(401);
        $result->assertJSONFragment([
            'success' => false
        ]);
    }

    public function testLoginFailsWithNonExistentUser()
    {
        $loginData = [
            'username' => 'nonexistent',
            'password' => 'Password123!'
        ];

        $result = $this->post('/api/auth/login', $loginData);
        
        $result->assertStatus(401);
    }

    public function testLoginFailsForInactiveUser()
    {
        $user = $this->createTestUser([
            'username' => 'inactive',
            'password_hash' => password_hash('Password123!', PASSWORD_DEFAULT),
            'is_active' => 0,
        ]);

        $loginData = [
            'username' => 'inactive',
            'password' => 'Password123!'
        ];

        $result = $this->post('/api/auth/login', $loginData);
        
        $result->assertStatus(403);
    }

    public function testMeEndpointReturnsUserDataWhenAuthenticated()
    {
        $user = $this->createTestUser();
        
        $result = $this->authenticatedGet('/api/auth/me', $user);
        
        $result->assertStatus(200);
        $data = json_decode($result->getJSON(), true);
        $this->assertEquals($user['username'], $data['data']['user']['username']);
    }

    public function testMeEndpointFailsWhenNotAuthenticated()
    {
        $result = $this->get('/api/auth/me');
        
        $result->assertStatus(401);
    }

    public function testLogoutClearsSession()
    {
        $user = $this->createTestUser([
            'username' => 'logouttest',
            'password_hash' => password_hash('Password123!', PASSWORD_DEFAULT),
        ]);

        // 先登入
        $loginResult = $this->post('/api/auth/login', [
            'username' => 'logouttest',
            'password' => 'Password123!'
        ]);

        $loginResult->assertStatus(200);

        // 登出
        $logoutResult = $this->authenticatedPost('/api/auth/logout', [], $user);
        
        $logoutResult->assertStatus(200);
        $logoutResult->assertJSONFragment([
            'success' => true
        ]);
    }
}
