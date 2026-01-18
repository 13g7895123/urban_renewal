<?php

namespace Tests\app\Controllers\Api;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\UserModel;
use App\Models\CompanyModel;
use App\Models\UrbanRenewalModel;
use App\Models\UserRenewalAssignmentModel;

/**
 * 公司審核系統測試
 * 測試使用者審核流程、邀請碼管理、成員指派等功能
 */
class CompanyApprovalTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate     = false;
    protected $migrateOnce = false;
    protected $refresh     = false;
    protected $namespace   = null;

    protected $userModel;
    protected $companyModel;
    protected $urbanRenewalModel;
    protected $assignmentModel;

    protected $testCompany;
    protected $testManager;
    protected $testPendingUser;
    protected $testApprovedUser;
    protected $testUrbanRenewal;
    protected $managerToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userModel = new UserModel();
        $this->companyModel = new CompanyModel();
        $this->urbanRenewalModel = new UrbanRenewalModel();
        $this->assignmentModel = new UserRenewalAssignmentModel();

        // 創建測試公司
        $companyId = $this->companyModel->insert([
            'name' => 'Test Company',
            'tax_id' => '12345678',
            'invite_code' => 'TESTCODE',
            'invite_code_active' => 1
        ]);
        $this->testCompany = $this->companyModel->find($companyId);

        // 創建測試更新會
        $renewalId = $this->urbanRenewalModel->insert([
            'name' => 'Test Urban Renewal',
            'company_id' => $companyId,
            'address' => 'Test Address',
            'status' => 'active'
        ]);
        $this->testUrbanRenewal = $this->urbanRenewalModel->find($renewalId);

        // 創建公司管理者
        $managerId = $this->userModel->insert([
            'username' => 'test_manager',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'manager@test.com',
            'role' => 'chairman',
            'user_type' => 'enterprise',
            'is_company_manager' => 1,
            'company_id' => $companyId,
            'approval_status' => 'approved',
            'is_substantive' => 1,
            'is_active' => 1
        ]);
        $this->testManager = $this->userModel->find($managerId);

        // 創建待審核使用者
        $pendingUserId = $this->userModel->insert([
            'username' => 'test_pending',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'pending@test.com',
            'role' => 'member',
            'user_type' => 'enterprise',
            'is_company_manager' => 0,
            'company_id' => $companyId,
            'company_invite_code' => 'TESTCODE',
            'approval_status' => 'pending',
            'is_active' => 0
        ]);
        $this->testPendingUser = $this->userModel->find($pendingUserId);

        // 創建已核准使用者
        $approvedUserId = $this->userModel->insert([
            'username' => 'test_approved',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'approved@test.com',
            'role' => 'member',
            'user_type' => 'enterprise',
            'is_company_manager' => 0,
            'company_id' => $companyId,
            'approval_status' => 'approved',
            'is_substantive' => 1,
            'is_active' => 1
        ]);
        $this->testApprovedUser = $this->userModel->find($approvedUserId);

        // 取得管理者 token
        $this->managerToken = $this->getAuthToken('test_manager', 'password123');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // 清理測試資料
        if ($this->testPendingUser) {
            $this->userModel->delete($this->testPendingUser['id'], true);
        }
        if ($this->testApprovedUser) {
            $this->userModel->delete($this->testApprovedUser['id'], true);
        }
        if ($this->testManager) {
            $this->userModel->delete($this->testManager['id'], true);
        }
        if ($this->testUrbanRenewal) {
            $this->urbanRenewalModel->delete($this->testUrbanRenewal['id'], true);
        }
        if ($this->testCompany) {
            $this->companyModel->delete($this->testCompany['id'], true);
        }

        // 清理指派記錄
        $this->assignmentModel->where('urban_renewal_id', $this->testUrbanRenewal['id'] ?? 0)->delete();
    }

    /**
     * 輔助方法：取得認證 token
     */
    protected function getAuthToken($username, $password)
    {
        $result = $this->withBodyFormat('json')
            ->post('/api/auth/login', [
                'username' => $username,
                'password' => $password
            ]);

        $data = json_decode($result->getJSON(), true);
        return $data['data']['token'] ?? null;
    }

    // ==================== 邀請碼管理測試 ====================

    public function testGetInviteCodeSuccess()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->get('/api/companies/me/invite-code');

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);

        $data = json_decode($result->getJSON(), true);
        $this->assertEquals('TESTCODE', $data['data']['invite_code']);
        $this->assertEquals(1, $data['data']['invite_code_active']);
    }

    public function testGetInviteCodeWithoutPermission()
    {
        // 創建一般使用者
        $userId = $this->userModel->insert([
            'username' => 'normal_user',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'normal@test.com',
            'role' => 'member',
            'is_company_manager' => 0,
            'is_active' => 1
        ]);

        $token = $this->getAuthToken('normal_user', 'password123');

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])
            ->get('/api/companies/me/invite-code');

        $result->assertStatus(403);
        $result->assertJSONFragment(['status' => 'error']);

        // 清理
        $this->userModel->delete($userId, true);
    }

    public function testGenerateInviteCodeSuccess()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->post('/api/companies/me/generate-invite-code');

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);

        $data = json_decode($result->getJSON(), true);
        $this->assertNotEmpty($data['data']['invite_code']);
        $this->assertNotEquals('TESTCODE', $data['data']['invite_code']);
        $this->assertEquals(8, strlen($data['data']['invite_code']));
    }

    public function testGenerateInviteCodeUpdatesDatabase()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->post('/api/companies/me/generate-invite-code');

        $data = json_decode($result->getJSON(), true);
        $newCode = $data['data']['invite_code'];

        // 驗證資料庫已更新
        $company = $this->companyModel->find($this->testCompany['id']);
        $this->assertEquals($newCode, $company['invite_code']);
        $this->assertEquals(1, $company['invite_code_active']);
    }

    // ==================== 待審核使用者列表測試 ====================

    public function testGetPendingUsersSuccess()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->get('/api/companies/me/pending-users');

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);

        $data = json_decode($result->getJSON(), true);
        $this->assertIsArray($data['data']);
        $this->assertGreaterThanOrEqual(1, count($data['data']));

        // 驗證包含待審核使用者
        $usernames = array_column($data['data'], 'username');
        $this->assertContains('test_pending', $usernames);
    }

    public function testGetPendingUsersWithPagination()
    {
        // 創建多個待審核使用者
        for ($i = 1; $i <= 5; $i++) {
            $this->userModel->insert([
                'username' => "pending_user_$i",
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'email' => "pending$i@test.com",
                'role' => 'member',
                'company_id' => $this->testCompany['id'],
                'approval_status' => 'pending',
                'is_active' => 0
            ]);
        }

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->get('/api/companies/me/pending-users?page=1&per_page=3');

        $result->assertStatus(200);

        $data = json_decode($result->getJSON(), true);
        $this->assertArrayHasKey('pager', $data);
        $this->assertLessThanOrEqual(3, count($data['data']));

        // 清理
        for ($i = 1; $i <= 5; $i++) {
            $this->userModel->where('username', "pending_user_$i")->delete();
        }
    }

    public function testGetPendingUsersDoesNotShowOtherCompanies()
    {
        // 創建另一個公司和待審核使用者
        $otherCompanyId = $this->companyModel->insert([
            'name' => 'Other Company',
            'tax_id' => '87654321'
        ]);

        $this->userModel->insert([
            'username' => 'other_pending',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'other@test.com',
            'role' => 'member',
            'company_id' => $otherCompanyId,
            'approval_status' => 'pending',
            'is_active' => 0
        ]);

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->get('/api/companies/me/pending-users');

        $data = json_decode($result->getJSON(), true);
        $usernames = array_column($data['data'], 'username');

        // 不應包含其他公司的使用者
        $this->assertNotContains('other_pending', $usernames);

        // 清理
        $this->userModel->where('username', 'other_pending')->delete();
        $this->companyModel->delete($otherCompanyId, true);
    }

    // ==================== 審核使用者測試 ====================

    public function testApproveUserSuccess()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->withBodyFormat('json')
            ->post("/api/companies/me/approve-user/{$this->testPendingUser['id']}", [
                'action' => 'approve'
            ]);

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);
        $result->assertJSONFragment(['message' => '已核准使用者申請']);

        // 驗證資料庫已更新
        $user = $this->userModel->find($this->testPendingUser['id']);
        $this->assertEquals('approved', $user['approval_status']);
        $this->assertEquals(1, $user['is_substantive']);
        $this->assertEquals(1, $user['is_active']);
        $this->assertNotNull($user['approved_at']);
        $this->assertEquals($this->testManager['id'], $user['approved_by']);
    }

    public function testRejectUserSuccess()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->withBodyFormat('json')
            ->post("/api/companies/me/approve-user/{$this->testPendingUser['id']}", [
                'action' => 'reject'
            ]);

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);
        $result->assertJSONFragment(['message' => '已拒絕使用者申請']);

        // 驗證資料庫已更新
        $user = $this->userModel->find($this->testPendingUser['id']);
        $this->assertEquals('rejected', $user['approval_status']);
        $this->assertEquals(0, $user['is_active']);
        $this->assertNotNull($user['approved_at']);
        $this->assertEquals($this->testManager['id'], $user['approved_by']);
    }

    public function testApproveUserFromDifferentCompanyFails()
    {
        // 創建另一個公司的待審核使用者
        $otherCompanyId = $this->companyModel->insert([
            'name' => 'Other Company',
            'tax_id' => '87654321'
        ]);

        $otherUserId = $this->userModel->insert([
            'username' => 'other_pending',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'email' => 'other@test.com',
            'role' => 'member',
            'company_id' => $otherCompanyId,
            'approval_status' => 'pending',
            'is_active' => 0
        ]);

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->withBodyFormat('json')
            ->post("/api/companies/me/approve-user/$otherUserId", [
                'action' => 'approve'
            ]);

        $result->assertStatus(404);
        $result->assertJSONFragment(['status' => 'error']);

        // 清理
        $this->userModel->delete($otherUserId, true);
        $this->companyModel->delete($otherCompanyId, true);
    }

    // ==================== 可用成員列表測試 ====================

    public function testGetAvailableMembersSuccess()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->get('/api/companies/me/available-members');

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);

        $data = json_decode($result->getJSON(), true);
        $this->assertIsArray($data['data']);

        // 應包含已核准的使用者
        $usernames = array_column($data['data'], 'username');
        $this->assertContains('test_approved', $usernames);
        $this->assertContains('test_manager', $usernames);

        // 不應包含待審核的使用者
        $this->assertNotContains('test_pending', $usernames);
    }

    public function testGetAvailableMembersDoesNotContainSensitiveData()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->get('/api/companies/me/available-members');

        $data = json_decode($result->getJSON(), true);

        foreach ($data['data'] as $member) {
            $this->assertArrayNotHasKey('password_hash', $member);
            $this->assertArrayNotHasKey('password_reset_token', $member);
        }
    }

    // ==================== 成員指派測試 ====================

    public function testAssignMemberToRenewalSuccess()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->withBodyFormat('json')
            ->post("/api/companies/me/renewals/{$this->testUrbanRenewal['id']}/assign", [
                'user_id' => $this->testApprovedUser['id'],
                'permissions' => ['view', 'edit']
            ]);

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);
        $result->assertJSONFragment(['message' => '指派成功']);

        // 驗證指派記錄已建立
        $assignment = $this->assignmentModel
            ->where('user_id', $this->testApprovedUser['id'])
            ->where('urban_renewal_id', $this->testUrbanRenewal['id'])
            ->first();

        $this->assertNotNull($assignment);
        $this->assertEquals($this->testManager['id'], $assignment['assigned_by']);
    }

    public function testAssignPendingUserFails()
    {
        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->withBodyFormat('json')
            ->post("/api/companies/me/renewals/{$this->testUrbanRenewal['id']}/assign", [
                'user_id' => $this->testPendingUser['id'],
                'permissions' => ['view']
            ]);

        $result->assertStatus(403);
        $result->assertJSONFragment(['status' => 'error']);
    }

    public function testGetRenewalMembersSuccess()
    {
        // 先指派成員
        $this->assignmentModel->assign(
            $this->testApprovedUser['id'],
            $this->testUrbanRenewal['id'],
            $this->testManager['id'],
            ['view', 'edit']
        );

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->get("/api/companies/me/renewals/{$this->testUrbanRenewal['id']}/members");

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);

        $data = json_decode($result->getJSON(), true);
        $this->assertIsArray($data['data']);
        $this->assertGreaterThanOrEqual(1, count($data['data']));
    }

    public function testUnassignMemberFromRenewalSuccess()
    {
        // 先指派成員
        $this->assignmentModel->assign(
            $this->testApprovedUser['id'],
            $this->testUrbanRenewal['id'],
            $this->testManager['id'],
            ['view']
        );

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->delete("/api/companies/me/renewals/{$this->testUrbanRenewal['id']}/members/{$this->testApprovedUser['id']}");

        $result->assertStatus(200);
        $result->assertJSONFragment(['status' => 'success']);
        $result->assertJSONFragment(['message' => '已取消指派']);

        // 驗證指派記錄已刪除
        $assignment = $this->assignmentModel
            ->where('user_id', $this->testApprovedUser['id'])
            ->where('urban_renewal_id', $this->testUrbanRenewal['id'])
            ->first();

        $this->assertNull($assignment);
    }

    public function testUnassignMemberFromOtherCompanyRenewalFails()
    {
        // 創建另一個公司和更新會
        $otherCompanyId = $this->companyModel->insert([
            'name' => 'Other Company',
            'tax_id' => '87654321'
        ]);

        $otherRenewalId = $this->urbanRenewalModel->insert([
            'name' => 'Other Renewal',
            'company_id' => $otherCompanyId,
            'address' => 'Other Address',
            'status' => 'active'
        ]);

        $result = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken
        ])
            ->delete("/api/companies/me/renewals/$otherRenewalId/members/{$this->testApprovedUser['id']}");

        $result->assertStatus(403);
        $result->assertJSONFragment(['status' => 'error']);

        // 清理
        $this->urbanRenewalModel->delete($otherRenewalId, true);
        $this->companyModel->delete($otherCompanyId, true);
    }

    // ==================== 權限驗證測試 ====================

    public function testNonManagerCannotAccessApprovalEndpoints()
    {
        // 使用已核准的一般使用者
        $token = $this->getAuthToken('test_approved', 'password123');

        $endpoints = [
            ['method' => 'get', 'url' => '/api/companies/me/pending-users'],
            ['method' => 'get', 'url' => '/api/companies/me/invite-code'],
            ['method' => 'post', 'url' => '/api/companies/me/generate-invite-code'],
            ['method' => 'post', 'url' => "/api/companies/me/approve-user/{$this->testPendingUser['id']}"],
            ['method' => 'get', 'url' => '/api/companies/me/available-members'],
        ];

        foreach ($endpoints as $endpoint) {
            $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token
            ])
                ->withBodyFormat('json')
                ->{$endpoint['method']}($endpoint['url'], ['action' => 'approve']);

            $result->assertStatus(403);
            $result->assertJSONFragment(['status' => 'error']);
        }
    }

    public function testUnauthenticatedUserCannotAccessApprovalEndpoints()
    {
        $result = $this->get('/api/companies/me/pending-users');
        $result->assertStatus(401);
    }
}
