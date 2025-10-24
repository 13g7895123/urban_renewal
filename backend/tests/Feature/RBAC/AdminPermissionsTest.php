<?php

namespace Tests\Feature\RBAC;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\UserModel;
use App\Models\UrbanRenewalModel;
use App\Models\MeetingModel;

/**
 * 管理員權限整合測試
 * 驗證管理員登入後可無限制存取所有更新地區資源
 */
class AdminPermissionsTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate     = false;
    protected $migrateOnce = false;
    protected $refresh     = false;
    protected $namespace   = null;

    protected $adminUser;
    protected $regularUser;
    protected $adminToken;
    protected $userToken;
    protected $urbanRenewal1;
    protected $urbanRenewal2;

    protected function setUp(): void
    {
        parent::setUp();

        $userModel = new UserModel();
        $urbanRenewalModel = new UrbanRenewalModel();

        // Create test urban renewal projects
        $this->urbanRenewal1 = $urbanRenewalModel->insert([
            'area_name' => '測試更新地區A',
            'location' => '台北市中正區',
            'status' => 'active'
        ]);

        $this->urbanRenewal2 = $urbanRenewalModel->insert([
            'area_name' => '測試更新地區B',
            'location' => '台北市大安區',
            'status' => 'active'
        ]);

        // Create admin user (urban_renewal_id = null)
        $adminId = $userModel->insert([
            'username' => 'admin_test',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'email' => 'admin@test.com',
            'role' => 'admin',
            'urban_renewal_id' => null,
            'is_active' => 1
        ]);
        $this->adminUser = $userModel->find($adminId);

        // Create regular user assigned to urbanRenewal1
        $userId = $userModel->insert([
            'username' => 'user_test',
            'password_hash' => password_hash('user123', PASSWORD_DEFAULT),
            'email' => 'user@test.com',
            'role' => 'member',
            'urban_renewal_id' => $this->urbanRenewal1,
            'is_active' => 1
        ]);
        $this->regularUser = $userModel->find($userId);

        // Login admin and get token
        $adminLogin = $this->withBodyFormat('json')
            ->post('/api/auth/login', [
                'username' => 'admin_test',
                'password' => 'admin123'
            ]);
        $adminData = json_decode($adminLogin->getJSON(), true);
        $this->adminToken = $adminData['data']['token'];

        // Login regular user and get token
        $userLogin = $this->withBodyFormat('json')
            ->post('/api/auth/login', [
                'username' => 'user_test',
                'password' => 'user123'
            ]);
        $userData = json_decode($userLogin->getJSON(), true);
        $this->userToken = $userData['data']['token'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $userModel = new UserModel();
        $urbanRenewalModel = new UrbanRenewalModel();

        // Clean up test data
        if ($this->adminUser) {
            $userModel->delete($this->adminUser['id']);
        }
        if ($this->regularUser) {
            $userModel->delete($this->regularUser['id']);
        }
        if ($this->urbanRenewal1) {
            $urbanRenewalModel->delete($this->urbanRenewal1);
        }
        if ($this->urbanRenewal2) {
            $urbanRenewalModel->delete($this->urbanRenewal2);
        }
    }

    /**
     * AC-1: 管理員登入後，其 JWT token 的 urban_renewal_id 為 null
     */
    public function testAdminTokenHasNullUrbanRenewalId()
    {
        $this->assertNotEmpty($this->adminToken);

        // Decode JWT to verify urban_renewal_id is null
        $jwtConfig = config('JWT');
        $key = $_ENV['JWT_SECRET'] ?? 'urban_renewal_secret_key_2025';

        $decoded = \Firebase\JWT\JWT::decode(
            $this->adminToken,
            new \Firebase\JWT\Key($key, 'HS256')
        );

        $this->assertNull($decoded->urban_renewal_id);
        $this->assertEquals('admin', $decoded->role);
    }

    /**
     * AC-2: 管理員存取任一更新地區的會議列表時，API 不拋出 403 錯誤
     */
    public function testAdminCanAccessMeetingsFromAnyUrbanRenewal()
    {
        // Access meetings from urbanRenewal1
        $result1 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->adminToken
            ])
            ->get('/api/meetings?urban_renewal_id=' . $this->urbanRenewal1);

        $result1->assertStatus(200);

        // Access meetings from urbanRenewal2
        $result2 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->adminToken
            ])
            ->get('/api/meetings?urban_renewal_id=' . $this->urbanRenewal2);

        $result2->assertStatus(200);
    }

    /**
     * AC-3: 管理員可讀取所有 urban_renewal 記錄的列表
     */
    public function testAdminCanListAllUrbanRenewals()
    {
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->adminToken
            ])
            ->get('/api/urban-renewals');

        $result->assertStatus(200);

        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['data']);

        // Verify both urban renewals are in the list
        $ids = array_column($data['data'], 'id');
        $this->assertContains($this->urbanRenewal1, $ids);
        $this->assertContains($this->urbanRenewal2, $ids);
    }

    /**
     * AC-4: 一般使用者存取非其指派的 urban_renewal_id 資源時，API 回傳 403 Forbidden
     */
    public function testRegularUserCannotAccessUnassignedUrbanRenewal()
    {
        // Regular user tries to access urbanRenewal2 (not assigned to them)
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->userToken
            ])
            ->get('/api/meetings?urban_renewal_id=' . $this->urbanRenewal2);

        $result->assertStatus(403);

        $data = json_decode($result->getJSON(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('403', $data['error']['code']);
    }

    /**
     * AC-5: 一般使用者可正常存取其指派的 urban_renewal_id 資源
     */
    public function testRegularUserCanAccessAssignedUrbanRenewal()
    {
        // Regular user accesses their assigned urbanRenewal1
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->userToken
            ])
            ->get('/api/meetings?urban_renewal_id=' . $this->urbanRenewal1);

        $result->assertStatus(200);

        $data = json_decode($result->getJSON(), true);
        $this->assertTrue($data['success']);
    }

    /**
     * Additional test: Admin can create resources in any urban renewal
     */
    public function testAdminCanCreateResourcesInAnyUrbanRenewal()
    {
        $meetingModel = new MeetingModel();

        // Create meeting in urbanRenewal1
        $meeting1Id = $meetingModel->insert([
            'urban_renewal_id' => $this->urbanRenewal1,
            'meeting_type' => 'general',
            'title' => '測試會議A',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'status' => 'scheduled',
            'created_by' => $this->adminUser['id']
        ]);

        // Create meeting in urbanRenewal2
        $meeting2Id = $meetingModel->insert([
            'urban_renewal_id' => $this->urbanRenewal2,
            'meeting_type' => 'general',
            'title' => '測試會議B',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'status' => 'scheduled',
            'created_by' => $this->adminUser['id']
        ]);

        $this->assertNotEmpty($meeting1Id);
        $this->assertNotEmpty($meeting2Id);

        // Admin can read both meetings
        $result1 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->adminToken
            ])
            ->get('/api/meetings/' . $meeting1Id);
        $result1->assertStatus(200);

        $result2 = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->adminToken
            ])
            ->get('/api/meetings/' . $meeting2Id);
        $result2->assertStatus(200);

        // Clean up
        $meetingModel->delete($meeting1Id);
        $meetingModel->delete($meeting2Id);
    }

    /**
     * Additional test: Admin can update resources in any urban renewal
     */
    public function testAdminCanUpdateResourcesInAnyUrbanRenewal()
    {
        // Create a meeting in urbanRenewal1
        $meetingModel = new MeetingModel();
        $meetingId = $meetingModel->insert([
            'urban_renewal_id' => $this->urbanRenewal1,
            'meeting_type' => 'general',
            'title' => '原始標題',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'status' => 'scheduled',
            'created_by' => $this->regularUser['id']
        ]);

        // Admin updates the meeting
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->adminToken
            ])
            ->withBodyFormat('json')
            ->put('/api/meetings/' . $meetingId, [
                'title' => '更新後標題',
                'status' => 'completed'
            ]);

        $result->assertStatus(200);

        // Verify update
        $updated = $meetingModel->find($meetingId);
        $this->assertEquals('更新後標題', $updated['title']);
        $this->assertEquals('completed', $updated['status']);

        // Clean up
        $meetingModel->delete($meetingId);
    }

    /**
     * Additional test: Admin can delete resources from any urban renewal
     */
    public function testAdminCanDeleteResourcesFromAnyUrbanRenewal()
    {
        // Create a meeting in urbanRenewal2
        $meetingModel = new MeetingModel();
        $meetingId = $meetingModel->insert([
            'urban_renewal_id' => $this->urbanRenewal2,
            'meeting_type' => 'general',
            'title' => '待刪除會議',
            'scheduled_at' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'status' => 'scheduled',
            'created_by' => $this->regularUser['id']
        ]);

        // Admin deletes the meeting
        $result = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->adminToken
            ])
            ->delete('/api/meetings/' . $meetingId);

        $result->assertStatus(200);

        // Verify deletion
        $deleted = $meetingModel->find($meetingId);
        $this->assertNull($deleted);
    }
}
