<?php

namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\UserModel;
use App\Models\CompanyModel;

/**
 * API 測試基類
 * 提供 API 測試所需的輔助方法
 */
abstract class ApiTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    /**
     * Should the database be refreshed before each test?
     */
    protected $refresh = true;

    /**
     * The namespace to help us find the migration classes.
     */
    protected $namespace = 'App';

    /**
     * 測試用戶資料
     */
    protected $testUser = null;
    protected $testCompany = null;
    protected $authToken = null;

    /**
     * 建立測試用戶
     */
    protected function createTestUser(array $data = []): array
    {
        $userModel = new UserModel();
        
        $defaultData = [
            'username' => 'testuser_' . time(),
            'email' => 'test_' . time() . '@example.com',
            'password_hash' => password_hash('Password123!', PASSWORD_DEFAULT),
            'full_name' => 'Test User',
            'phone' => '0912345678',
            'user_type' => 'general',
            'role' => 'member',
            'is_active' => 1,
        ];

        $userData = array_merge($defaultData, $data);
        $userId = $userModel->insert($userData);

        return $userModel->find($userId);
    }

    /**
     * 建立測試企業
     */
    protected function createTestCompany(array $data = []): array
    {
        $companyModel = new CompanyModel();
        
        $defaultData = [
            'name' => 'Test Company ' . time(),
            'tax_id' => '12345678',
            'contact_person' => 'Test Contact',
            'contact_phone' => '02-12345678',
            'contact_email' => 'company_' . time() . '@example.com',
            'address' => 'Test Address',
            'is_active' => 1,
        ];

        $companyData = array_merge($defaultData, $data);
        $companyId = $companyModel->insert($companyData);

        return $companyModel->find($companyId);
    }

    /**
     * 建立企業管理者用戶
     */
    protected function createCompanyManager(int $companyId = null): array
    {
        if ($companyId === null) {
            $company = $this->createTestCompany();
            $companyId = $company['id'];
        }

        return $this->createTestUser([
            'role' => 'admin',
            'is_company_manager' => 1,
            'company_id' => $companyId,
        ]);
    }

    /**
     * 建立管理員用戶
     */
    protected function createAdminUser(): array
    {
        return $this->createTestUser([
            'role' => 'admin',
        ]);
    }

    /**
     * 模擬用戶登入並取得 Token
     */
    protected function loginAs(array $user): string
    {
        helper('auth');
        
        $token = generate_jwt_token($user);
        $this->authToken = $token;
        
        return $token;
    }

    /**
     * 發送帶有認證的 API 請求
     */
    protected function authenticatedGet(string $path, array $user = null)
    {
        if ($user) {
            $token = $this->loginAs($user);
        }

        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->get($path);
    }

    /**
     * 發送帶有認證的 POST 請求
     */
    protected function authenticatedPost(string $path, array $data, array $user = null)
    {
        if ($user) {
            $token = $this->loginAs($user);
        }

        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
            'Content-Type' => 'application/json',
        ])->post($path, $data);
    }

    /**
     * 發送帶有認證的 PUT 請求
     */
    protected function authenticatedPut(string $path, array $data, array $user = null)
    {
        if ($user) {
            $token = $this->loginAs($user);
        }

        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
            'Content-Type' => 'application/json',
        ])->put($path, $data);
    }

    /**
     * 發送帶有認證的 DELETE 請求
     */
    protected function authenticatedDelete(string $path, array $user = null)
    {
        if ($user) {
            $token = $this->loginAs($user);
        }

        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->delete($path);
    }

    /**
     * 驗證 JSON 響應結構
     */
    protected function assertJsonStructure(array $structure, $response)
    {
        $json = json_decode($response->getJSON(), true);
        
        foreach ($structure as $key => $value) {
            if (is_array($value)) {
                $this->assertArrayHasKey($key, $json);
                $this->assertJsonStructure($value, (object)['json' => json_encode($json[$key])]);
            } else {
                $this->assertArrayHasKey($value, $json);
            }
        }
    }

    /**
     * 清理測試資料
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // 清理會在各測試中自動 rollback
    }
}
