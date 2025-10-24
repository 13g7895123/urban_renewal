<?php

namespace Tests\Support\Fixtures;

use App\Models\UserModel;
use Firebase\JWT\JWT;

/**
 * User Test Fixture
 * 提供測試用使用者資料和 Token 生成方法
 */
class UserFixture
{
    protected $userModel;
    protected $createdUsers = [];

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * 建立管理員使用者
     * urban_renewal_id = null，擁有無限制存取權
     */
    public function createAdmin(array $overrides = []): array
    {
        $data = array_merge([
            'username' => 'admin_' . time() . '_' . rand(1000, 9999),
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'email' => 'admin_' . time() . '@test.com',
            'full_name' => '系統管理員',
            'role' => 'admin',
            'urban_renewal_id' => null,
            'is_active' => 1,
            'login_attempts' => 0
        ], $overrides);

        $userId = $this->userModel->insert($data);
        $user = $this->userModel->find($userId);
        $this->createdUsers[] = $userId;

        return $user;
    }

    /**
     * 建立理事長使用者
     * 擁有特定 urban_renewal_id 的完整管理權
     */
    public function createChairman(int $urbanRenewalId, array $overrides = []): array
    {
        $data = array_merge([
            'username' => 'chairman_' . time() . '_' . rand(1000, 9999),
            'password_hash' => password_hash('chairman123', PASSWORD_DEFAULT),
            'email' => 'chairman_' . time() . '@test.com',
            'full_name' => '理事長',
            'role' => 'chairman',
            'urban_renewal_id' => $urbanRenewalId,
            'is_active' => 1,
            'login_attempts' => 0
        ], $overrides);

        $userId = $this->userModel->insert($data);
        $user = $this->userModel->find($userId);
        $this->createdUsers[] = $userId;

        return $user;
    }

    /**
     * 建立會員使用者
     * 擁有特定 urban_renewal_id 的基本存取權
     */
    public function createMember(int $urbanRenewalId, array $overrides = []): array
    {
        $data = array_merge([
            'username' => 'member_' . time() . '_' . rand(1000, 9999),
            'password_hash' => password_hash('member123', PASSWORD_DEFAULT),
            'email' => 'member_' . time() . '@test.com',
            'full_name' => '會員',
            'role' => 'member',
            'urban_renewal_id' => $urbanRenewalId,
            'is_active' => 1,
            'login_attempts' => 0
        ], $overrides);

        $userId = $this->userModel->insert($data);
        $user = $this->userModel->find($userId);
        $this->createdUsers[] = $userId;

        return $user;
    }

    /**
     * 建立觀察員使用者
     * 僅有特定 urban_renewal_id 的唯讀權限
     */
    public function createObserver(int $urbanRenewalId, array $overrides = []): array
    {
        $data = array_merge([
            'username' => 'observer_' . time() . '_' . rand(1000, 9999),
            'password_hash' => password_hash('observer123', PASSWORD_DEFAULT),
            'email' => 'observer_' . time() . '@test.com',
            'full_name' => '觀察員',
            'role' => 'observer',
            'urban_renewal_id' => $urbanRenewalId,
            'is_active' => 1,
            'login_attempts' => 0
        ], $overrides);

        $userId = $this->userModel->insert($data);
        $user = $this->userModel->find($userId);
        $this->createdUsers[] = $userId;

        return $user;
    }

    /**
     * 根據角色建立使用者
     */
    public function createUserByRole(string $role, ?int $urbanRenewalId = null, array $overrides = []): array
    {
        switch ($role) {
            case 'admin':
                return $this->createAdmin($overrides);
            case 'chairman':
                if ($urbanRenewalId === null) {
                    throw new \InvalidArgumentException('Chairman requires urban_renewal_id');
                }
                return $this->createChairman($urbanRenewalId, $overrides);
            case 'member':
                if ($urbanRenewalId === null) {
                    throw new \InvalidArgumentException('Member requires urban_renewal_id');
                }
                return $this->createMember($urbanRenewalId, $overrides);
            case 'observer':
                if ($urbanRenewalId === null) {
                    throw new \InvalidArgumentException('Observer requires urban_renewal_id');
                }
                return $this->createObserver($urbanRenewalId, $overrides);
            default:
                throw new \InvalidArgumentException('Invalid role: ' . $role);
        }
    }

    /**
     * 產生 JWT Token
     * 用於測試 API 請求的認證
     */
    public function generateToken(array $user, ?int $sessionId = null): string
    {
        $key = $_ENV['JWT_SECRET'] ?? 'urban_renewal_secret_key_2025';

        $payload = [
            'iss' => 'urban-renewal-system',
            'aud' => 'urban-renewal-users',
            'iat' => time(),
            'exp' => time() + 86400, // 24小時
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'urban_renewal_id' => $user['urban_renewal_id']
        ];

        if ($sessionId) {
            $payload['session_id'] = $sessionId;
        }

        return JWT::encode($payload, $key, 'HS256');
    }

    /**
     * 產生過期的 JWT Token
     * 用於測試 token 過期處理
     */
    public function generateExpiredToken(array $user): string
    {
        $key = $_ENV['JWT_SECRET'] ?? 'urban_renewal_secret_key_2025';

        $payload = [
            'iss' => 'urban-renewal-system',
            'aud' => 'urban-renewal-users',
            'iat' => time() - 172800, // 2天前
            'exp' => time() - 86400, // 1天前過期
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'urban_renewal_id' => $user['urban_renewal_id']
        ];

        return JWT::encode($payload, $key, 'HS256');
    }

    /**
     * 建立完整測試場景
     * 包含所有4種角色的使用者
     */
    public function createFullScenario(int $urbanRenewalId): array
    {
        return [
            'admin' => $this->createAdmin(),
            'chairman' => $this->createChairman($urbanRenewalId),
            'member' => $this->createMember($urbanRenewalId),
            'observer' => $this->createObserver($urbanRenewalId)
        ];
    }

    /**
     * 建立多個更新地區的使用者場景
     */
    public function createMultiUrbanRenewalScenario(array $urbanRenewalIds): array
    {
        $scenarios = [];

        foreach ($urbanRenewalIds as $id) {
            $scenarios["urban_renewal_{$id}"] = [
                'chairman' => $this->createChairman($id),
                'member' => $this->createMember($id),
                'observer' => $this->createObserver($id)
            ];
        }

        $scenarios['admin'] = $this->createAdmin();

        return $scenarios;
    }

    /**
     * 建立停用的使用者
     * 用於測試帳號狀態檢查
     */
    public function createInactiveUser(string $role = 'member', ?int $urbanRenewalId = null): array
    {
        $overrides = ['is_active' => 0];
        return $this->createUserByRole($role, $urbanRenewalId, $overrides);
    }

    /**
     * 建立被鎖定的使用者
     * 用於測試帳號鎖定機制
     */
    public function createLockedUser(string $role = 'member', ?int $urbanRenewalId = null): array
    {
        $overrides = [
            'login_attempts' => 5,
            'locked_until' => date('Y-m-d H:i:s', time() + 1800) // 鎖定30分鐘
        ];
        return $this->createUserByRole($role, $urbanRenewalId, $overrides);
    }

    /**
     * 清理所有建立的測試使用者
     */
    public function cleanup(): void
    {
        foreach ($this->createdUsers as $userId) {
            $this->userModel->delete($userId);
        }
        $this->createdUsers = [];
    }

    /**
     * Destructor - 自動清理
     */
    public function __destruct()
    {
        $this->cleanup();
    }
}
