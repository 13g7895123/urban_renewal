<?php

namespace Tests\Unit\Models;

use Tests\Support\ApiTestCase;
use App\Models\UserModel;

class UserModelTest extends ApiTestCase
{
    protected $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new UserModel();
    }

    public function testCreateUserWithValidData()
    {
        $userData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password_hash' => password_hash('Password123!', PASSWORD_DEFAULT),
            'full_name' => 'New User',
            'phone' => '0912345678',
            'user_type' => 'general',
            'role' => 'member',
            'is_active' => 1,
        ];

        $userId = $this->userModel->insert($userData);
        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);

        $user = $this->userModel->find($userId);
        $this->assertEquals('newuser', $user['username']);
        $this->assertEquals('newuser@example.com', $user['email']);
    }

    public function testEmailMustBeUnique()
    {
        $this->createTestUser(['email' => 'unique@example.com']);

        // 嘗試建立相同 email 的使用者
        $result = $this->userModel->insert([
            'username' => 'another',
            'email' => 'unique@example.com',
            'password_hash' => password_hash('pass', PASSWORD_DEFAULT),
            'full_name' => 'Another User',
        ]);

        $this->assertFalse($result);
        $errors = $this->userModel->errors();
        $this->assertNotEmpty($errors);
    }

    public function testUsernameMustBeUnique()
    {
        $this->createTestUser(['username' => 'uniqueuser']);

        $result = $this->userModel->insert([
            'username' => 'uniqueuser',
            'email' => 'different@example.com',
            'password_hash' => password_hash('pass', PASSWORD_DEFAULT),
            'full_name' => 'Different User',
        ]);

        $this->assertFalse($result);
    }

    public function testFindActiveUsers()
    {
        $this->createTestUser(['is_active' => 1]);
        $this->createTestUser(['is_active' => 1]);
        $this->createTestUser(['is_active' => 0]);

        $activeUsers = $this->userModel->where('is_active', 1)->findAll();
        $this->assertGreaterThanOrEqual(2, count($activeUsers));
    }

    public function testSoftDelete()
    {
        $user = $this->createTestUser();
        $userId = $user['id'];

        // 軟刪除
        $this->userModel->delete($userId);

        // 應該無法找到（因為已刪除）
        $deleted = $this->userModel->find($userId);
        $this->assertNull($deleted);

        // 使用 withDeleted 應該能找到
        $withDeleted = $this->userModel->withDeleted()->find($userId);
        $this->assertNotNull($withDeleted);
    }

    public function testUpdateUser()
    {
        $user = $this->createTestUser();
        $userId = $user['id'];

        $updated = $this->userModel->update($userId, [
            'full_name' => 'Updated Name'
        ]);

        $this->assertTrue($updated);

        $updatedUser = $this->userModel->find($userId);
        $this->assertEquals('Updated Name', $updatedUser['full_name']);
    }
}
